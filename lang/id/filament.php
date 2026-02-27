<?php

return [
    'navigation' => [
        'groups' => [
            'users' => 'Pengguna',
            'content' => 'Konten',
            'finance' => 'Keuangan',
        ],
        'roles' => 'Peran',
    ],

    'common' => [
        'yes' => 'Ya',
        'no' => 'Tidak',
        'anonymous' => 'Anonim',
    ],

    'options' => [
        'role' => [
            'admin' => 'Admin',
            'member' => 'Anggota',
        ],
        'donation_category' => [
            'zakat' => 'Zakat',
            'infak' => 'Infak',
            'sedekah' => 'Sedekah',
        ],
        'donation_payment_type' => [
            'maal' => 'Zakat Maal',
            'fitrah' => 'Zakat Fitrah',
            'profesi' => 'Zakat Profesi',
            'kemanusiaan' => 'Infak Kemanusiaan',
            'infak_umum' => 'Infak Umum',
            'jariyah' => 'Sedekah Jariyah',
            'sedekah_umum' => 'Sedekah Umum',
            'umum' => 'Umum',
        ],
        'donation_status' => [
            'pending' => 'Pending',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            'confirmed' => 'Dikonfirmasi',
        ],
        'member_prayer_status' => [
            'published' => 'Ditampilkan',
            'hidden' => 'Disembunyikan',
        ],
        'payment_method_type' => [
            'bank' => 'Transfer Bank',
            'qris' => 'QRIS',
            'ewallet' => 'E-Wallet',
        ],
    ],

    'resources' => [
        'articles' => [
            'model_label' => 'Berita',
            'plural_model_label' => 'Berita',
            'navigation_label' => 'Berita',
            'sections' => [
                'information' => 'Informasi Artikel',
                'content' => 'Isi Artikel',
            ],
            'descriptions' => [
                'information' => 'Metadata artikel untuk URL dan tampilan list konten.',
            ],
            'fields' => [
                'title' => 'Judul',
                'slug' => 'Slug',
                'thumbnail' => 'Thumbnail',
                'is_published' => 'Publikasikan Artikel',
                'content' => 'Konten',
                'created_at' => 'Tanggal Dibuat',
            ],
            'placeholders' => [
                'title' => 'Masukkan judul artikel',
                'slug' => 'slug-artikel',
            ],
            'filters' => [
                'publication' => 'Publikasi',
                'published' => 'Terbit',
                'draft' => 'Draft',
            ],
        ],

        'donations' => [
            'model_label' => 'Donasi',
            'plural_model_label' => 'Donasi',
            'navigation_label' => 'Donasi',
            'sections' => [
                'donor_data' => 'Data Donatur',
                'transaction_details' => 'Detail Transaksi',
                'program_context' => 'Konteks Program & Kalkulator',
                'admin_notes' => 'Catatan Admin',
            ],
            'descriptions' => [
                'donor_data' => 'Isi data donatur terdaftar atau tamu (guest).',
                'transaction_details' => 'Informasi pokok transaksi donasi dan verifikasinya.',
                'program_context' => 'Dipakai untuk donasi infak/sedekah kontekstual dan detail kalkulator zakat.',
            ],
            'fields' => [
                'member' => 'Anggota',
                'registered_user' => 'Pengguna Terdaftar',
                'guest_token' => 'Token Tamu',
                'donor_name' => 'Nama Donatur',
                'donor_phone' => 'No. Telepon Donatur',
                'donor_email' => 'Email Donatur',
                'payment_method' => 'Metode Pembayaran',
                'category' => 'Kategori',
                'payment_type' => 'Jenis Donasi',
                'amount' => 'Nominal',
                'proof_image' => 'Bukti Transfer',
                'status' => 'Status',
                'context_label' => 'Konteks Donasi',
                'context_slug' => 'Slug Konteks',
                'calculator_type' => 'Tipe Kalkulator',
                'intention_note' => 'Catatan Niat',
                'calculator_breakdown' => 'Breakdown Kalkulator (JSON)',
                'admin_note' => 'Catatan Internal',
                'created_at' => 'Tanggal Dibuat',
                'updated_at' => 'Tanggal Diperbarui',
            ],
            'placeholders' => [
                'guest_token' => 'Token donatur tamu',
                'payment_type' => 'Pilih jenis donasi',
                'member_guest' => 'Tamu',
            ],
            'helper_text' => [
                'calculator_breakdown' => 'Isi dengan format JSON valid. Contoh: {"nisab": 123, "wajib": true}',
            ],
            'filters' => [
                'category' => 'Kategori',
                'payment_type' => 'Jenis Donasi',
                'status' => 'Status',
                'payment_method' => 'Metode Pembayaran',
            ],
            'columns' => [
                'view_proof' => 'Lihat Bukti',
                'no_proof' => 'Belum Ada Bukti',
            ],
            'actions' => [
                'approve' => 'Setujui',
                'reject' => 'Tolak',
                'approve_success' => 'Donasi berhasil disetujui.',
                'reject_success' => 'Donasi berhasil ditolak.',
            ],
        ],

        'member_prayers' => [
            'model_label' => 'Doa Anggota',
            'plural_model_label' => 'Doa Anggota',
            'navigation_label' => 'Doa Anggota',
            'sections' => [
                'main' => 'Doa Anggota',
            ],
            'descriptions' => [
                'main' => 'Kelola konten doa serta visibilitasnya di aplikasi.',
            ],
            'fields' => [
                'user' => 'Pengguna',
                'visibility_status' => 'Status Tampil',
                'status' => 'Status',
                'is_anonymous' => 'Tampilkan sebagai anonim',
                'content' => 'Isi Doa',
                'created_at' => 'Tanggal Dibuat',
                'updated_at' => 'Tanggal Diperbarui',
            ],
            'filters' => [
                'status' => 'Status',
                'anonymous' => 'Anonim',
                'not_anonymous' => 'Tidak Anonim',
            ],
        ],

        'payment_methods' => [
            'model_label' => 'Metode Pembayaran',
            'plural_model_label' => 'Metode Pembayaran',
            'navigation_label' => 'Metode Pembayaran',
            'sections' => [
                'information' => 'Informasi Metode Pembayaran',
                'qris_configuration' => 'Konfigurasi QRIS',
            ],
            'descriptions' => [
                'information' => 'Data utama metode pembayaran yang tampil di aplikasi.',
                'qris_configuration' => 'Wajib diisi jika tipe metode adalah QRIS.',
            ],
            'fields' => [
                'name' => 'Nama Metode',
                'type' => 'Tipe Metode',
                'account_number' => 'Nomor Akun / Rekening / NMID',
                'account_holder' => 'Nama Pemilik Akun',
                'is_active' => 'Aktif',
                'logo' => 'Logo Metode',
                'qris_image' => 'Upload Gambar QRIS',
                'qris_static_payload' => 'QRIS Static Payload (EMV)',
                'qris_template' => 'Template QRIS',
                'qris_image_indicator' => 'Gambar QRIS',
                'created_at' => 'Tanggal Dibuat',
                'updated_at' => 'Tanggal Diperbarui',
            ],
            'placeholders' => [
                'name' => 'Contoh: Bank BSI / QRIS',
                'account_number' => 'Contoh: 1234567890',
                'account_holder' => 'Contoh: Baitul Maal LAZ',
            ],
            'helper_text' => [
                'qris_image' => 'Upload gambar QRIS, payload EMV akan diisi otomatis.',
                'qris_static_payload' => 'Bisa otomatis dari upload gambar QRIS, atau diisi manual.',
            ],
            'filters' => [
                'type' => 'Tipe',
                'active_status' => 'Status Aktif',
                'active' => 'Aktif',
                'inactive' => 'Nonaktif',
            ],
        ],

        'users' => [
            'model_label' => 'Anggota',
            'plural_model_label' => 'Anggota',
            'navigation_label' => 'Anggota',
            'sections' => [
                'account_information' => 'Informasi Akun',
                'profile' => 'Profil',
            ],
            'descriptions' => [
                'account_information' => 'Data utama akun pengguna untuk autentikasi dan otorisasi.',
                'profile' => 'Informasi tambahan yang ditampilkan di aplikasi.',
            ],
            'fields' => [
                'name' => 'Nama Lengkap',
                'email' => 'Email',
                'password' => 'Password',
                'role' => 'Peran',
                'phone' => 'Nomor Telepon',
                'avatar' => 'Foto Profil',
            ],
            'placeholders' => [
                'name' => 'Contoh: Ahmad Fauzi',
                'email' => 'contoh@email.com',
                'password' => 'Kosongkan jika tidak diubah',
                'phone' => '08xxxxxxxxxx',
            ],
            'filters' => [
                'role' => 'Peran',
            ],
        ],

        'videos' => [
            'model_label' => 'Video Kajian',
            'plural_model_label' => 'Video Kajian',
            'navigation_label' => 'Video Kajian',
            'sections' => [
                'youtube_source' => 'Sumber YouTube',
                'content_and_publication' => 'Konten & Publikasi',
            ],
            'descriptions' => [
                'youtube_source' => 'Tempel link video untuk mengisi metadata otomatis.',
            ],
            'fields' => [
                'youtube_link' => 'Link YouTube',
                'title' => 'Judul Video',
                'thumbnail_url' => 'Thumbnail URL',
                'description' => 'Deskripsi',
                'is_published' => 'Tampilkan di Aplikasi',
            ],
            'placeholders' => [
                'youtube_link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'title' => 'Judul konten video',
                'thumbnail_url' => 'https://...',
            ],
            'helper_text' => [
                'youtube_link' => 'Judul, deskripsi, dan thumbnail akan terisi otomatis saat field ini selesai diinput.',
                'thumbnail_url' => 'Bisa otomatis dari YouTube, atau isi manual.',
            ],
            'filters' => [
                'publication' => 'Publikasi',
                'published' => 'Terbit',
                'draft' => 'Draft',
            ],
        ],
    ],

    'widgets' => [
        'recent_donations' => [
            'heading' => 'Donasi Terbaru',
            'columns' => [
                'date' => 'Tanggal',
                'donor' => 'Donatur',
                'category' => 'Kategori',
                'amount' => 'Jumlah',
                'payment_method' => 'Metode Pembayaran',
                'status' => 'Status',
            ],
        ],
        'donation_stats' => [
            'total_donations' => 'Total Donasi',
            'total_donations_description' => 'Total donasi terkumpul',
            'today_donations' => 'Donasi Hari Ini',
            'today_donations_description' => 'Donasi masuk hari ini',
            'pending_confirmation' => 'Menunggu Konfirmasi',
            'pending_confirmation_description' => 'Donasi pending',
            'confirmed' => 'Terkonfirmasi',
            'confirmed_description' => 'Donasi terverifikasi',
        ],
        'content_stats' => [
            'total_members' => 'Total Member',
            'registered_members' => 'Member terdaftar',
            'articles' => 'Artikel',
            'videos' => 'Video',
            'member_prayers' => 'Doa Member',
            'published_count' => ':count dipublikasi',
        ],
        'zakat_stats' => [
            'fitrah' => 'Zakat Fitrah',
            'fitrah_description' => 'Total zakat fitrah',
            'maal' => 'Zakat Maal',
            'maal_description' => 'Total zakat maal',
            'profession' => 'Zakat Profesi',
            'profession_description' => 'Total zakat profesi',
        ],
        'donation_category_chart' => [
            'heading' => 'Distribusi Donasi per Kategori',
            'total_label' => 'Total (Rp)',
        ],
        'donation_chart' => [
            'heading' => 'Tren Donasi (7 Hari Terakhir)',
            'total_label' => 'Total Donasi (Rp)',
        ],
    ],

    'notifications' => [
        'invalid_qris' => 'QRIS tidak valid',
        'invalid_youtube_link' => 'Link YouTube tidak valid.',
    ],

    'exports' => [
        'articles' => [
            'columns' => [
                'id' => 'ID',
                'title' => 'Judul',
                'slug' => 'Slug',
                'is_published' => 'Terbit',
                'published_at' => 'Tanggal Terbit',
                'created_at' => 'Tanggal Dibuat',
                'content' => 'Konten',
            ],
        ],
        'donations' => [
            'columns' => [
                'id' => 'ID Donasi',
                'member' => 'Member',
                'donor_name' => 'Nama Donatur',
                'donor_phone' => 'No. HP Donatur',
                'donor_email' => 'Email Donatur',
                'category' => 'Kategori',
                'payment_type' => 'Jenis Donasi',
                'program' => 'Program',
                'payment_method' => 'Metode Pembayaran',
                'amount' => 'Jumlah',
                'status' => 'Status',
                'admin_note' => 'Catatan Admin',
                'created_at' => 'Tanggal Donasi',
            ],
        ],
        'member_prayers' => [
            'columns' => [
                'id' => 'ID',
                'member' => 'Member',
                'content' => 'Isi Doa',
                'is_anonymous' => 'Anonim',
                'likes_count' => 'Jumlah Amin',
                'status' => 'Status',
                'created_at' => 'Tanggal Dibuat',
            ],
        ],
        'payment_methods' => [
            'columns' => [
                'id' => 'ID',
                'name' => 'Nama',
                'type' => 'Jenis',
                'account_number' => 'No. Rekening',
                'account_holder' => 'Atas Nama',
                'is_active' => 'Aktif',
                'qris_static_payload' => 'QRIS Payload',
                'qris_image' => 'Gambar QRIS',
                'created_at' => 'Tanggal Dibuat',
            ],
        ],
        'users' => [
            'columns' => [
                'id' => 'ID',
                'name' => 'Nama',
                'email' => 'Email',
                'role' => 'Peran',
                'phone' => 'No. HP',
                'email_verified_at' => 'Email Terverifikasi',
                'created_at' => 'Tanggal Registrasi',
            ],
        ],
        'videos' => [
            'columns' => [
                'id' => 'ID',
                'title' => 'Judul',
                'youtube_id' => 'YouTube ID',
                'description' => 'Deskripsi',
                'is_published' => 'Terbit',
                'created_at' => 'Tanggal Dibuat',
            ],
        ],
    ],
];
