<?php

namespace App\Filament\Resources\PaymentMethods\Pages;

use App\Filament\Resources\PaymentMethods\PaymentMethodResource;
use App\Services\QrisPayloadService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class EditPaymentMethod extends EditRecord
{
    protected static string $resource = PaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->prepareQrisPayload($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepareQrisPayload(array $data): array
    {
        $uploadedQrisImage = is_string($data['qris_image'] ?? null)
            ? $data['qris_image']
            : null;

        try {
            if (($data['type'] ?? null) === 'qris' && $uploadedQrisImage !== null && $uploadedQrisImage !== '') {
                $imagePath = Storage::disk('public')->path($uploadedQrisImage);
                $data['qris_static_payload'] = app(QrisPayloadService::class)->extractStaticPayloadFromImage($imagePath);
            }
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'qris_image' => $exception->getMessage(),
            ]);
        }

        if (($data['type'] ?? null) === 'qris' && blank($data['qris_static_payload'] ?? null)) {
            throw ValidationException::withMessages([
                'qris_static_payload' => 'Upload foto QRIS atau isi payload QRIS secara manual.',
            ]);
        }

        if (($data['type'] ?? null) === 'qris') {
            try {
                $data['qris_static_payload'] = app(QrisPayloadService::class)
                    ->normalizeStaticPayload((string) $data['qris_static_payload']);
            } catch (InvalidArgumentException $exception) {
                throw ValidationException::withMessages([
                    'qris_static_payload' => 'Payload QRIS manual tidak valid.',
                ]);
            }
        }

        if (($data['type'] ?? null) !== 'qris') {
            $data['qris_static_payload'] = null;
            $data['qris_image'] = null;
        }

        return $data;
    }
}
