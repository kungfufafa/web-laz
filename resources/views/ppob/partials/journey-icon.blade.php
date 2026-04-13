@switch($journey)
    @case('pulsa')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" {{ $attributes }}>
            <rect x="7" y="2.5" width="10" height="19" rx="2.5"></rect>
            <path d="M10 18h4"></path>
        </svg>
        @break

    @case('data')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" {{ $attributes }}>
            <path d="M5 8.5a10.5 10.5 0 0 1 14 0"></path>
            <path d="M8 11.5a6.5 6.5 0 0 1 8 0"></path>
            <path d="M11 14.5a2.5 2.5 0 0 1 2 0"></path>
            <path d="M12 18.5h.01"></path>
        </svg>
        @break

    @case('pln_token')
    @case('pln_bill')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" {{ $attributes }}>
            <path d="M13 2 5 13h5l-1 9 8-11h-5l1-9Z"></path>
        </svg>
        @break

    @case('e_wallet')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" {{ $attributes }}>
            <path d="M3 7.5A2.5 2.5 0 0 1 5.5 5h10A2.5 2.5 0 0 1 18 7.5V9h1.5A1.5 1.5 0 0 1 21 10.5v4a1.5 1.5 0 0 1-1.5 1.5H18v.5A2.5 2.5 0 0 1 15.5 19h-10A2.5 2.5 0 0 1 3 16.5v-9Z"></path>
            <path d="M18 10.5h3v4h-3a2 2 0 1 1 0-4Z"></path>
        </svg>
        @break

    @case('voucher')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" {{ $attributes }}>
            <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5V9a2 2 0 0 0 0 4v1.5A2.5 2.5 0 0 1 17.5 17h-11A2.5 2.5 0 0 1 4 14.5V13a2 2 0 0 0 0-4V7.5Z"></path>
            <path d="M12 5v12"></path>
        </svg>
        @break

    @case('bpjs')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" {{ $attributes }}>
            <path d="M12 21s-7-4.2-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 5.8-7 10-7 10Z"></path>
            <path d="M10 11h4"></path>
            <path d="M12 9v4"></path>
        </svg>
        @break

    @case('pdam')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" {{ $attributes }}>
            <path d="M12 3s5 5.3 5 9a5 5 0 0 1-10 0c0-3.7 5-9 5-9Z"></path>
        </svg>
        @break

    @case('internet_tv')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" {{ $attributes }}>
            <rect x="3" y="5" width="18" height="12" rx="2"></rect>
            <path d="M8 21h8"></path>
            <path d="M12 17v4"></path>
        </svg>
        @break

    @case('finance')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" {{ $attributes }}>
            <path d="M3 8.5 12 4l9 4.5"></path>
            <path d="M5 10v7.5A1.5 1.5 0 0 0 6.5 19h11a1.5 1.5 0 0 0 1.5-1.5V10"></path>
            <path d="M9 13h6"></path>
        </svg>
        @break

    @default
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" {{ $attributes }}>
            <circle cx="12" cy="12" r="8"></circle>
            <path d="M12 8v4l2.5 2.5"></path>
        </svg>
@endswitch
