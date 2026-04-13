# PPOB Development Guide

Dokumen ini adalah pusat referensi pengembangan fitur PPOB di project ini.

Tujuan utamanya:

- menjaga fokus pengembangan tetap di modul PPOB;
- memperjelas file mana yang jadi sumber kebenaran;
- menjelaskan alur Digiflazz, Midtrans, dan Tripay;
- mengurangi kebingungan saat perbaikan, penambahan fitur, atau investigasi bug.

Dokumen operasional production tetap ada di [PPOB_PRODUCTION_PLAYBOOK.md](./PPOB_PRODUCTION_PLAYBOOK.md). File ini fokus ke arsitektur, alur kerja, dan peta kode PPOB.

## 1. Scope PPOB di Project Ini

Modul PPOB di repo ini menggunakan pembagian tanggung jawab berikut:

- Digiflazz = sumber katalog produk PPOB dan fulfillment transaksi.
- Midtrans = payment gateway utama yang saat ini dijadikan default lewat `PPOB_PAYMENT_GATEWAY=midtrans`.
- Tripay = payment gateway alternatif.
- Filament = panel admin untuk pricing rule, katalog PPOB, transaksi PPOB, dan widget ringkasan.
- Web + API = dua entry point pengguna.

Yang perlu dipahami:

- Katalog produk PPOB tidak dibuat manual sebagai sumber utama. Sumber utamanya adalah Digiflazz.
- Pembayaran tidak ditangani oleh Digiflazz. Pembayaran ditangani oleh Midtrans atau Tripay.
- Fulfillment baru berjalan setelah transaksi dianggap `paid`.
- Midtrans dan Tripay hanya mengurusi pembayaran.
- Digiflazz mengurusi inquiry postpaid, topup prepaid, pay postpaid, dan status fulfillment.

## 2. Batasan Modul

Kalau ada perubahan PPOB, area utama yang seharusnya disentuh adalah:

- route PPOB;
- controller PPOB;
- request PPOB;
- service PPOB;
- model PPOB;
- job PPOB;
- command PPOB;
- resource PPOB;
- view PPOB;
- test PPOB.

Hindari menyebarkan logic PPOB ke modul lain jika tidak benar-benar perlu.

Contoh:

- validasi customer number PPOB jangan ditaruh di controller umum, gunakan `App\Support\PpobCustomerInput`;
- orkestrasi pembayaran jangan ditaruh di callback controller, gunakan `App\Services\PpobTransactionService`;
- callback provider jangan langsung update banyak field dari controller, gunakan `App\Services\PpobCallbackService`.

## 3. Gambaran Arsitektur

### 3.1 Alur besar

1. Admin sync katalog dari Digiflazz.
2. Produk PPOB disimpan di `ppob_products`.
3. Pricing rule dihitung lokal lewat `PpobPricingService`.
4. User memilih produk PPOB dari web atau API.
5. Untuk postpaid, sistem membuat inquiry ke Digiflazz terlebih dahulu.
6. Sistem membuat transaksi pembayaran ke Midtrans atau Tripay.
7. Callback payment gateway masuk ke callback endpoint PPOB.
8. Status pembayaran diperbarui.
9. Jika pembayaran sukses, fulfillment ke Digiflazz dijalankan.
10. Status fulfillment disimpan di `ppob_transactions`.

### 3.2 Pembagian provider

| Concern | Provider |
| --- | --- |
| Sinkron katalog | Digiflazz |
| Inquiry postpaid | Digiflazz |
| Fulfillment prepaid | Digiflazz |
| Fulfillment postpaid | Digiflazz |
| Payment channel list | Midtrans atau Tripay |
| Payment checkout | Midtrans atau Tripay |
| Payment callback | Midtrans atau Tripay |
| Fulfillment callback/status | Digiflazz |

## 4. Entry Point Utama

### 4.1 Route API

File: [routes/api.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/routes/api.php)

Endpoint PPOB aktif:

- `GET /api/ppob/products`
- `GET /api/ppob/payment-channels`
- `POST /api/ppob/inquiries`
- `POST /api/ppob/transactions`
- `GET /api/ppob/transactions`
- `GET /api/ppob/transactions/{ppobTransaction}`
- `POST /api/ppob/callbacks/midtrans`
- `POST /api/ppob/callbacks/tripay`
- `POST /api/ppob/callbacks/digiflazz`

### 4.2 Route Web

File: [routes/web.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/routes/web.php)

Route PPOB aktif:

- `/` = landing page PPOB publik
- `/ppob` = redirect ke home
- `/ppob/services/{serviceType}/{journey}`
- `/ppob/services/{serviceType}/{journey}/inquiries`
- `/ppob/services/{serviceType}/{journey}/transactions`
- `/ppob/transactions/{ppobTransaction}`
- `/ppob/transactions/{ppobTransaction}/refresh`

### 4.3 Scheduler

File: [routes/console.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/routes/console.php)

Task penting:

- prune `ProviderCallbackLog`
- reconcile transaksi PPOB
- monitor failure PPOB

## 5. Source of Truth per Layer

### 5.1 Controller

#### API

- [app/Http/Controllers/Api/PpobController.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Http/Controllers/Api/PpobController.php)
  - list produk
  - list payment channel
  - inquiry postpaid
  - create transaction
  - history
  - detail transaction

- [app/Http/Controllers/Api/PpobCallbackController.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Http/Controllers/Api/PpobCallbackController.php)
  - endpoint callback Midtrans
  - endpoint callback Tripay
  - endpoint callback Digiflazz

#### Web

- [app/Http/Controllers/Web/PpobController.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Http/Controllers/Web/PpobController.php)
  - landing PPOB
  - katalog berdasarkan journey
  - inquiry postpaid web
  - create transaction web
  - detail transaction web
  - refresh status web
  - guest checkout handling

- [app/Http/Controllers/Web/AuthController.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Http/Controllers/Web/AuthController.php)
  - login/register user publik untuk jalur PPOB web

### 5.2 Request Validation

- [app/Http/Requests/Api/StorePpobInquiryRequest.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Http/Requests/Api/StorePpobInquiryRequest.php)
  - validasi inquiry postpaid
  - normalisasi `customer_no`

- [app/Http/Requests/Api/StorePpobTransactionRequest.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Http/Requests/Api/StorePpobTransactionRequest.php)
  - validasi transaksi prepaid dan postpaid
  - fallback alias `tripay_method` ke `payment_channel_code`

- [app/Http/Requests/Web/LoginWebUserRequest.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Http/Requests/Web/LoginWebUserRequest.php)
- [app/Http/Requests/Web/RegisterWebUserRequest.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Http/Requests/Web/RegisterWebUserRequest.php)

### 5.3 Service Orchestrator

#### Service inti PPOB

- [app/Services/PpobTransactionService.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Services/PpobTransactionService.php)
  - pusat orkestrasi transaksi PPOB
  - resolve payment channel
  - create transaction prepaid/postpaid
  - sync status Midtrans/Tripay
  - refresh transaction
  - dispatch fulfillment
  - dispatch reconciliation

- [app/Services/PpobCallbackService.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Services/PpobCallbackService.php)
  - validasi callback provider
  - simpan callback log
  - mapping callback ke transaksi
  - trigger update status dan fulfillment

- [app/Services/PpobCatalogService.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Services/PpobCatalogService.php)
  - sync price list Digiflazz ke tabel lokal
  - list produk aktif PPOB

- [app/Services/PpobPricingService.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Services/PpobPricingService.php)
  - pilih pricing rule paling spesifik
  - hitung markup
  - hitung rounding harga jual

- [app/Services/PpobAlertService.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Services/PpobAlertService.php)
  - warning log
  - error log
  - security warning
  - monitor failure summary

#### Adapter provider

- [app/Services/DigiflazzClient.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Services/DigiflazzClient.php)
  - HTTP client level untuk Digiflazz

- [app/Services/DigiflazzPpobService.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Services/DigiflazzPpobService.php)
  - inquiry postpaid
  - cache inquiry reference
  - fulfill prepaid/postpaid
  - refresh fulfillment status
  - apply payload Digiflazz ke transaksi

- [app/Services/MidtransClient.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Services/MidtransClient.php)
  - daftar channel pembayaran Midtrans
  - create Snap transaction
  - check status transaksi
  - verifikasi signature callback

- [app/Services/TripayClient.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Services/TripayClient.php)
  - daftar channel pembayaran Tripay
  - create closed payment
  - detail transaksi
  - payment instruction
  - verifikasi signature callback

### 5.4 Support Helper

- [app/Support/PpobCustomerInput.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Support/PpobCustomerInput.php)
  - sumber kebenaran format `customer_no`
  - membedakan mobile, numeric, safe identifier
  - jika validasi input PPOB berubah, mulai dari file ini

- [app/Support/PhoneCarrier.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Support/PhoneCarrier.php)
  - mapping prefix operator untuk UI prepaid pulsa/data

- [app/Support/PhoneNumber.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Support/PhoneNumber.php)
  - normalisasi nomor telepon Indonesia

## 6. Model dan Struktur Data

### 6.1 Model inti

- [app/Models/PpobProduct.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Models/PpobProduct.php)
  - tabel katalog lokal hasil sync Digiflazz

- [app/Models/PpobTransaction.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Models/PpobTransaction.php)
  - tabel pusat transaksi PPOB
  - menyimpan status pembayaran, status fulfillment, payload gateway, payload Digiflazz

- [app/Models/PpobPricingRule.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Models/PpobPricingRule.php)
  - rule margin PPOB

- [app/Models/ProviderCallbackLog.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Models/ProviderCallbackLog.php)
  - arsip callback provider
  - punya pruning retention

### 6.2 Migrations penting

- [database/migrations/2026_03_30_133852_create_ppob_products_table.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/database/migrations/2026_03_30_133852_create_ppob_products_table.php)
- [database/migrations/2026_03_30_133852_create_ppob_transactions_table.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/database/migrations/2026_03_30_133852_create_ppob_transactions_table.php)
- [database/migrations/2026_03_30_133852_create_provider_callback_logs_table.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/database/migrations/2026_03_30_133852_create_provider_callback_logs_table.php)
- [database/migrations/2026_03_30_152716_create_ppob_pricing_rules_table.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/database/migrations/2026_03_30_152716_create_ppob_pricing_rules_table.php)
- [database/migrations/2026_03_30_152742_add_pricing_fields_to_ppob_products_table.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/database/migrations/2026_03_30_152742_add_pricing_fields_to_ppob_products_table.php)
- [database/migrations/2026_03_30_152742_add_pricing_fields_to_ppob_transactions_table.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/database/migrations/2026_03_30_152742_add_pricing_fields_to_ppob_transactions_table.php)
- [database/migrations/2026_04_09_152826_add_midtrans_fields_to_ppob_transactions_table.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/database/migrations/2026_04_09_152826_add_midtrans_fields_to_ppob_transactions_table.php)
- [database/migrations/2026_04_11_175508_rename_tripay_columns_on_ppob_transactions_table.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/database/migrations/2026_04_11_175508_rename_tripay_columns_on_ppob_transactions_table.php)

### 6.3 Catatan skema penting

Di `ppob_transactions` ada dua kelompok field gateway:

- field Midtrans eksplisit:
  - `midtrans_order_id`
  - `midtrans_transaction_id`
  - `midtrans_snap_token`
  - `midtrans_redirect_url`
  - `midtrans_payment_type`
  - `midtrans_expired_at`
  - `midtrans_payload`

- field gateway netral:
  - `payment_gateway_reference`
  - `payment_gateway_order_id`
  - `payment_gateway_checkout_url`
  - `payment_gateway_pay_url`
  - `payment_gateway_pay_code`
  - `payment_gateway_expired_at`
  - `payment_gateway_payload`

Artinya:

- Midtrans masih punya field khusus karena Snap flow berbeda;
- Tripay memakai field gateway netral;
- `metadata.payment_gateway` menentukan gateway yang dipakai transaksi;
- jangan menghapus field Midtrans tanpa audit penuh ke resource, callback, view, dan test.

## 7. Alur Kerja Detail

### 7.1 Sync katalog dari Digiflazz

Alur:

1. Command atau action Filament memanggil `PpobCatalogService::syncFromDigiflazz()`.
2. `DigiflazzClient::fetchPriceList()` mengambil price list.
3. `PpobPricingService` menghitung harga lokal.
4. `ppob_products` di-update.
5. SKU lama yang hilang dari hasil sync akan di-nonaktifkan.

File terkait:

- [app/Console/Commands/PpobSyncDigiflazzCatalog.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Console/Commands/PpobSyncDigiflazzCatalog.php)
- [app/Services/PpobCatalogService.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Services/PpobCatalogService.php)
- [app/Filament/Resources/PpobProducts/Pages/ListPpobProducts.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Filament/Resources/PpobProducts/Pages/ListPpobProducts.php)

### 7.2 Flow prepaid

Alur:

1. User ambil daftar produk PPOB.
2. User pilih produk prepaid.
3. Request masuk ke `StorePpobTransactionRequest`.
4. `PpobTransactionService::createTransaction()` ambil produk lokal.
5. Service membuat transaksi pembayaran ke gateway aktif:
   - Midtrans => Snap transaction
   - Tripay => closed payment
6. Transaksi PPOB disimpan sebagai `unpaid`.
7. Callback payment masuk.
8. Status payment menjadi `paid`.
9. Fulfillment ke Digiflazz dijalankan.
10. Status fulfillment menjadi `processing`, `succeeded`, `failed`, atau `manual_review`.

### 7.3 Flow postpaid

Alur:

1. User pilih produk postpaid.
2. Request inquiry masuk ke `StorePpobInquiryRequest`.
3. `DigiflazzPpobService::createInquiry()` memanggil inquiry Digiflazz.
4. Inquiry disimpan ke cache dengan reference lokal.
5. Web menyimpan inquiry ini di session agar user bisa reload dan tetap lanjut bayar.
6. Saat bayar, `createTransaction()` resolve inquiry reference dari cache.
7. Transaksi payment gateway dibuat.
8. Setelah `paid`, sistem memanggil fulfillment Digiflazz postpaid.

Catatan penting:

- inquiry postpaid bukan transaksi final;
- transaksi final baru terbentuk saat payment gateway berhasil dibuat;
- perubahan flow inquiry harus dicek di API dan web sekaligus.

### 7.4 Callback payment gateway

#### Midtrans

Alur:

1. Callback masuk ke `PpobCallbackController::midtrans`.
2. `PpobCallbackService::handleMidtransCallback()` memverifikasi signature.
3. Callback disimpan ke `provider_callback_logs`.
4. Sistem ambil transaksi terbaru dari Midtrans status API bila tersedia.
5. `PpobTransactionService::syncMidtransPayload()` update transaction.
6. Jika layak fulfill, dispatch fulfillment.

#### Tripay

Alur:

1. Callback masuk ke `PpobCallbackController::tripay`.
2. Signature diverifikasi.
3. Callback disimpan ke log.
4. `syncTripayPayload()` update transaction.
5. Jika layak fulfill, dispatch fulfillment.

### 7.5 Callback/status Digiflazz

Alur:

1. Callback masuk ke `PpobCallbackController::digiflazz`.
2. Signature diverifikasi.
3. Callback disimpan ke log.
4. `DigiflazzPpobService::applyPayload()` update status fulfillment transaksi.

### 7.6 Reconcile dan refresh

Refresh manual dan monitoring background tetap penting karena callback provider bisa gagal atau terlambat.

File terkait:

- [app/Jobs/ReconcilePpobTransaction.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Jobs/ReconcilePpobTransaction.php)
- [app/Jobs/ProcessPpobFulfillment.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Jobs/ProcessPpobFulfillment.php)
- [app/Console/Commands/PpobReconcileTransactions.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Console/Commands/PpobReconcileTransactions.php)
- [app/Console/Commands/PpobMonitorFailures.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Console/Commands/PpobMonitorFailures.php)
- [app/Console/Commands/PpobHealthCheck.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Console/Commands/PpobHealthCheck.php)

## 8. UI dan Admin

### 8.1 Web View aktif

- [resources/views/welcome.blade.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/resources/views/welcome.blade.php)
  - landing PPOB publik

- [resources/views/layouts/web.blade.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/resources/views/layouts/web.blade.php)
  - layout publik PPOB

- [resources/views/ppob/catalog.blade.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/resources/views/ppob/catalog.blade.php)
  - journey catalog
  - prepaid selection
  - postpaid inquiry/pay

- [resources/views/ppob/show.blade.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/resources/views/ppob/show.blade.php)
  - detail transaksi PPOB
  - tombol bayar
  - payment instructions
  - refresh status

- [resources/views/auth/login.blade.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/resources/views/auth/login.blade.php)
- [resources/views/auth/register.blade.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/resources/views/auth/register.blade.php)

### 8.2 Filament

#### Produk PPOB

- [app/Filament/Resources/PpobProducts/PpobProductResource.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Filament/Resources/PpobProducts/PpobProductResource.php)
- [app/Filament/Resources/PpobProducts/Tables/PpobProductsTable.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Filament/Resources/PpobProducts/Tables/PpobProductsTable.php)
- [app/Filament/Resources/PpobProducts/Pages/ListPpobProducts.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Filament/Resources/PpobProducts/Pages/ListPpobProducts.php)

#### Transaksi PPOB

- [app/Filament/Resources/PpobTransactions/PpobTransactionResource.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Filament/Resources/PpobTransactions/PpobTransactionResource.php)
- [app/Filament/Resources/PpobTransactions/Tables/PpobTransactionsTable.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Filament/Resources/PpobTransactions/Tables/PpobTransactionsTable.php)
- [app/Filament/Resources/PpobTransactions/Pages/ListPpobTransactions.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Filament/Resources/PpobTransactions/Pages/ListPpobTransactions.php)

#### Pricing rule PPOB

- [app/Filament/Resources/PpobPricingRules/PpobPricingRuleResource.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Filament/Resources/PpobPricingRules/PpobPricingRuleResource.php)
- [app/Filament/Resources/PpobPricingRules/Schemas/PpobPricingRuleForm.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Filament/Resources/PpobPricingRules/Schemas/PpobPricingRuleForm.php)
- [app/Filament/Resources/PpobPricingRules/Tables/PpobPricingRulesTable.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Filament/Resources/PpobPricingRules/Tables/PpobPricingRulesTable.php)
- [app/Filament/Resources/PpobPricingRules/Pages/ListPpobPricingRules.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Filament/Resources/PpobPricingRules/Pages/ListPpobPricingRules.php)
- [app/Filament/Resources/PpobPricingRules/Pages/CreatePpobPricingRule.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Filament/Resources/PpobPricingRules/Pages/CreatePpobPricingRule.php)
- [app/Filament/Resources/PpobPricingRules/Pages/EditPpobPricingRule.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Filament/Resources/PpobPricingRules/Pages/EditPpobPricingRule.php)

#### Widget

- [app/Filament/Widgets/PpobStatsWidget.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Filament/Widgets/PpobStatsWidget.php)

#### Policy

- [app/Policies/PpobProductPolicy.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Policies/PpobProductPolicy.php)
- [app/Policies/PpobTransactionPolicy.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Policies/PpobTransactionPolicy.php)
- [app/Policies/PpobPricingRulePolicy.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Policies/PpobPricingRulePolicy.php)

## 9. Konfigurasi dan Env

### 9.1 Config file

- [config/services.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/config/services.php)
- [config/logging.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/config/logging.php)
- [.env.example](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/.env.example)

### 9.2 Env PPOB penting

#### Digiflazz

- `DIGIFLAZZ_BASE_URL`
- `DIGIFLAZZ_USERNAME`
- `DIGIFLAZZ_API_KEY`
- `DIGIFLAZZ_WEBHOOK_SECRET`
- `DIGIFLAZZ_TESTING`
- `DIGIFLAZZ_TIMEOUT`

#### Midtrans

- `MIDTRANS_SERVER_KEY`
- `MIDTRANS_CLIENT_KEY`
- `MIDTRANS_MERCHANT_ID`
- `MIDTRANS_IS_PRODUCTION`
- `MIDTRANS_SNAP_BASE_URL`
- `MIDTRANS_API_BASE_URL`
- `MIDTRANS_FINISH_URL`
- `MIDTRANS_ENABLED_PAYMENTS`
- `MIDTRANS_EXPIRY_MINUTES`
- `MIDTRANS_TIMEOUT`

#### Tripay

- `TRIPAY_BASE_URL`
- `TRIPAY_API_KEY`
- `TRIPAY_PRIVATE_KEY`
- `TRIPAY_MERCHANT_CODE`
- `TRIPAY_RETURN_URL`
- `TRIPAY_EXPIRY_MINUTES`
- `TRIPAY_TIMEOUT`

#### PPOB internal

- `PPOB_PAYMENT_GATEWAY`
- `PPOB_FULFILLMENT_DISPATCH`
- `PPOB_RECONCILE_BATCH_LIMIT`
- `PPOB_RECONCILE_SCHEDULE_MINUTES`
- `PPOB_MONITOR_SCHEDULE_MINUTES`
- `PPOB_JOB_TRIES`
- `PPOB_JOB_BACKOFF_SECONDS`
- `PPOB_JOB_TIMEOUT_SECONDS`
- `PPOB_JOB_LOCK_EXPIRE_SECONDS`
- `PPOB_JOB_LOCK_RELEASE_SECONDS`
- `PPOB_FAILURE_ALERT_WINDOW_MINUTES`
- `PPOB_FAILURE_ALERT_THRESHOLD`

#### Logging

- `PPOB_LOG_LEVEL`
- `PPOB_LOG_DAILY_DAYS`
- `PPOB_ALERT_CHANNELS`

## 10. Seeder dan Default Rule

File:

- [database/seeders/PpobPricingRuleSeeder.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/database/seeders/PpobPricingRuleSeeder.php)
- [database/seeders/DatabaseSeeder.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/database/seeders/DatabaseSeeder.php)

Default bawaan:

- `Default PPOB Prepaid`
  - fixed markup
  - `1500`
  - rounding `100`

- `Default PPOB Postpaid`
  - percent markup
  - `2.5%`
  - min `2500`
  - max `6000`
  - rounding `500`

## 11. Response Contract

Kalau ubah output API, cek file ini:

- [app/Http/Resources/PpobProductResource.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Http/Resources/PpobProductResource.php)
- [app/Http/Resources/PpobTransactionResource.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/app/Http/Resources/PpobTransactionResource.php)

Karena perubahan resource akan mempengaruhi:

- mobile app contract;
- frontend web PPOB;
- test contract;
- integrasi QA.

## 12. Test Map

### Feature test

- [tests/Feature/Api/PpobTest.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/tests/Feature/Api/PpobTest.php)
  - API flow PPOB
  - callback Midtrans/Tripay/Digiflazz
  - refresh
  - reconcile

- [tests/Feature/Web/PpobWebFlowTest.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/tests/Feature/Web/PpobWebFlowTest.php)
  - flow web publik
  - guest checkout
  - postpaid inquiry
  - journey UI

- [tests/Feature/Console/PpobOperationalCommandsTest.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/tests/Feature/Console/PpobOperationalCommandsTest.php)
  - health check
  - monitor failures

- [tests/Feature/Filament/PpobResourceTest.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/tests/Feature/Filament/PpobResourceTest.php)
  - action Filament
  - sync resource
  - retry fulfillment

- [tests/Feature/Database/PpobPricingRuleSeederTest.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/tests/Feature/Database/PpobPricingRuleSeederTest.php)
  - seeder PPOB

- [tests/Feature/ProviderCallbackLogPruningTest.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/tests/Feature/ProviderCallbackLogPruningTest.php)
  - pruning callback log

### Unit test

- [tests/Unit/PpobPricingServiceTest.php](/Users/apriansyahrs/Documents/Code/dev/laz/web-laz/tests/Unit/PpobPricingServiceTest.php)
  - rule selection
  - percent/fixed markup
  - rounding

## 13. Checklist Saat Mengubah PPOB

### 13.1 Kalau mengubah pricing

Minimal cek:

- `PpobPricingRule`
- `PpobPricingService`
- Filament pricing rule resource
- seeder default pricing
- unit test pricing
- API/Web tests yang bergantung ke nominal

### 13.2 Kalau mengubah flow payment gateway

Minimal cek:

- `PpobTransactionService`
- client provider terkait
- `PpobCallbackService`
- `PpobTransactionResource`
- view `ppob/show.blade.php`
- env di `.env.example`
- `PpobHealthCheck`
- test API callback dan checkout

### 13.3 Kalau menambah gateway baru

Jangan hanya tambah client.

Minimal area yang harus ikut berubah:

- `config/services.php`
- `.env.example`
- `PpobTransactionService`
- `PpobCallbackService`
- command `ppob:health-check`
- resource transaction
- view payment detail
- test API
- test web jika checkout tampil berbeda

### 13.4 Kalau mengubah customer number logic

Mulai dari:

- `PpobCustomerInput`
- request inquiry/transaction
- web catalog copy
- test API
- test web

### 13.5 Kalau mengubah postpaid inquiry flow

Minimal cek:

- `DigiflazzPpobService::createInquiry`
- `resolveInquiryReference`
- session `ppob_inquiry` di controller web
- page reload behavior
- checkout success behavior
- API inquiry
- web inquiry tests

## 14. Titik Investigasi Bug Paling Sering

Kalau ada issue PPOB, mulai investigasi dari urutan ini:

1. route yang dipanggil
2. request validation
3. `PpobTransactionService`
4. adapter provider terkait
5. payload transaction di `ppob_transactions`
6. `provider_callback_logs`
7. log `storage/logs/ppob.log`
8. job queue dan scheduler
9. test yang paling dekat dengan flow tersebut

## 15. Ringkasan Fokus Kerja PPOB

Kalau ada development atau bugfix PPOB, fokus utama modul ini adalah:

- katalog dan pricing lokal;
- checkout prepaid dan postpaid;
- callback payment gateway;
- fulfillment Digiflazz;
- monitoring transaksi gagal atau stuck;
- UI publik PPOB;
- panel admin PPOB;
- test coverage PPOB.

Kalau perubahan tidak menyentuh salah satu area di atas, pastikan dulu memang masih relevan dengan PPOB sebelum menyebarkannya ke modul lain.
