<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'digiflazz' => [
        'base_url' => env('DIGIFLAZZ_BASE_URL', 'https://api.digiflazz.com/v1'),
        'username' => env('DIGIFLAZZ_USERNAME'),
        'api_key' => env('DIGIFLAZZ_API_KEY'),
        'webhook_secret' => env('DIGIFLAZZ_WEBHOOK_SECRET'),
        'testing' => env('DIGIFLAZZ_TESTING', false),
        'timeout' => (int) env('DIGIFLAZZ_TIMEOUT', 30),
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'snap_base_url' => env('MIDTRANS_SNAP_BASE_URL'),
        'api_base_url' => env('MIDTRANS_API_BASE_URL'),
        'finish_url' => env('MIDTRANS_FINISH_URL'),
        'enabled_payments' => array_values(array_filter(array_map(
            static fn (string $payment): string => trim($payment),
            explode(',', (string) env('MIDTRANS_ENABLED_PAYMENTS', 'bri_va,bca_va,bni_va,echannel,permata_va,qris,gopay,shopeepay'))
        ))),
        'expiry_minutes' => (int) env('MIDTRANS_EXPIRY_MINUTES', 60),
        'timeout' => (int) env('MIDTRANS_TIMEOUT', 30),
    ],

    'tripay' => [
        'base_url' => env('TRIPAY_BASE_URL', 'https://tripay.co.id/api-sandbox'),
        'api_key' => env('TRIPAY_API_KEY'),
        'private_key' => env('TRIPAY_PRIVATE_KEY'),
        'merchant_code' => env('TRIPAY_MERCHANT_CODE'),
        'return_url' => env('TRIPAY_RETURN_URL'),
        'expiry_minutes' => (int) env('TRIPAY_EXPIRY_MINUTES', 60),
        'timeout' => (int) env('TRIPAY_TIMEOUT', 30),
    ],

    'ppob' => [
        'payment_gateway' => env('PPOB_PAYMENT_GATEWAY', 'midtrans'),
        'fulfillment_dispatch' => env('PPOB_FULFILLMENT_DISPATCH', 'queue'),
        'reconcile_batch_limit' => (int) env('PPOB_RECONCILE_BATCH_LIMIT', 50),
        'reconcile_schedule_minutes' => (int) env('PPOB_RECONCILE_SCHEDULE_MINUTES', 5),
        'reconcile_success_window_hours' => (int) env('PPOB_RECONCILE_SUCCESS_WINDOW_HOURS', 24),
        'monitor_schedule_minutes' => (int) env('PPOB_MONITOR_SCHEDULE_MINUTES', 5),
        'job_tries' => (int) env('PPOB_JOB_TRIES', 5),
        'job_backoff_seconds' => (int) env('PPOB_JOB_BACKOFF_SECONDS', 30),
        'job_timeout_seconds' => (int) env('PPOB_JOB_TIMEOUT_SECONDS', 120),
        'job_lock_expire_seconds' => (int) env('PPOB_JOB_LOCK_EXPIRE_SECONDS', 300),
        'job_lock_release_seconds' => (int) env('PPOB_JOB_LOCK_RELEASE_SECONDS', 30),
        'failure_alert_window_minutes' => (int) env('PPOB_FAILURE_ALERT_WINDOW_MINUTES', 15),
        'failure_alert_threshold' => (int) env('PPOB_FAILURE_ALERT_THRESHOLD', 3),
    ],

];
