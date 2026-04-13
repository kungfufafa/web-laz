# PPOB Production Playbook

## Tujuan

Checklist operasional untuk menjalankan PPOB secara stabil di production.

## Environment Wajib

- `DIGIFLAZZ_BASE_URL`
- `DIGIFLAZZ_USERNAME`
- `DIGIFLAZZ_API_KEY`
- `DIGIFLAZZ_WEBHOOK_SECRET`
- `MIDTRANS_SERVER_KEY`
- `MIDTRANS_CLIENT_KEY`
- `MIDTRANS_MERCHANT_ID`
- `MIDTRANS_IS_PRODUCTION`
- `MIDTRANS_FINISH_URL`
- `MIDTRANS_ENABLED_PAYMENTS`
- `PPOB_FULFILLMENT_DISPATCH`
- `PPOB_RECONCILE_BATCH_LIMIT`
- `PPOB_RECONCILE_SCHEDULE_MINUTES`
- `PPOB_MONITOR_SCHEDULE_MINUTES`
- `PPOB_FAILURE_ALERT_WINDOW_MINUTES`
- `PPOB_FAILURE_ALERT_THRESHOLD`
- `PPOB_LOG_LEVEL`
- `PPOB_ALERT_CHANNELS`

## Worker & Scheduler

Jalankan worker queue yang memproses queue `ppob`.

```bash
php artisan queue:work --queue=ppob,default --tries=5 --timeout=120
```

Pastikan scheduler aktif:

```bash
* * * * * php /path/to/project/artisan schedule:run >> /dev/null 2>&1
```

## Command Operasional

```bash
php artisan ppob:sync-digiflazz-catalog
php artisan ppob:reconcile-transactions --limit=50
php artisan ppob:monitor-failures --minutes=15 --threshold=3
php artisan ppob:health-check
```

## First Deploy Checklist

1. Jalankan migrasi.
2. Seed permission dan default pricing rule.
3. Pastikan queue worker aktif.
4. Pastikan scheduler aktif.
5. Sync katalog Digiflazz.
6. Jalankan health check.
7. Verifikasi callback URL Midtrans dan Digiflazz.

## Sandbox End-to-End

1. Isi credential sandbox Midtrans.
2. Isi credential Digiflazz untuk uji.
3. Sync katalog sandbox.
4. Buat transaksi prepaid dan postpaid dari mobile.
5. Verifikasi:
   - transaksi Snap Midtrans terbentuk
   - callback Midtrans masuk
   - fulfillment Digiflazz sukses
   - transaksi muncul di Filament
   - reconcile command tidak menemukan stuck transaction

## Incident Response

- Jalankan `ppob:reconcile-transactions`
- Cek `storage/logs/ppob.log`
- Cek dashboard PPOB di Filament
- Cek queue worker dan failed jobs
- Cek credential dan status provider
