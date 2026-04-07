<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DefaultDataSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('users')->insert([
            'username'      => 'admin',
            'full_name'     => 'Administrator',
            'role'          => 'admin',
            'is_active'     => 1,
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->db->table('users')->insert([
            'username'      => 'editor',
            'full_name'     => 'Editor Konten',
            'role'          => 'editor',
            'is_active'     => 1,
            'password_hash' => password_hash('editor123', PASSWORD_DEFAULT),
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->db->table('home_settings')->insert([
            'hero_title'    => 'Satker PPS Kementerian PU',
            'hero_subtitle' => 'Mendorong pembangunan infrastruktur strategis yang terencana, inklusif, dan berkelanjutan untuk Indonesia.',
            'about_intro'   => 'Satker PPS Kementerian PU berperan merancang arah pembangunan prasarana untuk mendukung pertumbuhan wilayah, ketahanan nasional, dan pelayanan publik.',
            'official_name' => 'Satker PPS Kementerian PU',
            'logo_url' => '',
            'contact_email' => 'info@satkerpps.pu.go.id',
            'contact_phone' => '(0761) 000000',
            'contact_address' => 'Pekanbaru, Riau',
            'contact_map_url' => 'https://maps.google.com',
            'instagram_profile_url' => 'https://www.instagram.com/pu_prasaranastrategis_riau/',
            'instagram_post_urls' => '',
            'default_event_image' => '',
            'default_article_image' => '',
            'updated_at'    => date('Y-m-d H:i:s'),
            'updated_by'    => 1,
        ]);

        $this->db->table('home_slides')->insertBatch([
            [
                'title'      => 'Kolaborasi untuk Infrastruktur Negeri',
                'image_url'  => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=1400&q=80',
                'sort_order' => 1,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title'      => 'Perencanaan Tepat, Dampak Nyata',
                'image_url'  => 'https://images.unsplash.com/photo-1473773508845-188df298d2d1?auto=format&fit=crop&w=1400&q=80',
                'sort_order' => 2,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title'      => 'Prasarana Strategis untuk Masa Depan',
                'image_url'  => 'https://images.unsplash.com/photo-1473448912268-2022ce9509d8?auto=format&fit=crop&w=1400&q=80',
                'sort_order' => 3,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);

        $this->db->table('events')->insert([
            'title'        => 'Forum Konsultasi Infrastruktur Regional',
            'slug'         => 'forum-konsultasi-infrastruktur-regional',
            'summary'      => 'Diskusi lintas pemangku kepentingan untuk sinkronisasi prioritas proyek strategis tahun berjalan.',
            'content'      => 'Forum ini mempertemukan unsur pemerintah pusat, daerah, dan mitra strategis untuk memperkuat perencanaan infrastruktur yang terintegrasi.',
            'event_date'   => date('Y-m-d', strtotime('+20 days')),
            'location'     => 'Pekanbaru',
            'image_url'    => null,
            'is_published' => 1,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->db->table('articles')->insert([
            'title'        => 'Arah Pembangunan Prasarana Strategis 2026',
            'slug'         => 'arah-pembangunan-prasarana-strategis-2026',
            'summary'      => 'Ringkasan prioritas dan pendekatan perencanaan pembangunan prasarana strategis tahun 2026.',
            'content'      => 'Dokumen arah pembangunan 2026 menitikberatkan pada konektivitas antarkawasan, efisiensi biaya siklus hidup, dan kesiapsiagaan terhadap risiko iklim.',
            'category'     => 'Perencanaan',
            'image_url'    => null,
            'is_published' => 1,
            'published_at' => date('Y-m-d H:i:s'),
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }
}
