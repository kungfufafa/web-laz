<?php

use App\Services\QrisPayloadService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Str;

test('can extract qris payload from uploaded image', function () {
    $service = app(QrisPayloadService::class);
    $payload = '0002010102115204541153033605802ID5910TEST STORE6007JAKARTA63048E18';

    $filename = sys_get_temp_dir().DIRECTORY_SEPARATOR.'qris-'.Str::random(8).'.png';
    $directory = dirname($filename);

    if (! is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    $options = new QROptions([
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'imageBase64' => false,
    ]);

    (new QRCode($options))->render($payload, $filename);

    try {
        $decodedPayload = $service->extractStaticPayloadFromImage($filename);
        expect($decodedPayload)->toBe($payload);
    } finally {
        if (is_file($filename)) {
            unlink($filename);
        }
    }
});

test('can generate qris dynamic payload with amount', function () {
    $service = app(QrisPayloadService::class);
    $staticPayload = '0002010102115204541153033605802ID5910TEST STORE6007JAKARTA63048E18';

    $dynamicPayload = $service->generateDynamicPayload($staticPayload, 125000);

    expect($dynamicPayload)->toContain('010212');
    expect($dynamicPayload)->toContain('5406125000');
    expect(substr($dynamicPayload, -8, 4))->toBe('6304');
});

test('invalid static qris payload is rejected', function () {
    $service = app(QrisPayloadService::class);

    expect(fn () => $service->normalizeStaticPayload('0002010102116304ABCD'))
        ->toThrow(\InvalidArgumentException::class);
});
