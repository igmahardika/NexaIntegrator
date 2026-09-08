<?php

namespace App\Services;

class TemplateRegistryService
{
    /**
     * Get all registered default portal templates (Login Method Templates).
     * Nexa Hotspot 2-column modal is the unified default aesthetic.
     */
    public static function all(): array
    {
        return [
            'access-code' => [
                'id'            => 'access-code',
                'name'          => 'Access Code',
                'method'        => 'access-code',
                'category'      => 'Voucher & Tiket WiFi',
                'description'   => 'Metode login menggunakan kode akses atau voucher unik berdurasi (contoh: 1 Jam, 1 Hari). Praktis untuk tamu kafe, hotel, atau event.',
                'preview_badge' => 'Voucher / Tiket',
                'accent'        => '#00a6f4',
                'icon'          => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z',
                'default_config' => [
                    'brand_name'      => 'nexa Hotspot',
                    'brand_tagline'   => 'High-Speed Guest Connectivity',
                    'instagram'       => 'nexanet.id',
                    'logo_url'        => '/images/nexa/logo-hotspot-color.png',
                    'logo_white'      => '/images/nexa/logo-hotspot-white.png',
                    'logo_nexa'       => '/images/nexa/logo-nexa-color.png',
                    'topbar_color'    => '#00b4d8',
                    'topbar_title'    => 'Access Code',
                    'hero_title'      => 'Input Access Code',
                    'hero_subtitle'   => 'Masukkan kode voucher atau tiket akses internet Anda',
                    'input_placeholder' => 'Input Access Code',
                    'button_text'     => 'Validasi Kode Akses',
                    'primary_color'   => '#00a6f4',
                    'accent_color'    => '#00b4d8',
                    'bg_type'         => 'image',
                    'bg_value'        => '/images/nexa/bg-cafe.jpg',
                    'promo_image'     => '/images/nexa/promo-slide-1.jpg',
                    'promo_image_2'   => '/images/nexa/promo-slide-2.jpg',
                    'card_bg'         => '#ffffff',
                    'promo_enabled'   => true,
                    'promo_badge'     => 'Internet Nexa',
                    'promo_title'     => 'Koneksi Cepat & Handal',
                    'promo_text'      => 'Didukung jaringan fiber optik berkecepatan tinggi hingga 1 Gbps untuk produktivitas Anda.',
                    'promo_link'      => '',
                    'tos_text'        => 'Dengan memasukkan kode, Anda menyetujui syarat & ketentuan jaringan ini.',
                    'custom_css'      => '',
                ],
            ],

            'username-password' => [
                'id'            => 'username-password',
                'name'          => 'Username & Password',
                'method'        => 'username-password',
                'category'      => 'Member & Staff Authentication',
                'description'   => 'Metode login berbasis akun terdaftar (Username & Password). Pengguna harus memiliki akun aktif untuk terhubung ke internet.',
                'preview_badge' => 'Akun / Member',
                'accent'        => '#00a6f4',
                'icon'          => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                'default_config' => [
                    'brand_name'      => 'nexa Hotspot',
                    'brand_tagline'   => 'Member & Staff Secure Access',
                    'instagram'       => 'nexanet.id',
                    'logo_url'        => '/images/nexa/logo-hotspot-color.png',
                    'logo_white'      => '/images/nexa/logo-hotspot-white.png',
                    'logo_nexa'       => '/images/nexa/logo-nexa-color.png',
                    'topbar_color'    => '#00b4d8',
                    'topbar_title'    => 'Member Login',
                    'hero_title'      => 'Login Akun Member / Staff',
                    'hero_subtitle'   => 'Masukkan username dan password Anda untuk memulai sesi internet',
                    'input_placeholder' => 'Username',
                    'button_text'     => 'Masuk & Hubungkan',
                    'primary_color'   => '#00a6f4',
                    'accent_color'    => '#00b4d8',
                    'bg_type'         => 'image',
                    'bg_value'        => '/images/nexa/bg-cafe.jpg',
                    'promo_image'     => '/images/nexa/promo-slide-1.jpg',
                    'promo_image_2'   => '/images/nexa/promo-slide-2.jpg',
                    'card_bg'         => '#ffffff',
                    'promo_enabled'   => true,
                    'promo_badge'     => 'Akses Member',
                    'promo_title'     => 'Jaringan Khusus Member',
                    'promo_text'      => 'Belum memiliki akun? Silakan hubungi staf atau bagian IT administrator.',
                    'promo_link'      => '',
                    'tos_text'        => 'Dengan masuk, Anda menyetujui kebijakan penggunaan jaringan internet perusahaan/organisasi.',
                    'custom_css'      => '',
                ],
            ],

            'whatsapp-login' => [
                'id'            => 'whatsapp-login',
                'name'          => 'WhatsApp Login',
                'method'        => 'whatsapp-login',
                'category'      => 'Social & Mobile Messaging',
                'description'   => 'Metode login dengan memasukkan Nomor WhatsApp aktif. Efektif untuk mengumpulkan database kontak pelanggan (leads/CRM) untuk promosi bisnis.',
                'preview_badge' => 'WA Verified',
                'accent'        => '#00a6f4',
                'icon'          => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                'default_config' => [
                    'brand_name'      => 'nexa Hotspot',
                    'brand_tagline'   => 'Instant WhatsApp Internet',
                    'instagram'       => 'nexanet.id',
                    'logo_url'        => '/images/nexa/logo-hotspot-color.png',
                    'logo_white'      => '/images/nexa/logo-hotspot-white.png',
                    'logo_nexa'       => '/images/nexa/logo-nexa-color.png',
                    'topbar_color'    => '#00b4d8',
                    'topbar_title'    => 'WhatsApp Login',
                    'hero_title'      => 'Login dengan WhatsApp',
                    'hero_subtitle'   => 'Masukkan nomor WhatsApp aktif Anda untuk langsung terhubung',
                    'input_placeholder' => 'Nomor WhatsApp',
                    'button_text'     => 'Kirim & Hubungkan Sekarang',
                    'primary_color'   => '#00a6f4',
                    'accent_color'    => '#00b4d8',
                    'bg_type'         => 'image',
                    'bg_value'        => '/images/nexa/bg-cafe.jpg',
                    'promo_image'     => '/images/nexa/promo-slide-1.jpg',
                    'promo_image_2'   => '/images/nexa/promo-slide-2.jpg',
                    'card_bg'         => '#ffffff',
                    'promo_enabled'   => true,
                    'promo_badge'     => 'Promo WhatsApp',
                    'promo_title'     => 'Dapatkan Update Promo Nexa',
                    'promo_text'      => 'Nomor Anda aman. Kami hanya mengirimkan informasi penawaran spesial terpilih.',
                    'promo_link'      => '',
                    'tos_text'        => 'Dengan memasukkan nomor WhatsApp, Anda setuju untuk menerima notifikasi koneksi.',
                    'custom_css'      => '',
                ],
            ],

            'question' => [
                'id'            => 'question',
                'name'          => 'Question',
                'method'        => 'question',
                'category'      => 'Survei & Kuesioner',
                'description'   => 'Metode login dengan menjawab pertanyaan riset singkat atau kuesioner sponsor. Tamu mendapatkan internet gratis setelah menjawab survei.',
                'preview_badge' => 'Survei / Riset',
                'accent'        => '#00a6f4',
                'icon'          => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'default_config' => [
                    'brand_name'      => 'nexa Hotspot',
                    'brand_tagline'   => 'Free Sponsored WiFi',
                    'instagram'       => 'nexanet.id',
                    'logo_url'        => '/images/nexa/logo-hotspot-color.png',
                    'logo_white'      => '/images/nexa/logo-hotspot-white.png',
                    'logo_nexa'       => '/images/nexa/logo-nexa-color.png',
                    'topbar_color'    => '#00b4d8',
                    'topbar_title'    => 'Question & Survey',
                    'hero_title'      => 'Jawab Pertanyaan & Dapatkan WiFi',
                    'hero_subtitle'   => 'Bantu kami meningkatkan pelayanan dengan menjawab pertanyaan singkat berikut',
                    'input_placeholder' => 'Jawaban',
                    'button_text'     => 'Kirim Jawaban & Aktifkan Internet',
                    'primary_color'   => '#00a6f4',
                    'accent_color'    => '#00b4d8',
                    'bg_type'         => 'image',
                    'bg_value'        => '/images/nexa/bg-cafe.jpg',
                    'promo_image'     => '/images/nexa/promo-slide-1.jpg',
                    'promo_image_2'   => '/images/nexa/promo-slide-2.jpg',
                    'card_bg'         => '#ffffff',
                    'promo_enabled'   => true,
                    'promo_badge'     => 'Sponsor WiFi',
                    'promo_title'     => 'Internet Gratis Bersponsor',
                    'promo_text'      => 'Lengkapi pertanyaan di bawah untuk membuka kuota internet berkecepatan tinggi.',
                    'promo_link'      => '',
                    'tos_text'        => 'Jawaban survei dijaga kerahasiaannya dan digunakan untuk keperluan riset kepuasan.',
                    'custom_css'      => '',
                ],
            ],

            'button' => [
                'id'            => 'button',
                'name'          => 'Button',
                'method'        => 'button',
                'category'      => '1-Click Free Access',
                'description'   => 'Metode login tercepat dan termudah. Cukup klik satu tombol persetujuan, pengguna langsung terhubung ke jaringan internet tanpa input formulir.',
                'preview_badge' => '1-Click Login',
                'accent'        => '#00a6f4',
                'icon'          => 'M13 10V3L4 14h7v7l9-11h-7z',
                'default_config' => [
                    'brand_name'      => 'nexa Hotspot',
                    'brand_tagline'   => 'Fast & Easy One-Click Connection',
                    'instagram'       => 'nexanet.id',
                    'logo_url'        => '/images/nexa/logo-hotspot-color.png',
                    'logo_white'      => '/images/nexa/logo-hotspot-white.png',
                    'logo_nexa'       => '/images/nexa/logo-nexa-color.png',
                    'topbar_color'    => '#00b4d8',
                    'topbar_title'    => 'One-Click Access',
                    'hero_title'      => 'Akses WiFi Gratis (1-Click)',
                    'hero_subtitle'   => 'Klik tombol di bawah ini untuk langsung terhubung ke internet tanpa ribet',
                    'input_placeholder' => '',
                    'button_text'     => 'Hubungkan Internet Sekarang',
                    'primary_color'   => '#00a6f4',
                    'accent_color'    => '#00b4d8',
                    'bg_type'         => 'image',
                    'bg_value'        => '/images/nexa/bg-cafe.jpg',
                    'promo_image'     => '/images/nexa/promo-slide-1.jpg',
                    'promo_image_2'   => '/images/nexa/promo-slide-2.jpg',
                    'card_bg'         => '#ffffff',
                    'promo_enabled'   => true,
                    'promo_badge'     => 'Gratis',
                    'promo_title'     => 'Nikmati Akses High-Speed',
                    'promo_text'      => 'Koneksi aktif berkecepatan tinggi. Siap menemani kebutuhan streaming dan browsing Anda.',
                    'promo_link'      => '',
                    'tos_text'        => 'Dengan menekan tombol, Anda menyetujui Ketentuan Penggunaan & Kebijakan Privasi Jaringan.',
                    'custom_css'      => '',
                ],
            ],

            'email' => [
                'id'            => 'email',
                'name'          => 'Email',
                'method'        => 'email',
                'category'      => 'Lead Generation & Newsletter',
                'description'   => 'Metode login dengan memasukkan Nama Lengkap dan Alamat Email. Sangat ideal untuk kampanye email newsletter, promosi brand, dan membership baru.',
                'preview_badge' => 'Email List',
                'accent'        => '#00a6f4',
                'icon'          => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                'default_config' => [
                    'brand_name'      => 'nexa Hotspot',
                    'brand_tagline'   => 'Stay Connected & Subscribe',
                    'instagram'       => 'nexanet.id',
                    'logo_url'        => '/images/nexa/logo-hotspot-color.png',
                    'logo_white'      => '/images/nexa/logo-hotspot-white.png',
                    'logo_nexa'       => '/images/nexa/logo-nexa-color.png',
                    'topbar_color'    => '#00b4d8',
                    'topbar_title'    => 'Email Login',
                    'hero_title'      => 'Masuk dengan Email',
                    'hero_subtitle'   => 'Masukkan email Anda untuk menerima akses internet dan update penawaran menarik',
                    'input_placeholder' => 'nama@email.com',
                    'button_text'     => 'Lanjutkan & Dapatkan Akses',
                    'primary_color'   => '#00a6f4',
                    'accent_color'    => '#00b4d8',
                    'bg_type'         => 'image',
                    'bg_value'        => '/images/nexa/bg-cafe.jpg',
                    'promo_image'     => '/images/nexa/promo-slide-1.jpg',
                    'promo_image_2'   => '/images/nexa/promo-slide-2.jpg',
                    'card_bg'         => '#ffffff',
                    'promo_enabled'   => true,
                    'promo_badge'     => 'Newsletter',
                    'promo_title'     => 'Info & Promo Eksklusif Nexa',
                    'promo_text'      => 'Dapatkan info voucher diskon mingguan dan penawaran spesial langsung ke email Anda.',
                    'promo_link'      => '',
                    'tos_text'        => 'Kami menghormati privasi Anda. Alamat email Anda tidak akan pernah disalahgunakan.',
                    'custom_css'      => '',
                ],
            ],
        ];
    }

    /**
     * Get single template definition.
     */
    public static function get(?string $templateId): array
    {
        $all = static::all();
        return $all[$templateId] ?? $all['access-code'] ?? reset($all);
    }

    /**
     * Resolve final merged configuration for a location/site.
     */
    public static function resolveConfig(?string $templateId, ?array $customConfig): array
    {
        $template = static::get($templateId);
        $defaults = $template['default_config'];

        if (empty($customConfig)) {
            return $defaults;
        }

        // Support isolated per-template config if saved nested under template key
        if ($templateId && isset($customConfig[$templateId]) && is_array($customConfig[$templateId])) {
            return array_replace_recursive($defaults, $customConfig[$templateId]);
        }

        // Deep merge custom configuration
        return array_replace_recursive($defaults, $customConfig);
    }
}
