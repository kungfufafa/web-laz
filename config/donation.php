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
                'umum' => 'Infak Umum',
            ],
        ],
        'sedekah' => [
            'label' => 'Sedekah',
            'payment_types' => [
                'umum' => 'Sedekah Umum',
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

    'contexts' => [],

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

    'recommended_amounts' => [1000, 2000, 5000, 10000, 20000, 50000, 100000],

];
