# Aplikasi Web LAZ

Aplikasi web dan backend API berbasis Laravel untuk LAZ (Lembaga Amil Zakat). Sistem ini menyediakan API yang komprehensif untuk aplikasi seluler, termasuk autentikasi, manajemen donasi, perhitungan zakat, pengiriman konten (artikel dan video), dan interaksi doa anggota, bersama dengan panel administratif yang didukung oleh Filament.

## Fitur Utama

- **Sistem Autentikasi**: Registrasi, login, dan penghapusan akun yang aman menggunakan Laravel Sanctum.
- **Donasi & Zakat**: Menangani transaksi donasi, perhitungan zakat, konfigurasi metode donasi, dan melihat riwayat donasi.
- **Manajemen Konten**: Menyediakan artikel, video, dan media melalui API.
- **Doa Anggota**: Memungkinkan pengguna untuk memublikasikan doa dan berinteraksi dengan doa pengguna lain (seperti mengaminkan doa).
- **Panel Admin**: Didukung oleh Filament untuk manajemen seluruh data secara komprehensif.
- **Halaman Informasi**: Halaman web bawaan untuk Kebijakan Privasi, Ketentuan Layanan, Dukungan, dan portal Penghapusan Akun.

## Teknologi yang Digunakan

- **Framework**: [Laravel 12](https://laravel.com)
- **Bahasa Pemrograman**: PHP 8.2+
- **Panel Admin**: [Filament v5](https://filamentphp.com)
- **Autentikasi API**: Laravel Sanctum
- **Database**: SQLite (default untuk lokal) / MySQL / PostgreSQL

## Panduan Memulai

### Prasyarat

- PHP >= 8.2
- Composer
- Node.js & NPM
- SQLite / MySQL / PostgreSQL

### Instalasi

1. Clone repositori:
   ```bash
   git clone <url-repositori>
   cd laz-web
   ```

2. Instal dependensi PHP:
   ```bash
   composer install
   ```

3. Instal dependensi NPM:
   ```bash
   npm install
   ```

4. Konfigurasi file environment (lingkungan):
   ```bash
   cp .env.example .env
   ```
   Sesuaikan file `.env` dengan pengaturan database dan environment lokal Anda.

5. Hasilkan *key* aplikasi:
   ```bash
   php artisan key:generate
   ```

6. Jalankan migrasi database:
   ```bash
   php artisan migrate
   ```
   *(Opsional)* Jalankan seeder jika tersedia: `php artisan db:seed`

7. Jalankan aplikasi:
   ```bash
   # Menjalankan server lokal dan Vite secara bersamaan
   composer run dev
   # Atau menggunakan perintah artisan standar:
   php artisan serve
   npm run dev
   ```

## Struktur Aplikasi

### Endpoint API (`/api/*`)

- **Autentikasi**: `/register`, `/login`, `/logout`, `/user`, `/account-deletion`
- **Donasi**: `/donations`, `/donations/history`, `/donation-config`, `/payment-methods`, `/zakat/calculate`
- **Konten**: `/articles`, `/articles/{slug}`, `/videos`, `/media/{path}`
- **Doa**: `/prayers`, `/prayers/{prayer}/amen`

*Catatan: Beberapa endpoint memerlukan autentikasi Sanctum. Terdapat* rate limiting *(contoh: 5 permintaan/menit untuk endpoint autentikasi).*

### Halaman Web
- `/` - Halaman Selamat Datang
- `/privacy-policy` - Kebijakan Privasi
- `/terms-of-service` - Syarat dan Ketentuan
- `/support` - Dukungan
- `/account-deletion` - Portal Penghapusan Akun

## Lisensi

Proyek ini adalah *software* *open-source* dengan [lisensi MIT](https://opensource.org/licenses/MIT).
