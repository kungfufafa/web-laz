<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Donation Categories
    |--------------------------------------------------------------------------
    |
    | Each category defines its label and allowed payment types.
    |
    */

    'categories' => [
        'zakat' => [
            'label' => 'Zakat',
            'payment_types' => [
                'maal' => 'Zakat Maal',
                'fitrah' => 'Zakat Fitrah',
                'profesi' => 'Zakat Profesi',
            ],
        ],
        'infak' => [
            'label' => 'Infak',
            'payment_types' => [
                'kemanusiaan' => 'Infak Kemanusiaan',
                'umum' => 'Umum',
            ],
        ],
        'sedekah' => [
            'label' => 'Sedekah',
            'payment_types' => [
                'jariyah' => 'Sedekah Jariyah',
                'umum' => 'Umum',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Category Aliases
    |--------------------------------------------------------------------------
    |
    | Spelling variants that normalize to canonical category keys.
    |
    */

    'category_aliases' => [
        'sodaqoh' => 'sedekah',
        'sodakoh' => 'sedekah',
        'shodaqoh' => 'sedekah',
        'shodaqah' => 'sedekah',
        'shadaqah' => 'sedekah',
    ],

    /*
    |--------------------------------------------------------------------------
    | Donation Contexts
    |--------------------------------------------------------------------------
    |
    | Predefined contexts for infak and sedekah categories.
    |
    */

    'contexts' => [
        'infak' => [
            [
                'slug' => 'infak-pendidikan',
                'label' => 'Infak Pendidikan',
                'description' => 'Dukung beasiswa santri, guru ngaji, dan pendidikan umat.',
            ],
            [
                'slug' => 'infak-kemanusiaan',
                'label' => 'Infak Kemanusiaan',
                'description' => 'Bantuan cepat tanggap bencana dan kebutuhan darurat.',
            ],
            [
                'slug' => 'infak-operasional',
                'label' => 'Infak Operasional Dakwah',
                'description' => 'Mendukung operasional dakwah dan pelayanan umat.',
            ],
        ],
        'sedekah' => [
            [
                'slug' => 'sedekah-jariyah',
                'label' => 'Sedekah Jariyah',
                'description' => 'Amal jangka panjang untuk pahala berkelanjutan.',
            ],
            [
                'slug' => 'sedekah-subuh',
                'label' => 'Sedekah Subuh',
                'description' => 'Sedekah rutin di waktu subuh.',
            ],
            [
                'slug' => 'sedekah-umum',
                'label' => 'Sedekah Umum',
                'description' => 'Sedekah bebas sesuai niat kebaikan Anda.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Zakat Calculator
    |--------------------------------------------------------------------------
    */

    'zakat_calculator_types' => ['fitrah', 'maal', 'profesi'],

    'zakat_defaults' => [
        'fitrah_rice_kg_per_person' => 2.5,
        'maal_nisab_gold_grams' => 85,
        'profesi_nisab_gold_grams' => 85,
    ],

    /*
    |--------------------------------------------------------------------------
    | Recommended Amounts
    |--------------------------------------------------------------------------
    */

    'recommended_amounts' => [25000, 50000, 100000, 250000, 500000, 1000000],

];
