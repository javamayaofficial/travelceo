# TODO TravelCEO

Tujuan dokumen ini: menyusun prioritas kerja agar TravelCEO siap digunakan sebagai platform membership dan LMS untuk owner travel.

Catatan:

- Urutan di dalam tiap kelompok sudah disusun berdasarkan prioritas eksekusi.
- Fokus utama: keamanan, stabilitas alur bisnis, pengalaman admin/member, dan kesiapan operasional.

## CRITICAL

1. Implementasikan reset password yang aman
- Tambahkan alur `forgot password` berbasis token, expiry, dan verifikasi yang benar.
- Hilangkan ketergantungan reset manual via WhatsApp untuk operasi harian.

2. Lindungi akses file sensitif upload dan bukti transfer
- Pastikan bukti transfer, backup, log, dan file sensitif tidak bisa diakses publik sembarang URL.
- Evaluasi pemisahan file private vs public asset.

3. Uji penuh alur inti end-to-end sebelum go-live
- Registrasi -> login -> checkout -> upload bukti -> approve admin -> kelas terbuka -> progress tersimpan.
- Semua alur wajib lolos tanpa error dan tanpa langkah manual tersembunyi.

4. Pastikan backup dan restore benar-benar bisa dipakai
- Uji file backup hasil admin panel.
- Uji proses restore ke database kosong agar recovery saat insiden benar-benar siap.

5. Audit final pada server produksi / staging
- Verifikasi HTTPS, permission folder, proteksi `install.php`, konfigurasi database, dan error log.
- Jangan hanya mengandalkan hasil localhost.

## HIGH

1. Lengkapi modul reset session dan keamanan akun
- Tambahkan opsi logout dari semua session jika dibutuhkan.
- Tambahkan verifikasi password lama saat user mengganti password.

2. Tambahkan alert dan observability untuk error penting
- Catat kegagalan login berulang, upload gagal, transaksi gagal, dan kegagalan WA.
- Buat log lebih mudah dibaca admin/owner.

3. Perkuat validasi bisnis checkout
- Cegah order ganda untuk produk yang sama jika memang tidak diizinkan.
- Tentukan aturan jelas untuk pembelian ulang, membership, dan produk gratis.

4. Rapikan manajemen transaksi dan komisi affiliate
- Pastikan tidak ada komisi ganda.
- Pastikan self-referral dan edge case kupon affiliate ter-cover penuh.

5. Tambahkan pengujian UAT lokal yang baku
- Buat checklist test untuk admin, member, checkout, kupon, affiliate, dan LMS.
- Gunakan sebelum setiap release.

6. Rapikan hardening installer
- Jika aplikasi sudah live, nonaktifkan atau hapus `install.php`.
- Tambahkan guard tambahan bila file tetap disimpan di environment tertentu.

7. Validasi integrasi tracking
- Pastikan Google Analytics dan Facebook Pixel benar-benar aktif dan tidak double fire.

## MEDIUM

1. Lengkapi dashboard member sesuai kebutuhan platform membership
- Tambahkan ringkasan progress total, status kelas aktif, dan ringkasan affiliate di dashboard utama.

2. Lengkapi dashboard admin
- Tambahkan grafik, tren transaksi, dan ringkasan operasional yang lebih informatif.

3. Tambahkan UX feedback yang lebih baik di panel admin
- Error upload, validasi form, dan status aksi perlu lebih jelas dan konsisten.

4. Rapikan manajemen kategori produk
- Putuskan apakah `categories` benar-benar dipakai.
- Jika dipakai, tampilkan di admin, produk, dan filtering katalog.

5. Lengkapi validasi YouTube dan LMS
- Pastikan URL video valid.
- Tambahkan fallback yang lebih ramah jika embed gagal atau video belum tersedia.

6. Perjelas model data produk membership vs course
- Saat ini semua jenis produk masih memakai alur umum.
- Bedakan kebutuhan `membership premium`, `ebook`, `toolkit`, dan `ecourse` bila memang behavior-nya berbeda.

7. Tambahkan mekanisme housekeeping storage
- Bersihkan file upload yang orphaned.
- Atur retensi backup dan log agar storage tidak membengkak.

## LOW

1. Implementasikan integrasi email / Mailketing
- Untuk notifikasi registrasi, pembelian, aktivasi, dan reset password.

2. Implementasikan modul artikel / blog
- Untuk SEO dan akuisisi traffic organik.

3. Tambahkan fitur grafik dan insight affiliate
- Misalnya performa klik, conversion rate, dan top affiliate.

4. Rapikan dokumen teknis project
- Sinkronkan `AUDIT.md`, `README.txt`, dan struktur aktual project agar tidak membingungkan.

5. Buat panduan operasional admin
- SOP verifikasi transaksi, penanganan reset akun, backup, restore, dan pembayaran komisi.

6. Tambahkan seed/demo data lokal
- Memudahkan testing cepat di Laragon atau staging.

## Urutan Eksekusi Rekomendasi

1. Selesaikan semua item `CRITICAL`.
2. Kerjakan item `HIGH` yang menyentuh keamanan akun, transaksi, dan observability.
3. Rapikan pengalaman admin/member lewat item `MEDIUM`.
4. Kerjakan ekspansi produk dan marketing lewat item `LOW`.

## Definisi Siap Pakai

TravelCEO dianggap lebih siap dipakai sebagai platform membership dan LMS jika kondisi berikut sudah terpenuhi:

- alur registrasi sampai akses kelas berjalan mulus
- reset password aman sudah tersedia
- transaksi, kupon, affiliate, dan komisi stabil
- backup/restore teruji
- error penting bisa dipantau
- akses file sensitif tidak terbuka publik
- admin bisa mengelola produk, materi, transaksi, dan pengaturan tanpa hambatan operasional
