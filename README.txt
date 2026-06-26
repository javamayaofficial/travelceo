============================================================
  THE TRAVEL CEO — PANDUAN PEMASANGAN & PENGGUNAAN
  Versi PRO (PHP + MySQL) — Tanpa Terminal
============================================================

Terima kasih! Paket ini berisi aplikasi lengkap "The Travel CEO":
platform membership & edukasi untuk pengusaha travel.

Semua bisa dipasang lewat cPanel (File Manager + phpMyAdmin).
TIDAK perlu terminal, SSH, atau aplikasi tambahan.


------------------------------------------------------------
A. CARA PASANG (IKUTI URUT, 10 MENIT SELESAI)
------------------------------------------------------------

LANGKAH 1 — UPLOAD
  1. Login ke cPanel hosting Anda.
  2. Buka "File Manager" lalu masuk ke folder "public_html".
  3. Klik "Upload", pilih file ZIP aplikasi ini, tunggu sampai 100%.
  4. Kembali ke File Manager, klik kanan file ZIP > "Extract".
  5. Pastikan file "index.php" dan "install.php" berada langsung
     di dalam public_html (bukan di dalam folder lain).

LANGKAH 2 — BUAT DATABASE
  1. Di cPanel, buka menu "MySQL Databases".
  2. Buat "New Database" (catat namanya).
  3. Buat "New User" + password (catat keduanya).
  4. Di bagian "Add User to Database": pilih user & database tadi,
     centang "ALL PRIVILEGES", lalu klik "Make Changes".

LANGKAH 3 — JALANKAN INSTALLER
  1. Buka browser ke:  https://domainanda.id/install.php
  2. Installer akan mengecek kebutuhan server (harus centang hijau).
  3. Isi data Database (host: biasanya "localhost", lalu nama DB,
     user, dan password yang tadi dicatat).
  4. Isi data Akun Admin (nama, email, WhatsApp, password).
  5. Klik "Pasang Sekarang". Selesai!

LANGKAH 4 — AMANKAN (WAJIB)
  1. Kembali ke File Manager.
  2. HAPUS file "install.php" (atau ganti namanya jadi
     "install-OFF.php"). Ini penting demi keamanan.

LANGKAH 5 — MULAI PAKAI
  - Halaman depan: https://domainanda.id/
  - Login admin:   https://domainanda.id/admin/
    (pakai email & password admin yang tadi dibuat)


------------------------------------------------------------
B. CARA GANTI NOMOR WHATSAPP & DATA WEBSITE
------------------------------------------------------------
Semua diatur dari dalam aplikasi (tidak perlu edit kode):

  1. Login admin > menu "Pengaturan".
  2. Di sana Anda bisa mengganti:
     - Nama Website, Logo, Favicon
     - Nomor WhatsApp (untuk tombol chat & notifikasi)
     - Email & Alamat
     - Nomor Rekening BCA / Mandiri / BSI
     - Token Fonnte (untuk WhatsApp otomatis)
     - Persentase komisi affiliate
     - SEO, Google Analytics, Facebook Pixel
  3. Klik "Simpan Pengaturan".


------------------------------------------------------------
C. MENGAKTIFKAN WHATSAPP OTOMATIS (FONNTE)
------------------------------------------------------------
  1. Daftar akun di www.fonnte.com dan hubungkan nomor WA Anda.
  2. Salin "Token" dari dashboard Fonnte.
  3. Tempel di: Admin > Pengaturan > Token Fonnte > Simpan.
  Notifikasi WA otomatis akan terkirim saat: registrasi,
  pembelian, pembayaran disetujui, dan ditolak.

  (Bila token belum diisi, aplikasi tetap berjalan normal,
   hanya notifikasi WA yang tidak terkirim.)


------------------------------------------------------------
D. ALUR KERJA HARIAN
------------------------------------------------------------
  1. Tambah Produk:  Admin > Produk Digital > + Produk Baru.
  2. Tambah Materi:   Admin > Produk > tombol "Materi" >
                      tempel link YouTube tiap materi.
  3. Verifikasi:      Admin > Transaksi > cek bukti transfer >
                      klik "Setujui". Kelas member langsung terbuka
                      dan WA otomatis terkirim.
  4. Salespage:       Admin > Salespage > buat halaman jualan HTML >
                      centang "Tampilkan di Homepage" agar muncul
                      di beranda secara otomatis.
  5. Kupon/Affiliate: Admin > Kupon (bisa kupon affiliate per orang).
                      Komisi otomatis tercatat saat transaksi disetujui.


------------------------------------------------------------
E. BACKUP & RESTORE DATA
------------------------------------------------------------
  BACKUP : Admin > Pengaturan > "Unduh Backup Sekarang".
           Simpan file .sql tersebut di tempat aman.
  RESTORE: Buka phpMyAdmin > pilih database > tab "Import" >
           pilih file backup .sql > klik "Go".


------------------------------------------------------------
F. STRUKTUR FOLDER (UNTUK REFERENSI)
------------------------------------------------------------
  index.php          -> halaman depan & member
  install.php        -> installer (hapus setelah pasang)
  config.php         -> dibuat otomatis installer (jangan dibagikan)
  database.sql       -> struktur database
  app/               -> kode inti (controller & tampilan)
  admin/             -> panel admin
  assets/            -> style.css & script.js
  storage/           -> upload bukti, log, & backup
  README.txt         -> file ini


------------------------------------------------------------
G. KEAMANAN (SUDAH AKTIF)
------------------------------------------------------------
  - Password disimpan ter-enkripsi (hashing).
  - Proteksi CSRF di semua form.
  - Materi premium terkunci untuk yang belum membeli.
  - Validasi data di sisi server.
  - Log aktivitas penting tersimpan.
  Tetap lakukan: hapus install.php & gunakan password kuat.


------------------------------------------------------------
H. PENGEMBANGAN LANJUTAN (FASE 2)
------------------------------------------------------------
Modul berikut sudah disiapkan secara terstruktur dan dapat
ditambahkan pada tahap berikutnya tanpa membongkar sistem:
  - Integrasi Email otomatis (Mailketing)
  - Artikel / Blog dengan SEO
Hubungi pengembang Anda untuk mengaktifkan modul ini.


------------------------------------------------------------
I. JIKA ADA KENDALA
------------------------------------------------------------
  - "Database connection failed": cek kembali nama DB, user,
    password di installer. Pastikan user sudah ditambahkan ke
    database dengan ALL PRIVILEGES.
  - Halaman putih / error: buka file storage/logs/php-error.log
    untuk melihat pesan errornya.
  - Tombol WA tidak muncul: isi Nomor WhatsApp di Pengaturan.
  - Upload gagal: pastikan folder "storage" ber-permission 755.

Selamat! Aplikasi Anda siap membantu para Travel CEO naik kelas.
============================================================
