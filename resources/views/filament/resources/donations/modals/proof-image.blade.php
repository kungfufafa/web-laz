<div>
    @if (filled($proofImageUrl))
        <img
            src="{{ $proofImageUrl }}"
            alt="{{ __('filament.resources.donations.fields.proof_image') }}"
            style="width: 100%; height: auto; max-height: 70vh; object-fit: contain; border-radius: 0.75rem;"
        />
    @else
        <p>{{ __('filament.resources.donations.columns.proof_unavailable') }}</p>
    @endif
</div>
