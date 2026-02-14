<?php

namespace App\Services;

use InvalidArgumentException;
use Zxing\QrReader;

class QrisPayloadService
{
    /**
     * Normalize and validate QRIS static payload.
     */
    public function normalizeStaticPayload(string $payload): string
    {
        $normalizedPayload = str_replace(["\r", "\n", "\t"], '', trim($payload));

        if ($normalizedPayload === '') {
            throw new InvalidArgumentException('QRIS static payload is empty.');
        }

        $entries = $this->parseTopLevelTlv($normalizedPayload);

        $hasPayloadFormatTag = false;
        $hasCrcTag = false;

        foreach ($entries as $entry) {
            if ($entry['tag'] === '00' && $entry['value'] === '01') {
                $hasPayloadFormatTag = true;
            }

            if ($entry['tag'] === '63' && strlen($entry['value']) === 4) {
                $hasCrcTag = true;
            }
        }

        if (! $hasPayloadFormatTag || ! $hasCrcTag) {
            throw new InvalidArgumentException('QRIS payload format is invalid.');
        }

        if (! $this->isValidCrc($normalizedPayload)) {
            throw new InvalidArgumentException('QRIS payload CRC is invalid.');
        }

        return $normalizedPayload;
    }

    /**
     * Extract QRIS static payload from uploaded QR image.
     */
    public function extractStaticPayloadFromImage(string $imagePath): string
    {
        if (! is_file($imagePath)) {
            throw new InvalidArgumentException('QR image file was not found.');
        }

        $reader = new QrReader($imagePath, QrReader::SOURCE_TYPE_FILE);
        $decoded = $reader->text();

        if (! is_string($decoded) || trim($decoded) === '') {
            throw new InvalidArgumentException('Tidak dapat membaca QR dari gambar yang diupload.');
        }

        try {
            return $this->normalizeStaticPayload($decoded);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException('QR yang diupload bukan payload EMV QRIS yang valid.');
        }
    }

    /**
     * Build dynamic QRIS payload by injecting amount and recalculating CRC.
     */
    public function generateDynamicPayload(string $staticPayload, float|int|string $amount): string
    {
        $normalizedPayload = str_replace(["\r", "\n", "\t"], '', trim($staticPayload));
        if ($normalizedPayload === '') {
            throw new InvalidArgumentException('QRIS static payload is empty.');
        }

        $entries = $this->parseTopLevelTlv($normalizedPayload);
        $entries = array_values(array_filter(
            $entries,
            fn (array $entry): bool => $entry['tag'] !== '63'
        ));

        $entries = $this->upsertTag($entries, '01', '12', '00');
        $entries = $this->upsertTag($entries, '54', $this->formatAmount($amount), '53');

        $payloadWithoutCrc = $this->buildTlv($entries).'6304';
        $crc = $this->crc16CcittFalse($payloadWithoutCrc);

        return $payloadWithoutCrc.$crc;
    }

    /**
     * @return array<int, array{tag: string, value: string}>
     */
    private function parseTopLevelTlv(string $payload): array
    {
        $entries = [];
        $cursor = 0;
        $payloadLength = strlen($payload);

        while ($cursor + 4 <= $payloadLength) {
            $tag = substr($payload, $cursor, 2);
            $cursor += 2;

            $lengthRaw = substr($payload, $cursor, 2);
            if (! ctype_digit($lengthRaw)) {
                throw new InvalidArgumentException('Invalid QRIS payload length segment.');
            }

            $valueLength = (int) $lengthRaw;
            $cursor += 2;

            if ($cursor + $valueLength > $payloadLength) {
                throw new InvalidArgumentException('QRIS payload is truncated.');
            }

            $value = substr($payload, $cursor, $valueLength);
            $cursor += $valueLength;

            $entries[] = [
                'tag' => $tag,
                'value' => $value,
            ];
        }

        if ($cursor !== $payloadLength) {
            throw new InvalidArgumentException('QRIS payload format is invalid.');
        }

        return $entries;
    }

    /**
     * @param  array<int, array{tag: string, value: string}>  $entries
     * @return array<int, array{tag: string, value: string}>
     */
    private function upsertTag(array $entries, string $tag, string $value, ?string $insertAfterTag = null): array
    {
        $updated = false;
        $insertIndex = null;

        foreach ($entries as $index => $entry) {
            if ($entry['tag'] === $tag) {
                $entries[$index]['value'] = $value;
                $updated = true;
            }

            if ($insertAfterTag !== null && $entry['tag'] === $insertAfterTag) {
                $insertIndex = $index + 1;
            }
        }

        if (! $updated) {
            $newEntry = [['tag' => $tag, 'value' => $value]];

            if ($insertIndex === null) {
                $entries = [...$entries, ...$newEntry];
            } else {
                array_splice($entries, $insertIndex, 0, $newEntry);
            }
        }

        return $entries;
    }

    /**
     * @param  array<int, array{tag: string, value: string}>  $entries
     */
    private function buildTlv(array $entries): string
    {
        $segments = array_map(function (array $entry): string {
            $valueLength = strlen($entry['value']);
            if ($valueLength > 99) {
                throw new InvalidArgumentException('QRIS payload tag value exceeds 99 characters.');
            }

            return $entry['tag'].str_pad((string) $valueLength, 2, '0', STR_PAD_LEFT).$entry['value'];
        }, $entries);

        return implode('', $segments);
    }

    private function formatAmount(float|int|string $amount): string
    {
        if (! is_numeric((string) $amount)) {
            throw new InvalidArgumentException('QRIS amount must be numeric.');
        }

        $numericAmount = (float) $amount;
        if ($numericAmount <= 0) {
            throw new InvalidArgumentException('QRIS amount must be greater than zero.');
        }

        $formatted = number_format($numericAmount, 2, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }

    private function crc16CcittFalse(string $payload): string
    {
        $crc = 0xFFFF;
        $length = strlen($payload);

        for ($i = 0; $i < $length; $i++) {
            $crc ^= ord($payload[$i]) << 8;

            for ($bit = 0; $bit < 8; $bit++) {
                if (($crc & 0x8000) !== 0) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    private function isValidCrc(string $payload): bool
    {
        $crcTagPosition = strrpos($payload, '6304');
        if ($crcTagPosition === false || $crcTagPosition + 8 !== strlen($payload)) {
            return false;
        }

        $payloadWithoutCrcValue = substr($payload, 0, $crcTagPosition + 4);
        $expectedCrc = $this->crc16CcittFalse($payloadWithoutCrcValue);
        $actualCrc = strtoupper(substr($payload, -4));

        return $expectedCrc === $actualCrc;
    }
}
