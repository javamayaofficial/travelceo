# AUDIT PROJECT: The Travel CEO

Tanggal audit: 2026-06-21

## 1. Ringkasan Eksekutif

Project ini adalah aplikasi web membership dan edukasi berbasis PHP procedural modular yang ditujukan untuk shared hosting cPanel. Secara umum, alur inti aplikasi sudah ada dan cukup lengkap untuk MVP:

- installer berbasis browser
- registrasi dan login member
- katalog produk dan detail produk
- checkout manual dengan upload bukti transfer
- verifikasi transaksi oleh admin
- LMS sederhana berbasis embed YouTube
- affiliate, kupon, komisi
- pengaturan situs dan backup database

Namun masih ada beberapa gap penting:

- ada fitur PRD yang belum diimplementasikan
- ada beberapa area keamanan yang sebelumnya lemah dan kini sebagian sudah diperbaiki
- ada beberapa modul yang baru sebatas struktur data atau pengaturan, tetapi belum benar-benar dipakai

Audit awal dilakukan dengan membaca source code dan struktur project. Setelah audit awal, beberapa temuan prioritas tinggi dan menengah sudah diperbaiki langsung di kode. Validasi syntax PHP terbaru sudah berhasil dijalankan menggunakan binary PHP Laragon lokal.

## 1A. Update Setelah Audit Awal

Perbaikan yang sudah diterapkan setelah audit awal:

- validasi upload sekarang memeriksa MIME/content, bukan hanya ekstensi
- login sekarang memiliki throttle anti brute-force
- checkout kupon dibungkus transaksi database untuk mengurangi race condition
- proteksi `storage/.htaccess` diperketat
- bug upload favicon `.ico` sudah diperbaiki
- session timeout dan logout cookie cleanup sudah ditambahkan
- cookie referral `tc_ref` sekarang memakai opsi keamanan yang lebih ketat
- installer sekarang memakai CSRF
- Facebook Pixel sekarang dirender jika diisi dari pengaturan

## 2. Struktur Folder

```text
the-travel-ceo-PRO/
|-- .htaccess
|-- AUDIT.md
|-- PRD-The-Travel-CEO.md
|-- README.txt
|-- config.sample.php
|-- database.sql
|-- index.php
|-- install.php
|-- admin/
|   |-- index.php
|   `-- views/
|       |-- commissions.php
|       |-- coupons.php
|       |-- dashboard.php
|       |-- layout.php
|       |-- lessons.php
|       |-- members.php
|       |-- products.php
|       |-- salespages.php
|       |-- settings.php
|       `-- transactions.php
|-- app/
|   |-- bootstrap.php
|   |-- helpers.php
|   |-- controllers/
|   |   |-- AuthController.php
|   |   |-- CheckoutController.php
|   |   |-- MemberController.php
|   |   `-- PageController.php
|   `-- views/
|       |-- checkout.php
|       |-- checkout_success.php
|       |-- home.php
|       |-- layout.php
|       |-- product.php
|       |-- products.php
|       |-- salespage.php
|       |-- auth/
|       |   |-- forgot.php
|       |   |-- login.php
|       |   `-- register.php
|       |-- member/
|       |   |-- affiliate.php
|       |   |-- dashboard.php
|       |   |-- learn.php
|       |   `-- profile.php
|       `-- partials/
|           |-- flash.php
|           `-- wa_button.php
|-- assets/
|   |-- script.js
|   `-- style.css
`-- storage/
    |-- .htaccess
    |-- index.html
    |-- backups/
    |   |-- .gitkeep
    |   `-- index.html
    |-- logs/
    |   |-- .gitkeep
    |   `-- index.html
    `-- uploads/
        |-- .gitkeep
        `-- index.html
```

## 3. Teknologi Yang Digunakan

### Backend

- PHP 7.4+ gaya procedural modular
- PDO MySQL untuk akses database
- Session native PHP
- Password hashing memakai `password_hash()` dan verifikasi `password_verify()`

### Database

- MySQL/MariaDB
- Skema disediakan di `database.sql`

### Frontend

- HTML server-rendered
- CSS custom di `assets/style.css`
- JavaScript vanilla di `assets/script.js`
- Google Fonts: Poppins dan Inter

### Integrasi Eksternal

- Fonnte API untuk notifikasi WhatsApp
- YouTube embed untuk LMS
- Google Analytics sudah didukung
- Facebook Pixel sekarang didukung melalui pengaturan

### Infrastruktur / Deployment

- Target deployment: shared hosting cPanel
- Apache `.htaccess`
- Installer berbasis browser (`install.php`)
- Backup SQL manual via panel admin

## 4. Arsitektur Singkat

### Routing

- `index.php` menjadi front controller untuk area publik dan member
- `admin/index.php` menjadi front controller untuk area admin
- routing menggunakan query string `?p=...`

### Lapisan utama

- `app/bootstrap.php`: bootstrapping, session, koneksi DB, tracking referral
- `app/helpers.php`: helper umum, auth, CSRF, upload, settings, WA
- `app/controllers/*.php`: handler logic publik/member
- `admin/index.php`: handler aksi admin + render admin view
- `app/views/*` dan `admin/views/*`: template server-rendered

### Penyimpanan file

- `storage/uploads`: bukti transfer, thumbnail, featured image, logo, favicon
- `storage/logs`: log PHP
- `storage/backups`: backup SQL hasil export

## 5. Fitur Yang Sudah Berjalan

Berikut fitur yang secara kode sudah tersedia dan memiliki implementasi nyata:

### Publik

- Homepage dinamis dengan fallback hero default
- Produk terbaru di homepage
- Katalog semua produk
- Detail produk
- Salespage publik berdasarkan slug
- Tombol WhatsApp floating

### Auth & Member

- Registrasi member
- Login member dan admin
- Logout
- Forgot password versi manual via WhatsApp admin
- Dashboard member
- Riwayat transaksi member
- Halaman belajar / LMS
- Simpan progress materi
- Profil member dan ubah password
- Dashboard affiliate
- Link referral berbasis cookie `tc_ref`

### Checkout & Pembayaran

- Checkout produk
- Validasi kupon
- Upload bukti transfer
- Pembuatan transaksi status `pending`
- Notifikasi WhatsApp ke buyer dan admin
- Halaman sukses checkout

### Admin

- Dashboard statistik
- Daftar transaksi + approve/reject
- Pembukaan akses kelas setelah approve
- Kelola produk
- Kelola materi
- Kelola salespage
- Kelola kupon
- Kelola komisi affiliate
- Daftar member
- Pengaturan website
- Backup database download `.sql`

### Sistem Pendukung

- CSRF untuk hampir semua form aplikasi utama
- Session cookie `httponly` dan `samesite=Lax`
- Session timeout berbasis idle time
- Password hashing
- Throttle login anti brute-force
- Validasi upload berbasis MIME/content
- Logging aktivitas penting

## 6. Fitur Yang Parsial Atau Belum Selesai

### Belum selesai dibanding PRD

1. Reset password token/email belum ada.
- Implementasi saat ini hanya mengarahkan user ke WhatsApp admin.
- PRD menyebut reset token dengan masa berlaku 60 menit, tetapi kode belum ada.
- Referensi: `app/controllers/AuthController.php`, `app/views/auth/forgot.php`

2. Integrasi email / Mailketing belum diimplementasikan.
- Di PRD dan README disebut sebagai modul lanjutan.
- Tidak ditemukan modul email, pengiriman email, atau setting API key email.
- Referensi: `README.txt`, `PRD-The-Travel-CEO.md`

3. Artikel / blog belum diimplementasikan.
- Tidak ada controller, route, model, table, atau view artikel.
- PRD menuliskan fitur blog sebagai fase berikutnya.
- Referensi: `PRD-The-Travel-CEO.md`

4. Facebook Pixel sekarang sudah aktif setelah perbaikan.
- Field setting tetap disimpan di admin dan kini sudah dirender di layout publik.
- Referensi: `admin/views/settings.php`, `admin/index.php`, `app/views/layout.php`

5. Dashboard member masih lebih sederhana dari PRD.
- Sudah ada kelas dan riwayat transaksi.
- Namun ringkasan progress terpusat dan ringkasan affiliate di dashboard utama belum terlihat.
- Referensi: `app/views/member/dashboard.php`

6. Dashboard admin belum memiliki grafik ringkas.
- Statistik kartu sudah ada, tetapi grafik yang disebut PRD tidak ditemukan.
- Referensi: `admin/views/dashboard.php`

7. Kategori produk belum terpakai.
- Tabel `categories` dan field `category_id` ada di SQL, tetapi tidak dipakai pada UI maupun controller.
- Referensi: `database.sql`

## 7. Bug Potensial Dan Masalah Fungsional

### Temuan prioritas tinggi

1. Upload favicon `.ico` sudah diperbaiki setelah audit awal.
- Whitelist helper upload dan field form admin sekarang sudah sinkron.
- Referensi:
  - `admin/views/settings.php`
  - `admin/index.php`
  - `app/helpers.php`

2. Counter kupon sudah diperkuat setelah audit awal.
- Validasi kupon sensitif sekarang dijalankan di dalam transaksi database dengan penguncian baris.
- Referensi: `app/controllers/CheckoutController.php`

3. Error upload pada logo, favicon, thumbnail, featured image tidak ditampilkan ke admin.
- Banyak aksi admin hanya mengecek `isset($up['file'])` tanpa menangani `isset($up['error'])`.
- Akibatnya file invalid atau terlalu besar bisa gagal diam-diam tanpa pesan.
- Referensi: `admin/index.php`

### Temuan prioritas menengah

4. HTML salespage dirender mentah ke publik.
- `home.php` dan `salespage.php` langsung mencetak field HTML dari database tanpa sanitasi.
- Untuk salespage builder ini mungkin disengaja, tetapi tetap membuka risiko stored XSS bila akun admin dibajak atau ada admin non-tepercaya.
- Referensi: `app/views/home.php`, `app/views/salespage.php`

5. Logout tidak membersihkan cookie session secara eksplisit.
- `session_destroy()` dipanggil, tetapi cookie session tidak di-expire manual.
- Biasanya tetap cukup, tetapi pada beberapa konfigurasi session browser cookie bisa tersisa sampai close browser.
- Referensi: `app/controllers/AuthController.php`

6. Installer membocorkan detail error koneksi database ke browser.
- Saat koneksi DB gagal, pesan exception ditampilkan langsung ke user.
- Ini membantu instalasi, tetapi tidak ideal dari sisi hardening.
- Referensi: `install.php`

## 8. Audit Keamanan

### 8.1 Login

Status:

- password di-hash
- session di-regenerate setelah login dan registrasi
- CSRF diterapkan pada form login dan register

Temuan:

1. Rate limiting login sudah ditambahkan setelah audit awal.
- Login kini memakai throttle berbasis email+IP dengan cooldown sementara setelah gagal berulang.
- Referensi: `app/controllers/AuthController.php`, `app/helpers.php`

2. Tidak ada verifikasi password lama saat user mengganti password dari profil.
- Jika session user diambil alih, attacker bisa langsung mengganti password tanpa mengetahui password lama.
- Referensi: `app/controllers/MemberController.php`

3. Forgot password belum memiliki token reset yang aman.
- Saat ini proses reset dilakukan manual via WhatsApp.
- Aman secara operasional untuk MVP, tetapi belum memenuhi praktik reset mandiri yang terstandar.
- Referensi: `app/views/auth/forgot.php`

### 8.2 Session

Status:

- cookie session memakai `httponly`
- `samesite=Lax`
- `secure` aktif saat HTTPS

Temuan:

1. Tidak ada idle timeout atau absolute session timeout.
- Timeout idle kini sudah ditambahkan setelah audit awal.
- Session admin memakai timeout lebih singkat dibanding member.
- Referensi: `app/bootstrap.php`

2. Referral cookie `tc_ref` di-set tanpa flag `httponly`, `samesite`, dan `secure`.
- Cookie referral kini memakai `httponly`, `samesite=Lax`, dan `secure` saat HTTPS.
- Referensi: `app/bootstrap.php`

### 8.3 Upload File

Status:

- ukuran maksimum dibatasi 5 MB
- ekstensi dibatasi
- nama file di-random

Temuan:

1. Validasi file hanya berdasarkan ekstensi, belum memverifikasi MIME/content.
- Risiko ini sudah diperbaiki setelah audit awal.
- Validasi sekarang memeriksa MIME/type file aktual.
- Referensi: `app/helpers.php`

2. File upload disimpan di lokasi yang bisa diakses publik.
- Bukti transfer, logo, thumbnail, dan file lain berada di `storage/uploads` dan di-link langsung dari web.
- Risiko utama: kebocoran bukti transfer dan dokumen PDF bila path diketahui atau dibagikan.
- Referensi: `app/helpers.php`, `admin/views/transactions.php`

3. Proteksi `storage/.htaccess` terlalu minimal.
- Saat ini hanya `Options -Indexes`.
- Belum ada rule deny execution untuk file script atau deny access ke jenis file sensitif tertentu.
- Referensi: `storage/.htaccess`

### 8.4 SQL Query

Status:

- mayoritas query memakai prepared statement
- `PDO::ATTR_EMULATE_PREPARES` dimatikan

Temuan:

1. Secara umum query cukup aman.
- Saya tidak menemukan SQL injection langsung dari input user yang mentah ke query utama.
- Ada beberapa query dinamis, tetapi nilainya dibatasi atau di-cast.

2. Query dinamis masih ada dan perlu disiplin review.
- Contoh:
  - filter transaksi memakai allowlist + `db()->quote()`
  - dashboard affiliate memakai cast integer
  - query `IN (...)` dibangun dinamis berdasarkan jumlah parameter
- Saat ini masih aman, tetapi raw query semacam ini perlu dijaga agar tidak berkembang menjadi celah di perubahan berikutnya.

## 9. File Kosong, Placeholder, Dan Dummy

### File 0 byte

- `storage/backups/.gitkeep`
- `storage/logs/.gitkeep`
- `storage/uploads/.gitkeep`

Ini normal sebagai placeholder direktori.

### File placeholder sederhana

- `storage/index.html`
- `storage/uploads/index.html`
- `storage/logs/index.html`
- `storage/backups/index.html`

Isi file hanya komentar HTML `<!-- The Travel CEO -->`.

### File template / placeholder konfigurasi

- `config.sample.php`

Ini template konfigurasi untuk instalasi manual, bukan bug.

## 10. Kesesuaian Dengan PRD

### Sudah sesuai / sebagian besar sesuai

- F1 Homepage dinamis
- F2 Salespage builder
- F3 Membership dasar
- F5 Katalog produk
- F6 LMS sederhana
- F7 Checkout + upload bukti
- F8 Verifikasi manual admin
- F9 Affiliate
- F10 Kupon + affiliate coupon
- F11 WhatsApp Fonnte
- F14 Dashboard admin
- F15 Pengaturan website
- F16 Installer + backup

### Parsial

- F3 forgot password masih manual
- F4 dashboard member belum selengkap PRD
- F14 dashboard admin belum ada grafik
- F15 Facebook Pixel hanya field setting

### Belum ada

- F12 Integrasi email / Mailketing
- F13 Artikel / blog

## 11. Penilaian Umum

### Kekuatan

- struktur kode relatif sederhana dan mudah dipahami
- cocok untuk shared hosting
- alur bisnis utama membership sudah nyata
- penggunaan prepared statement cukup konsisten
- ada CSRF di mayoritas form inti
- ada hardening session dasar

### Kelemahan

- beberapa fitur PRD masih belum selesai
- hardening upload file masih lemah
- login belum memiliki perlindungan brute force
- storage publik belum benar-benar dikunci
- ada setting yang tampil di admin tetapi belum benar-benar aktif

## 12. Prioritas Rekomendasi

### Prioritas 1

1. Perbaiki validasi upload file:
- verifikasi MIME dengan `finfo_file()`
- bedakan whitelist per jenis file
- tambahkan rule deny execution di `storage/.htaccess`

2. Tambahkan perlindungan brute force login:
- throttle per IP/email
- lock sementara setelah gagal berulang

3. Perbaiki bug favicon:
- sinkronkan accept form dengan whitelist upload

4. Lindungi installer:
- tambah CSRF
- kurangi detail error teknis yang tampil ke browser

### Prioritas 2

5. Bungkus proses checkout + penggunaan kupon dalam transaksi database.

6. Tambahkan notifikasi error upload ke admin untuk logo, favicon, thumbnail, dan featured image.

7. Tambahkan timeout session admin dan pembersihan cookie saat logout.

### Prioritas 3

8. Implementasikan Facebook Pixel renderer bila memang ingin ditawarkan di panel.

9. Lengkapi reset password mandiri yang aman.

10. Putuskan nasib modul kategori, email, dan blog:
- implementasikan
- atau hapus jejak UI/PRD yang belum dipakai agar ekspektasi pengguna tidak salah

## 13. Kesimpulan

Project ini sudah berada pada level "MVP yang bisa dipakai" untuk skenario:

- jual produk digital
- verifikasi manual pembayaran transfer
- buka akses kelas
- jalankan affiliate dan kupon

Tetapi belum sepenuhnya "production hardened". Area yang paling perlu dibereskan sebelum dipakai lebih luas adalah:

- upload security
- proteksi login
- hardening installer
- penyelesaian fitur parsial yang sudah tampil di panel admin

Secara keseluruhan, fondasi project cukup baik untuk diteruskan, tetapi masih membutuhkan satu putaran perapihan keamanan dan penyelesaian fitur agar benar-benar siap dipakai secara lebih aman dan stabil.
