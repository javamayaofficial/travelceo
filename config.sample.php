<?php
/**
 * config.php
 * File ini dibuat/diisi otomatis oleh Installer Wizard (install.php).
 * Anda juga bisa mengisinya manual lewat File Manager cPanel bila perlu.
 */

return [
    'db_host' => 'localhost',
    'db_name' => 'NAMA_DATABASE',
    'db_user' => 'USER_DATABASE',
    'db_pass' => 'PASSWORD_DATABASE',

    // URL dasar aplikasi (boleh dikosongkan, akan dideteksi otomatis)
    'base_url' => '',

    // Kunci keamanan (jangan dibagikan)
    'app_key'  => 'GANTI_DENGAN_KUNCI_ACAK',

    // Token API Fonnte untuk notifikasi WhatsApp dan OTP login
    'fonnte_token' => '',

    // Token API Mailketing dan email pengirim default
    'mailketing_token' => '',
    'mail_sender' => 'admin@domainanda.com',

    'installed' => false,
];
