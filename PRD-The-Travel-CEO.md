# 📄 PRODUCT REQUIREMENT DOCUMENT (PRD)
## The Travel CEO — TravelCEO.id

| Info | Keterangan |
|------|------------|
| **Nama Produk** | The Travel CEO (TravelCEO.id) |
| **Versi yang Dibangun** | PRO |
| **Jenis Bisnis** | Platform Membership & Edukasi (Layanan Langganan) |
| **Prinsip Desain** | Mobile First, WhatsApp First |
| **Target Hosting** | Shared Hosting Indonesia (cPanel + File Manager + phpMyAdmin) |
| **Status Dokumen** | Final — siap dieksekusi developer |

---

## 1. RINGKASAN PRODUK

**The Travel CEO** adalah platform membership dan akademi digital yang membantu pemilik dan tim travel umroh & wisata di Indonesia naik kelas menjadi pengusaha yang andal — lewat ecourse, mentoring, komunitas, dan tools praktis yang bisa diakses kapan saja dari HP.

Platform ini bukan sekadar tempat menonton video kursus. Ini adalah **mesin bisnis langganan utuh**: member bisa mendaftar, membayar, belajar, dan ikut memasarkan kelas melalui sistem affiliate — semuanya berjalan otomatis dengan notifikasi WhatsApp, sementara Admin cukup menggunakan HP untuk approve dan memantau.

> **Tagline:** *"Naik Kelas Jadi CEO Travel. Belajar, Bertumbuh, Berjamaah."*

---

## 2. PROBLEM STATEMENT

Pebisnis travel di Indonesia umumnya sangat menguasai pelayanan jamaah, tetapi **tidak pernah diajari cara membesarkan perusahaannya**. Masalah utama yang dipecahkan:

1. **Ilmu bisnis travel berserakan.** Materi digital marketing, AI, branding, dan leadership tersebar di mana-mana dan tidak spesifik untuk dunia travel.
2. **Tidak ada wadah terpusat.** Belum ada platform yang menyatukan training, komunitas, dan produk digital khusus owner travel dalam satu tempat.
3. **Susah jual produk digital sendiri.** Owner yang ingin menjual ecourse/ebook bingung soal sistem pembayaran, aktivasi, dan tracking penjualan.
4. **Pemasaran mahal.** Sulit menjangkau sesama owner travel tanpa biaya iklan besar — butuh sistem affiliate agar member ikut memasarkan.

---

## 3. TUJUAN PRODUK

Bagi pengguna, The Travel CEO ingin mencapai:

- **Mempercepat pertumbuhan bisnis travel** member melalui pembelajaran yang terstruktur dan aplikatif.
- **Memberi akses belajar yang fleksibel** — bisa dibuka dari HP kapan saja, tanpa harus hadir fisik.
- **Membuka peluang penghasilan tambahan** bagi member lewat program affiliate.
- **Membangun komunitas owner travel** terbesar di Indonesia yang saling menguatkan.

**Tujuan bisnis utama:** membangun **pendapatan berulang (recurring revenue)** dari langganan membership dan penjualan produk digital.

---

## 4. TARGET PENGGUNA / PERSONA

### Persona Utama: "Pak Fardi — Owner Travel Umroh"
- **Profil:** 38 tahun, pemilik travel umroh skala menengah di kota tier-2. Aktif di WhatsApp, semua urusan bisnis lewat HP.
- **Kebutuhan:** ingin travelnya naik kelas — lebih banyak jamaah, branding kuat, tim yang rapi, dan paham digital marketing serta AI.
- **Ketakutan:** takut tertinggal zaman, takut salah investasi ilmu, takut teknologi ribet, takut ditipu kelas online abal-abal.

### Persona Kedua: "Mbak Sinta — Tim Marketing Travel"
- **Profil:** 26 tahun, staf marketing, jago media sosial tapi ingin meningkatkan skill closing & konten.
- **Kebutuhan:** kelas praktis yang langsung bisa dipakai kerja.
- **Ketakutan:** materi terlalu teori, tidak relevan dengan dunia travel.

### Persona Ketiga: "Bu Dewi — Calon Pengusaha Travel"
- **Profil:** 33 tahun, ingin membuka travel sendiri tapi belum punya ilmu dasar.
- **Kebutuhan:** panduan dari nol, mentoring, dan komunitas.
- **Ketakutan:** rugi karena memulai tanpa ilmu yang benar.

### Persona Admin: "Owner Platform (Anda)"
- **Kebutuhan:** kelola produk, verifikasi pembayaran, pantau penjualan & affiliate — semua dari satu dashboard yang sederhana, idealnya dari HP.

---

## 5. USER JOURNEY

**Alur Member (dari pertama tahu sampai dapat manfaat):**

1. **Datang** ke TravelCEO.id (dari iklan, WA, atau link affiliate) → melihat homepage mobile-first dengan promo aktif.
2. **Tertarik** → membuka salespage produk, membaca manfaat, testimoni, dan FAQ.
3. **Klik CTA "Gabung / Beli Kelas"** → diarahkan ke registrasi (nama, email, no. WA, password).
4. **Registrasi berhasil** → 📲 WA otomatis: *"Selamat datang di The Travel CEO!"* + email selamat datang.
5. **Checkout** → memilih metode transfer (BCA/Mandiri/BSI), memasukkan kode kupon bila ada, mengunggah bukti transfer, menambah catatan.
6. **Status Pending** → menunggu verifikasi. Sistem menampilkan info rekening & instruksi.
7. **Admin approve** dari dashboard → 📲 WA & email otomatis: *"Pembayaran diterima, kelas Anda sudah terbuka!"*
8. **Belajar** → masuk LMS, menonton video YouTube, progress tersimpan, lanjut Next/Previous Lesson.
9. **Jadi Affiliate** → mendapat link & kode kupon pribadi (mis. FARDI10), membagikannya, lalu memantau klik, penjualan, dan komisi.
10. **Manfaat tercapai** → ilmu bertambah, bisnis tumbuh, dan member ikut menyebarkan platform.

**Alur Admin:**
Login → cek dashboard statistik → verifikasi transaksi pending → kelola produk/materi → atur salespage homepage → pantau affiliate & komisi → kelola pengaturan.

---

## 6. SITEMAP / STRUKTUR HALAMAN

**Area Publik (Guest)**
1. Homepage (dinamis, mobile-first)
2. Halaman Salespage `/{slug}`
3. Katalog Produk
4. Halaman Registrasi
5. Halaman Login
6. Halaman Lupa Password
7. Halaman Checkout
8. Halaman Artikel / Blog & Detail Artikel

**Area Member (login)**
9. Dashboard Member
10. Kelas Saya (daftar produk dimiliki)
11. Halaman Belajar / LMS
12. Dashboard Affiliate
13. Riwayat Transaksi
14. Profil & Ganti Password

**Area Admin**
15. Dashboard Admin (statistik)
16. Kelola Member
17. Kelola Produk Digital
18. Kelola Ecourse & Materi LMS
19. Salespage Builder
20. Kelola Affiliate & Komisi
21. Kelola Kupon
22. Kelola Transaksi (verifikasi)
23. Kelola Artikel
24. Pengaturan Fonnte (WhatsApp)
25. Pengaturan Mailketing (Email)
26. Pengaturan Website

---

## 7. DAFTAR FITUR (FEATURE SPECIFICATION)

> Catatan prioritas: **Fase 1 (MVP PRO)** = F1–F11 + F14–F16. **Fase 2** = F12–F13. Semua tetap dalam scope PRO.

---

### F1 — Homepage Mobile-First Dinamis
**Deskripsi:** Halaman depan responsif (mobile first) berisi Header, Hero Section, CTA Membership, Testimoni, FAQ, Footer. Body homepage **otomatis menampilkan konten dari salespage** yang dipilih Admin, sehingga ganti promo cukup dari dashboard tanpa edit kode.
**Aturan Validasi:** Bila tidak ada salespage yang dipilih, tampilkan hero default + daftar produk terbaru.
**Empty State:** "Belum ada promo aktif. Lihat semua kelas kami 👉".
**Success State:** Konten salespage tampil mulus di homepage.
**CTA WA:** Tombol mengambang "💬 Tanya via WhatsApp" di pojok kanan bawah.

### F2 — Salespage Builder
**Deskripsi:** Admin membuat halaman jualan menggunakan HTML. Field: Judul, Slug, Script HTML, Meta Title, Meta Description, Featured Image, status Publish/Draft, dan toggle "Tampilkan di Homepage".
**Aturan Validasi:** Slug wajib unik & otomatis disanitasi (huruf kecil, tanpa spasi). Judul & Slug wajib diisi. Hanya satu salespage yang boleh aktif di homepage pada satu waktu.
**Empty State:** "Belum ada salespage. Buat salespage pertama Anda."
**Success State:** Notifikasi "Salespage tersimpan" + link preview.

### F3 — Sistem Membership (Auth & Role)
**Deskripsi:** Registrasi, Login, Lupa Password, dan manajemen sesi. Tiga role: **Guest**, **Member**, **Admin**.
**Aturan Validasi:** Email wajib valid & unik. No. WA wajib format Indonesia (08xx / 62xx). Password minimal 8 karakter. Password disimpan dengan hashing (password_hash). Token reset password kedaluwarsa dalam 60 menit.
**Empty State:** —
**Success State:** Redirect ke Dashboard Member + 📲 WA & email selamat datang.
**CTA WA:** Setelah registrasi, tombol "Lanjut Belajar" + pesan WA selamat datang otomatis.

### F4 — Dashboard Member
**Deskripsi:** Halaman utama member berisi ringkasan kelas yang dimiliki, progress belajar, status transaksi, dan ringkasan affiliate.
**Empty State:** "Anda belum memiliki kelas. Yuk mulai naik kelas! 👉 Lihat Katalog".
**Success State:** Daftar kelas + progress bar tampil.

### F5 — Katalog Produk Digital
**Deskripsi:** Daftar produk: Ecourse, Webinar Rekaman, Membership Premium, Ebook, Toolkit, Template. Field per produk: Thumbnail, Judul, Harga, Deskripsi Singkat, Deskripsi Lengkap.
**Aturan Validasi:** Harga ≥ 0 (0 = gratis). Judul & jenis produk wajib. Thumbnail format gambar (jpg/png/webp), maks 2 MB.
**Empty State:** "Produk segera hadir."
**Success State:** Grid produk tampil dengan tombol "Lihat Detail / Beli".

### F6 — LMS Ecourse (Belajar)
**Deskripsi:** Member menonton materi via **YouTube Embed**. Kategori: AI untuk Travel, Digital Marketing Travel, Website Travel, Tim Syiar, Leadership Travel. Materi memiliki Judul, Deskripsi Singkat, Link YouTube, dan Urutan. Fitur: daftar materi, progress belajar, tombol Next/Previous, dan **materi terkunci** bagi yang belum membeli.
**Aturan Validasi:** Link YouTube wajib valid. Materi hanya terbuka bila member memiliki produk terkait & transaksinya disetujui. Progress tersimpan per materi.
**Empty State (belum beli):** Materi tampil terkunci 🔒 dengan CTA "Beli untuk Membuka".
**Success State:** Video tampil, progress bertambah, tombol "Materi Selanjutnya" aktif.

### F7 — Checkout & Upload Bukti Transfer
**Deskripsi:** Pembeli memilih produk → memilih metode transfer (BCA/Mandiri/BSI) → memasukkan kode kupon (opsional) → mengunggah bukti transfer → menambah catatan. Status transaksi: **Pending / Disetujui / Ditolak**.
**Aturan Validasi:** Bukti transfer wajib (jpg/png/pdf, maks 5 MB). Kupon divalidasi (aktif, dalam periode, belum melebihi batas). Total dihitung otomatis setelah diskon.
**Empty State:** —
**Success State:** Halaman konfirmasi "Pesanan diterima, menunggu verifikasi" + 📲 WA otomatis ke pembeli & Admin.
**CTA WA:** Tombol "Konfirmasi via WhatsApp" yang membuka chat ke Admin berisi nomor order.

### F8 — Verifikasi & Aktivasi Manual (Admin)
**Deskripsi:** Admin meninjau bukti transfer lalu **Approve** atau **Reject**. Setelah disetujui: member aktif, produk terbuka, komisi affiliate dihitung, serta notifikasi WA & email terkirim.
**Aturan Validasi:** Hanya Admin yang dapat mengubah status. Satu transaksi tidak bisa di-approve dua kali.
**Empty State:** "Tidak ada transaksi menunggu verifikasi. 🎉"
**Success State:** Status berubah hijau + 📲 WA & email otomatis terkirim ke pembeli.

### F9 — Affiliate Marketing
**Deskripsi:** Setiap member dapat menjadi affiliate dengan link `https://travelceo.id/ref/username`. Dashboard Affiliate menampilkan: Total Klik, Total Penjualan, Total Komisi, Pending Komisi, Paid Komisi. Mendukung komisi **persentase** atau **tetap (nominal)**. Status komisi: Pending / Approved / Paid.
**Aturan Validasi:** Klik dilacak via cookie referral (mis. 30 hari). Komisi hanya dihitung untuk transaksi yang disetujui. Self-referral ditolak.
**Empty State:** "Belum ada klik. Bagikan link Anda untuk mulai mendapat komisi 👉" + tombol "Bagikan via WhatsApp".
**Success State:** Statistik real-time + tabel referral.
**CTA WA:** Tombol "Bagikan Link via WhatsApp" dengan teks promosi siap pakai.

### F10 — Sistem Kupon + Integrasi Affiliate
**Deskripsi:** Admin membuat kupon: Nama, Kode, Persentase/Nominal Diskon, Produk Terkait, Tanggal Mulai/Berakhir, Batas Penggunaan. Tipe: Kupon Global, Kupon Produk Tertentu, Kupon Affiliate. Setiap affiliate bisa punya kode kupon pribadi (mis. **FARDI10**) — saat dipakai: pembeli dapat diskon, penjualan tercatat ke affiliate, komisi dihitung otomatis.
**Aturan Validasi:** Kode kupon unik. Diskon tidak melebihi harga produk. Kupon kedaluwarsa/penuh otomatis ditolak dengan pesan jelas.
**Empty State:** "Belum ada kupon dibuat."
**Success State:** "Kupon berhasil dipakai. Anda hemat Rp X" + total ter-update.

### F11 — Integrasi WhatsApp (Fonnte)
**Deskripsi:** Kirim pesan WA otomatis saat: Registrasi, Pembelian, Aktivasi Member, Approve Pembayaran, Reject Pembayaran. Pengaturan: Token Fonnte + Template Pesan (mendukung variabel seperti {nama}, {produk}, {nominal}).
**Aturan Validasi:** Token wajib valid. Nomor tujuan dinormalisasi ke format 62. Bila gagal kirim, catat di log & tampilkan peringatan ke Admin (transaksi tetap berjalan).
**Empty State:** "Token Fonnte belum diatur. Atur sekarang untuk mengaktifkan notifikasi WA."
**Success State:** "Pesan WA terkirim ✅".

### F12 — Integrasi Email (Mailketing) — *Fase 2*
**Deskripsi:** Kirim email otomatis (via API Key Mailketing) saat: Registrasi, Selamat Datang, Pembelian Berhasil, Aktivasi Akun, Reset Password.
**Aturan Validasi:** API Key wajib valid. Template email tersimpan. Kegagalan dicatat di log.
**Empty State:** "API Key Mailketing belum diatur."
**Success State:** "Email terkirim ✅".

### F13 — Artikel & Blog — *Fase 2*
**Deskripsi:** Manajemen artikel dengan Kategori, Tag, Featured Image, dan SEO (Meta Title, Meta Description). SEO friendly untuk mendatangkan pengunjung organik.
**Aturan Validasi:** Slug unik. Judul & isi wajib.
**Empty State:** "Belum ada artikel."
**Success State:** Artikel tampil di blog + dapat dibagikan via WA.

### F14 — Dashboard Admin
**Deskripsi:** Statistik: Total Member, Member Aktif, Total Penjualan, Total Produk, Total Affiliate, Total Komisi, Total Transaksi. Menu lengkap sesuai sitemap area Admin.
**Empty State:** Angka 0 ditampilkan rapi dengan ajakan menambah data.
**Success State:** Kartu statistik & grafik ringkas tampil responsif.

### F15 — Pengaturan Website
**Deskripsi:** Nama Website, Logo, Favicon, Email, WhatsApp, Alamat, Google Analytics, Facebook Pixel, dan SEO Homepage.
**Aturan Validasi:** Nomor WhatsApp format valid. Logo/favicon format gambar. Field SEO opsional tapi disarankan.
**Empty State:** Form terisi nilai default saat instalasi.
**Success State:** "Pengaturan tersimpan."

### F16 — Installer Wizard & Backup/Restore (Infrastruktur PRO)
**Deskripsi:** `install.php` memandu instalasi via browser: cek requirement → input database → buat akun admin → tulis `config.php` otomatis. Fitur backup/restore database dari dashboard Admin.
**Aturan Validasi:** Installer mengecek versi PHP, ekstensi (mysqli/pdo), dan izin tulis folder. Wajib dihapus/dikunci setelah instalasi. Backup tersimpan di folder `backup/` di luar akses publik.
**Empty State:** —
**Success State:** "Instalasi selesai 🎉 — silakan login sebagai admin."

---

## 8. ACCEPTANCE CRITERIA

Aplikasi dianggap berhasil bila memenuhi:

- ✅ **Mobile First:** semua halaman nyaman dibuka di layar HP (≤ 420px) tanpa scroll horizontal.
- ✅ **Cepat:** homepage tampil dalam ≤ 3 detik di koneksi 4G standar.
- ✅ **Semua tombol berfungsi:** tidak ada tautan/CTA yang mati atau error.
- ✅ **Alur inti lancar:** Registrasi → Checkout → Upload Bukti → Approve → Materi Terbuka berjalan tanpa error.
- ✅ **Keamanan dasar:** password ter-hash, materi premium tidak bisa diakses tanpa hak, hanya Admin yang bisa approve.
- ✅ **Notifikasi WA terkirim** pada 5 event utama (registrasi, beli, aktivasi, approve, reject).
- ✅ **Affiliate akurat:** klik, penjualan, dan komisi tercatat benar saat kupon affiliate dipakai.
- ✅ **Tanpa error PHP** yang tampil ke pengguna (error ditangani & dicatat di log).
- ✅ **CTA WhatsApp** tersedia di titik-titik penting (homepage, checkout, affiliate).
- ✅ **Installer berjalan** di shared hosting cPanel tanpa terminal.

---

## 9. DESIGN BRIEF

**Kesan yang diinginkan:** Premium, terpercaya, modern, namun tetap ramah dan tidak menakuti pengguna gaptek. Harus terasa seperti "akademi bisnis profesional", bukan toko online biasa.

**Palet Warna (saran):**
- **Primer — Biru Tua / Navy** `#0F2C4C` → kesan profesional, kepercayaan, "CEO".
- **Aksen — Emas / Amber** `#E0A23B` → kesan premium & eksklusif.
- **Sekunder — Teal** `#1E8E8E` → segar, modern, nuansa "travel".
- **Netral** `#F7F8FA` (latar), `#1C1C1E` (teks), `#6B7280` (teks sekunder).
- **Status:** Hijau `#16A34A` (sukses), Kuning `#F59E0B` (pending), Merah `#DC2626` (ditolak).

**Tipografi:**
- Heading: **Poppins** atau **Plus Jakarta Sans** (tegas, modern, mudah dibaca).
- Body: **Inter** atau **Open Sans** (nyaman dibaca di HP).
- Ukuran dasar 16px, baris longgar (line-height 1.6).

**Komponen Visual:**
- Tombol besar, mudah disentuh jempol (min. tinggi 44px).
- Kartu (card) dengan sudut membulat (radius 12–16px) dan bayangan halus.
- Tombol WhatsApp warna hijau khas `#25D366` dengan ikon.
- Ikon konsisten (mis. set Lucide/Feather).
- Banyak ruang kosong (white space) agar terkesan premium dan tidak sumpek.

---

## 10. STRUKTUR FILE

### Versi QUICK (HTML/CSS/JS murni — file di root, tanpa subfolder)
```
index.html
style.css
script.js
```
> Cukup untuk etalase/validasi: homepage + CTA "Daftar via WhatsApp". Tanpa database, tanpa instalasi.

### Versi GROWTH (PHP + MySQL — shared hosting)
```
index.php
config.php
schema.sql
assets/
  ├── css/
  ├── js/
  └── img/
```
> Sudah ada login member & database sederhana. Pasang dengan import `schema.sql` via phpMyAdmin lalu edit `config.php`.

### Versi PRO (PHP Modular + MySQL) — **yang dibangun**
```
public_html/
├── install.php              ← Installer Wizard (dihapus/dikunci setelah instalasi)
├── config.php               ← dibuat & diisi otomatis oleh installer
├── index.php                ← front controller / router sederhana
├── .htaccess                ← URL rapi & proteksi folder sensitif
│
├── app/
│   ├── core/                ← Database.php, Auth.php, Router.php, Helpers.php
│   ├── controllers/
│   ├── models/
│   └── views/
│       ├── front/           ← homepage, salespage, katalog
│       ├── member/          ← dashboard, LMS, affiliate
│       └── partials/        ← header, footer, tombol WA
│
├── admin/
│   ├── index.php
│   ├── controllers/
│   └── views/
│
├── modules/
│   ├── membership/
│   ├── lms/
│   ├── checkout/
│   ├── affiliate/
│   ├── coupon/
│   ├── salespage/
│   ├── fonnte/              ← integrasi WhatsApp
│   ├── mailketing/          ← integrasi Email
│   └── blog/
│
├── assets/
│   ├── css/
│   ├── js/
│   ├── img/
│   └── uploads/             ← bukti transfer, thumbnail (izin tulis)
│
├── database/
│   └── schema.sql
│
├── backup/                  ← hasil backup database (di luar akses publik)
└── storage/
    └── logs/                ← catatan error & log kirim WA/email
```

> **Opsi Enterprise (tersembunyi, bukan default):** VPS/Docker/CI hanya untuk skala sangat besar. Untuk mayoritas pengguna, struktur cPanel di atas sudah lebih dari cukup.

---

## 11. DEPLOYMENT (cPanel TANPA TERMINAL)

> Ditulis untuk pengguna yang hanya tahu **File Manager** dan **phpMyAdmin**.

### 🟢 QUICK
1. Buka **cPanel → File Manager → public_html**.
2. **Upload** file ZIP.
3. Klik kanan ZIP → **Extract**.
4. Pastikan **`index.html`** berada langsung di dalam `public_html`.
5. Buka domain Anda di browser → **selesai, sudah online.**

### 🟡 GROWTH
1. **File Manager → public_html → Upload ZIP → Extract.**
2. Buka **cPanel → MySQL Databases** → buat **database baru** dan **user**, lalu hubungkan user ke database (centang ALL PRIVILEGES). Catat nama DB, user, dan password.
3. Buka **phpMyAdmin** → pilih database → tab **Import** → pilih file **`schema.sql`** → klik **Go**.
4. Di File Manager, klik kanan **`config.php` → Edit** → isi nama database, user, dan password → **Save**.
5. Buka domain Anda → **selesai.**

### 🔵 PRO (Direkomendasikan)
1. **File Manager → public_html → Upload ZIP → Extract.**
2. Buka **MySQL Databases** → buat **database** & **user**, hubungkan dengan ALL PRIVILEGES. Catat detailnya.
3. Buka browser ke **`https://domainanda.id/install.php`**.
4. Ikuti **Installer Wizard:**
   - **Langkah 1 — Cek Requirement:** sistem memeriksa versi PHP & izin folder (centang hijau semua).
   - **Langkah 2 — Database:** masukkan nama DB, user, password, host (biasanya `localhost`). Wizard membuat tabel otomatis.
   - **Langkah 3 — Akun Admin:** isi nama, email, dan password admin.
   - **Langkah 4 — Selesai.**
5. **Hapus atau kunci `install.php`** (klik kanan → Delete/Rename) — wizard akan mengingatkan ini.
6. Login ke **`/admin`** → mulai isi produk, materi, dan pengaturan.

> Tidak perlu terminal, SSH, atau perintah rumit. Semua lewat browser & cPanel.

---

## 12. DEFINISI SELESAI (DEFINITION OF DONE)

Aplikasi dinyatakan **selesai dan siap dipublikasikan** bila:

1. ✅ Semua fitur Fase 1 (F1–F11, F14–F16) berfungsi sesuai spesifikasi.
2. ✅ Alur inti **Registrasi → Checkout → Upload Bukti → Approve → Materi Terbuka** lulus uji tanpa error.
3. ✅ Notifikasi **WhatsApp (Fonnte)** terkirim otomatis pada 5 event utama.
4. ✅ Sistem **Affiliate + Kupon** menghitung komisi dengan benar (diuji dengan kode contoh seperti FARDI10).
5. ✅ Semua halaman **nyaman & rapi di HP** (mobile-first), tanpa scroll horizontal.
6. ✅ **Installer Wizard** berhasil dijalankan di shared hosting cPanel hingga login admin.
7. ✅ **Keamanan dasar** terpenuhi: password ter-hash, materi premium terkunci, akses admin terbatas, `install.php` dikunci/dihapus.
8. ✅ **Tidak ada error PHP** yang tampil ke pengguna; error tercatat di `storage/logs/`.
9. ✅ **Pengaturan Website** (logo, WA, SEO, analytics) berfungsi & tersimpan.
10. ✅ Sudah diuji di **minimal 2 browser HP** (Chrome Android & Safari iOS) dan tampil baik.

---

*PRD ini disusun agar bisa langsung dieksekusi developer. Mulai dari Fase 1, luncurkan cepat, lalu kembangkan ke Fase 2 — visi besar The Travel CEO tetap hidup dan bertumbuh.* 🚀
