<p align="center">
    <img src="public/images/logo.png" alt="Rumitech Logo" width="120">
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

Daftarkan akun melalui halaman register, lalu jalankan:

```bash
php artisan user:make-admin email@example.com
```

## Akses Halaman

| Halaman | URL |
| --- | --- |
| Landing page | `/` |
| Login | `/login` |
| Dashboard pengguna | `/ess` |
| Dashboard admin | `/admin` |
| Manajemen pengguna | `/admin/users` |
