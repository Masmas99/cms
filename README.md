<p align="center">
    <img src="public/images/logo.webp" alt="Rumitech Logo" width="120">
</p>

<h1 align="center">Rumitech</h1>

<p align="center">
    Aplikasi CMS berbasis Laravel untuk mengelola konten, pengguna, dan akses admin.
</p>

## Fitur

- Login bersama untuk admin dan pengguna biasa
- Dashboard admin di `/admin`
- Manajemen pengguna: tambah, edit, hapus, dan atur role
- Halaman pengguna di `/ess`
- Manajemen profil pengguna
- Tampilan responsif dengan Tailwind CSS

## Teknologi

- Laravel 13
- PHP 8.3
- MySQL / SQLite
- Tailwind CSS
- Alpine.js
- Vite

## Persyaratan

- PHP 8.3 atau lebih baru
- Composer
- Node.js dan NPM
- Database MySQL atau SQLite

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Sesuaikan konfigurasi database di file `.env`, lalu jalankan:

```bash
php artisan migrate
npm install
npm run build
php artisan serve
```

Untuk Windows, gunakan `copy .env.example .env` jika perintah `cp` tidak tersedia.

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

Jika database baru, `php artisan migrate --seed` akan menjalankan seeder admin secara otomatis.

Seeder akan membuat akun admin yang sudah terverifikasi. Jika email tersebut sudah ada, seeder hanya memastikan role admin dan verifikasi email tanpa mengubah password.

### Promosikan user yang sudah ada

```bash
php artisan user:make-admin email@example.com
```

## Konfigurasi Email (Lupa Kata Sandi)

`.env.example` sudah menggunakan konfigurasi SMTP Gmail. Isi `MAIL_USERNAME` dan `MAIL_PASSWORD` di `.env` agar link reset kata sandi benar-benar dikirim ke email.

Untuk mengirim email sungguhan, atur SMTP di `.env`:

```env
APP_URL=https://domain-anda.com

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_SCHEME=smtp
MAIL_USERNAME=email@domain.com
MAIL_PASSWORD=password-smtp
MAIL_FROM_ADDRESS="email@domain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Gunakan `MAIL_SCHEME=smtps` jika penyedia SMTP memakai port `465`. Untuk Gmail/Google Workspace, aktifkan verifikasi 2 langkah lalu buat App Password 16 karakter (tanpa spasi). Gunakan host `smtp.gmail.com`, port `587`, dan scheme `smtp`, bukan kata sandi akun biasa.

Setelah mengubah `.env`, jalankan:

```bash
php artisan config:clear
```

Pastikan `APP_URL` sudah memakai domain VPS agar link reset tidak mengarah ke `localhost`.

## Akses Halaman

| Halaman | URL |
| --- | --- |
| Landing page | `/` |
| Login | `/login` |
| Dashboard pengguna | `/ess` |
| Dashboard admin | `/admin` |
| Manajemen pengguna | `/admin/users` |

