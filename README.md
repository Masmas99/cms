<p align="center">
    <img src="public/images/logo.webp" alt="Rumitech Logo" width="120">
</p>

<h1 align="center">Rumitech</h1>

<p align="center">
    Aplikasi WMS dan CMS berbasis Laravel untuk mengelola gudang, stok, pengguna, dan akses admin.
</p>

## Fitur

- Landing page, login, registrasi, login Google, dan reset kata sandi lewat email
- Dua portal terpisah: admin di `/admin` dan staf/ESS di `/ess`
- Manajemen pengguna dan role admin/staf
- Master data produk, gudang, bin/lokasi, pemasok, dan ekspedisi
- Barang masuk: purchase order, penerimaan, staging, dan put-away
- Barang keluar: sales order, reservasi, picking FIFO/FEFO, packing, dan shipping
- Transfer stok antar bin atau gudang
- Stock opname blind count dan penyesuaian stok dengan approval admin
- Retur barang dan disposisi retur oleh admin
- Kartu stok, laporan aging/kapasitas, ekspor CSV, dan cetak lewat browser
- Label barcode Code 128B dan mode scan barcode USB/keyboard
- Audit aktivitas untuk request non-GET
- Tampilan responsif dengan Tailwind CSS dan halaman design system di `/design-system`

## Peran Pengguna

| Role | Akses |
| --- | --- |
| `admin` | Akses penuh: master data, manajemen user, approval, laporan lengkap, dan audit aktivitas |
| `user` | Operasional gudang, blind count, pengajuan penyesuaian, retur, scan, dan kartu stok |

Staf tidak dapat membuka fitur admin. Route admin-only tetap mengembalikan `403` jika diakses dari portal ESS.

## Teknologi

- Laravel 13
- PHP 8.3
- MySQL / SQLite
- Tailwind CSS 3
- Alpine.js
- Vite
- PHPUnit

## Persyaratan

- PHP 8.3 atau lebih baru
- Composer
- Node.js dan NPM
- Database MySQL atau SQLite

## Instalasi Lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Sesuaikan database dan kredensial admin di `.env`, lalu jalankan:

```bash
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Untuk Windows, gunakan `copy .env.example .env` jika perintah `cp` tidak tersedia.

`php artisan migrate --seed` juga membuat data demo WMS. Gunakan hanya untuk lingkungan lokal atau development.

Untuk development frontend, jalankan `npm run dev` atau `composer run dev`.

## Membuat Admin

### Melalui seeder

Tambahkan kredensial admin ke `.env`:

```env
ADMIN_NAME="Administrator"
ADMIN_EMAIL="admin@domain.com"
ADMIN_PASSWORD="password-yang-kuat"
```

Lalu jalankan:

```bash
php artisan config:clear
php artisan db:seed --class=AdminUserSeeder
```

Seeder akan membuat akun admin yang sudah terverifikasi. Jika email tersebut sudah ada, seeder hanya memastikan role admin dan verifikasi email tanpa mengubah password.

### Promosikan user yang sudah ada

```bash
php artisan user:make-admin email@example.com
```

## Data Demo WMS

Untuk mengisi data contoh tanpa menjalankan seluruh seeder:

```bash
php artisan db:seed --class=WmsDemoSeeder
```

Seeder ini membuat gudang `WH-JKT`, lokasi staging, beberapa bin, produk, pemasok, ekspedisi, stok awal, purchase order `PO-DEMO-001`, dan sales order `SO-DEMO-001`.

Pada lingkungan `local`, seeder juga membuat akun staf demo:

```text
Email: staff@rumitech.test
Password: password
```

Jangan menjalankan `php artisan db:seed` di VPS jika tidak ingin data demo ikut masuk. Untuk VPS, gunakan `php artisan db:seed --class=AdminUserSeeder` saja.

## Deploy VPS

Atur `.env` produksi terlebih dahulu:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

ADMIN_NAME="Administrator"
ADMIN_EMAIL="admin@domain.com"
ADMIN_PASSWORD="password-yang-kuat"
```

Lalu jalankan:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --class=AdminUserSeeder
npm ci
npm run build
php artisan optimize
```

Pastikan `APP_URL` memakai domain VPS agar link reset kata sandi tidak mengarah ke `localhost`.

## Konfigurasi Email (Lupa Kata Sandi)

`.env.example` sudah menggunakan konfigurasi SMTP Gmail. Isi `MAIL_USERNAME` dan `MAIL_PASSWORD` di `.env` agar link reset kata sandi benar-benar dikirim ke email.

```env
APP_URL=https://domain-anda.com

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_SCHEME=smtp
MAIL_USERNAME=email@domain.com
MAIL_PASSWORD=app-password-16-karakter
MAIL_FROM_ADDRESS="email@domain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Gunakan `MAIL_SCHEME=smtps` jika penyedia SMTP memakai port `465`. Untuk Gmail/Google Workspace, aktifkan verifikasi 2 langkah lalu buat App Password 16 karakter tanpa spasi. Gunakan kata sandi aplikasi, bukan kata sandi akun biasa.

Setelah mengubah `.env`, jalankan:

```bash
php artisan config:clear
```

Halaman reset kata sandi menggunakan bahasa Indonesia.

## Konfigurasi Login Google

Isi kredensial OAuth Google di `.env`:

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

Tambahkan URI callback tersebut sebagai authorized redirect URI di Google Cloud Console.

## Konfigurasi Landing Page

Tombol dan banner landing page mengarah ke URL WMS yang dapat diatur lewat `.env`:

```env
WMS_URL=http://20.92.125.158.sslip.io:8088/
```

Jika `WMS_URL` tidak diisi, aplikasi memakai nilai default dari `config/app.php`.

## Akses Halaman

| Halaman | URL |
| --- | --- |
| Landing page | `/` |
| Design system | `/design-system` |
| Login | `/login` |
| Dashboard admin | `/admin` |
| Dashboard staf/ESS | `/ess` |
| Manajemen user | `/admin/users` |

Halaman operasional gudang tersedia di `/admin/{fitur}` untuk admin dan `/ess/{fitur}` untuk staf. Contoh: `/admin/stocks`, `/ess/stocks`, `/admin/purchase-orders`, dan `/ess/purchase-orders`.

## Pengujian

```bash
php artisan test --compact
```

Untuk memeriksa format kode PHP:

```bash
vendor/bin/pint --dirty --format agent
```

Jalankan `npm run build` setelah mengubah tampilan atau aset frontend.
