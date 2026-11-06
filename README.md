# E-Report

E-Report adalah aplikasi pelaporan digital untuk membantu warga menyampaikan laporan dan memantau tindak lanjutnya, serta membantu administrator mengelola laporan secara terpusat.

## Fitur

### Warga

- Registrasi dan login menggunakan session.
- Membuat, melihat, mencari, dan memfilter laporan milik sendiri.
- Mengunggah foto bukti dengan validasi.
- Mengedit atau menghapus laporan yang masih berstatus pending.
- Memantau status dan riwayat perubahan laporan.
- Menerima notifikasi pembaruan laporan.
- Melihat ringkasan laporan pribadi pada dashboard.

### Administrator

- Melihat ringkasan analitik dan tren laporan.
- Mencari, memfilter, mengurutkan, serta meninjau laporan warga.
- Memperbarui status laporan dan memberikan tanggapan.
- Melihat riwayat perubahan status.
- Mengelola profil dan status aktif akun warga.
- Meninjau aktivitas dan notifikasi terkait laporan.

Status laporan mengikuti implementasi aplikasi dan schema database: `pending`, `approved`, dan `rejected`.

## Teknologi

- PHP 8.3 atau lebih baru dan Laravel 13.
- MySQL untuk penggunaan aplikasi; SQLite in-memory digunakan pada konfigurasi test.
- Composer untuk dependency PHP.
- Node.js dan npm, Vite 8, serta Tailwind CSS 4 untuk asset frontend.
- Blade dan Livewire 4.
- Pest untuk pengujian.

## Persyaratan

- PHP 8.3+ dengan ekstensi Laravel yang diperlukan, termasuk PDO MySQL bila menggunakan MySQL.
- Composer.
- Node.js versi yang didukung Vite 8 (Node.js 20.19+ atau 22.12+).
- MySQL yang kompatibel untuk database lokal.
- Git.

Untuk pemeriksaan platform dependency PHP, jalankan:

```bash
composer check-platform-reqs
```

## Menjalankan secara lokal

1. Pasang dependency PHP:

   ```bash
   composer install
   ```

2. Salin template environment lokal, lalu buat application key:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. Siapkan database MySQL khusus development, lalu atur koneksi database lokal di `.env`. Jangan memakai database produksi atau menyalin `.env` dari mesin lain.

4. Jalankan migration hanya terhadap database development yang sudah dipilih dan diverifikasi:

   ```bash
   php artisan migrate
   ```

   Perintah ini mengubah schema database yang dikonfigurasi. Untuk database yang berisi data, buat dan verifikasi backup lebih dahulu. Jangan gunakan perintah reset/refresh database untuk instalasi biasa.

5. Pasang dependency frontend dan buat asset:

   ```bash
   npm ci
   npm run build
   ```

6. Jalankan server development:

   ```bash
   php artisan serve
   ```

   Buka alamat lokal yang ditampilkan Artisan. Untuk pengembangan frontend dengan hot reload, jalankan `npm run dev` di terminal terpisah.

Registrasi publik membuat akun warga. Seeder berisi data pengembangan; jangan jalankan seeder pada database produksi.

## Pengujian dan pemeriksaan dependency

Jalankan seluruh test:

```bash
php artisan test --compact
```

Pemeriksaan tambahan yang tersedia:

```bash
composer audit
npm audit
vendor/bin/pint --format agent
npm run build
```

Test suite menggunakan SQLite in-memory berdasarkan konfigurasi PHPUnit dan tidak memerlukan perubahan pada database aplikasi lokal.

## Keamanan dan environment

- `.env` berisi konfigurasi dan rahasia runtime; file tersebut tidak boleh dimasukkan ke Git. Gunakan `.env.example` hanya sebagai template.
- Buat APP_KEY untuk environment masing-masing. Jangan menyalin key development ke production atau membagikannya.
- Production harus menggunakan `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, serta cookie session `Secure` dan `HttpOnly`.
- Atur kredensial database dan layanan melalui secret manager atau environment deployment; jangan menaruhnya di source code, README, issue, atau log.
- Foto bukti laporan disimpan pada storage privat dan harus diakses melalui route yang melakukan autentikasi dan otorisasi. Jangan memindahkannya ke direktori publik tanpa peninjauan keamanan.
- Batasi akses database dan file storage dengan prinsip least privilege. Siapkan backup dan verifikasi prosedur pemulihan sebelum perubahan production.
- Tinjau [DEPLOYMENT-CHECKLIST.md](DEPLOYMENT-CHECKLIST.md) sebelum menyiapkan deployment.
