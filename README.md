<p align="center">
    <img src="public/images/logo.webp" alt="Rumitech Logo" width="120">
</p>

<h1 align="center">Rumitech</h1>

<p align="center">
    Aplikasi WMS berbasis Laravel untuk mengelola stok dan operasional gudang.
</p>

## Tentang

Rumitech membantu tim gudang mencatat barang masuk, barang keluar, perpindahan stok, dan aktivitas inventori dalam satu aplikasi. Tersedia dua portal: admin untuk pengelolaan dan persetujuan, serta ESS untuk staf yang menjalankan operasional harian.

## Fitur Utama

- Master data produk, gudang, bin, pemasok, dan ekspedisi
- Barang masuk: purchase order, penerimaan, staging, dan put-away
- Barang keluar: sales order, reservasi, picking FIFO/FEFO, packing, dan shipping
- Transfer stok antar bin atau gudang
- Stock opname blind count dan penyesuaian stok
- Retur barang dan disposisi
- Kartu stok dan laporan inventori
- Label barcode dan mode scan
- Audit aktivitas pengguna
- Manajemen pengguna dengan role admin dan staf

## Peran Pengguna

- **Admin**: akses penuh ke master data, manajemen user, persetujuan, laporan lengkap, dan audit aktivitas.
- **Staf**: menjalankan operasional gudang, blind count, pengajuan penyesuaian, retur, scan, dan melihat kartu stok.

## Teknologi

Laravel 13, PHP 8.3, MySQL/SQLite, Tailwind CSS, Alpine.js, dan Vite.
