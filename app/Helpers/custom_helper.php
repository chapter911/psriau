<?php

/**
 * Custom Helper Functions untuk Export dan Utility.
 */

if (! function_exists('terbilang_rupiah')) {
    /**
     * Mengubah angka menjadi terbilang mata uang Rupiah.
     */
    function terbilang_rupiah($number)
    {
        return terbilang_angka((int) $number) . ' Rupiah';
    }
}

if (! function_exists('terbilang_angka')) {
    /**
     * Mengubah angka menjadi terbilang dalam bahasa Indonesia.
     */
    function terbilang_angka($number)
    {
        $number = abs((int) $number);
        $words = [
            0 => '',
            1 => 'satu',
            2 => 'dua',
            3 => 'tiga',
            4 => 'empat',
            5 => 'lima',
            6 => 'enam',
            7 => 'tujuh',
            8 => 'delapan',
            9 => 'sembilan',
            10 => 'sepuluh',
            11 => 'sebelas',
            12 => 'dua belas',
            13 => 'tiga belas',
            14 => 'empat belas',
            15 => 'lima belas',
            16 => 'enam belas',
            17 => 'tujuh belas',
            18 => 'delapan belas',
            19 => 'sembilan belas',
            20 => 'dua puluh',
            30 => 'tiga puluh',
            40 => 'empat puluh',
            50 => 'lima puluh',
            60 => 'enam puluh',
            70 => 'tujuh puluh',
            80 => 'delapan puluh',
            90 => 'sembilan puluh',
            100 => 'seratus',
            1000 => 'seribu',
            1000000 => 'satu juta',
            1000000000 => 'satu miliar',
            1000000000000 => 'satu triliun',
        ];

        if ($number === 0) {
            return 'nol';
        }

        $hyphen = ' ';
        $conjunction = ' ';
        if ($number < 21) {
            $word = $words[$number];
        } elseif ($number < 100) {
            $tens = ((int) ($number / 10)) * 10;
            $units = $number % 10;
            $word = $words[$tens];
            if ($units) {
                $word .= $hyphen . $words[$units];
            }
        } elseif ($number < 1000) {
            $hundreds = (int) ($number / 100);
            $remainder = $number % 100;
            if ($hundreds === 1) {
                $word = 'seratus';
            } else {
                $word = $words[$hundreds] . ' ratus';
            }
            if ($remainder) {
                $word .= $conjunction . terbilang_angka($remainder);
            }
        } elseif ($number < 1000000) {
            $thousands = (int) ($number / 1000);
            $remainder = $number % 1000;
            if ($thousands === 1) {
                $word = 'seribu';
            } else {
                $word = terbilang_angka($thousands) . ' ribu';
            }
            if ($remainder) {
                $word .= $conjunction . terbilang_angka($remainder);
            }
        } elseif ($number < 1000000000) {
            $millions = (int) ($number / 1000000);
            $remainder = $number % 1000000;
            if ($millions === 1) {
                $word = 'satu juta';
            } else {
                $word = terbilang_angka($millions) . ' juta';
            }
            if ($remainder) {
                $word .= $conjunction . terbilang_angka($remainder);
            }
        } elseif ($number < 1000000000000) {
            $billions = (int) ($number / 1000000000);
            $remainder = $number % 1000000000;
            if ($billions === 1) {
                $word = 'satu miliar';
            } else {
                $word = terbilang_angka($billions) . ' miliar';
            }
            if ($remainder) {
                $word .= $conjunction . terbilang_angka($remainder);
            }
        } else {
            $trillions = (int) ($number / 1000000000000);
            $remainder = $number % 1000000000000;
            if ($trillions === 1) {
                $word = 'satu triliun';
            } else {
                $word = terbilang_angka($trillions) . ' triliun';
            }
            if ($remainder) {
                $word .= $conjunction . terbilang_angka($remainder);
            }
        }

        return $word;
    }
}

if (! function_exists('tanggal_indonesia')) {
    /**
     * Mengubah tanggal ke format Indonesia.
     */
    function tanggal_indonesia($date)
    {
        if (empty($date)) {
            return '';
        }

        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $split = explode('-', (string) $date);
        if (count($split) === 3) {
            return $split[2] . ' ' . $bulan[(int) $split[1]] . ' ' . $split[0];
        }

        return (string) $date;
    }
}

if (! function_exists('rupiah')) {
    /**
     * Format mata uang Rupiah.
     */
    function rupiah($nominal)
    {
        return 'Rp. ' . number_format((float) $nominal, 0, ',', '.');
    }
}

if (! function_exists('tanggal_lengkap_indonesia')) {
    /**
     * Format tanggal lengkap Indonesia dengan hari.
     */
    function tanggal_lengkap_indonesia($date)
    {
        if (empty($date)) {
            return '';
        }

        $hari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $timestamp = strtotime((string) $date);
        if ($timestamp === false) {
            return (string) $date;
        }

        $dayName = $hari[date('l', $timestamp)] ?? '';
        $split = explode('-', date('Y-m-d', $timestamp));

        return $dayName . ', ' . $split[2] . ' ' . $bulan[(int) $split[1]] . ' ' . $split[0];
    }
}

if (! function_exists('kop_surat_url')) {
    /**
     * Ambil file kop surat aktif.
     */
    function kop_surat_url(?int $kopSuratId = null): string
    {
        try {
            $db = db_connect();
            if (! $db->tableExists('kop_surat')) {
                return '';
            }

            $row = null;

            if ($kopSuratId !== null && $kopSuratId > 0) {
                $row = $db->table('kop_surat')
                    ->where('id', $kopSuratId)
                    ->where('image_url IS NOT NULL')
                    ->where("TRIM(image_url) != ''")
                    ->limit(1)
                    ->get()
                    ->getRowArray();
            }

            if (! is_array($row)) {
                $row = $db->table('kop_surat')
                    ->where('is_active', 1)
                    ->where('image_url IS NOT NULL')
                    ->where("TRIM(image_url) != ''")
                    ->orderBy('id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
            }

            if (! is_array($row)) {
                $row = $db->table('kop_surat')
                    ->where('image_url IS NOT NULL')
                    ->where("TRIM(image_url) != ''")
                    ->orderBy('id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
            }

            if (is_array($row) && ! empty($row['image_url'])) {
                return (string) $row['image_url'];
            }
        } catch (\Throwable $e) {
            return '';
        }

        return '';
    }
}

if (! function_exists('kop_surat_img_tag')) {
    /**
     * Render tag img kop surat aktif.
     */
    function kop_surat_img_tag(string $class = '', string $style = '', string $alt = 'Kop Surat', ?int $kopSuratId = null): string
    {
        $url = kop_surat_url($kopSuratId);
        if ($url === '') {
            return '';
        }

        $localPath = FCPATH . ltrim($url, '/');
        $src = '';
        if (is_file($localPath)) {
            $mime = 'image/png';
            $detectedMime = @mime_content_type($localPath);
            if (is_string($detectedMime) && $detectedMime !== '') {
                $mime = $detectedMime;
            }

            $binary = @file_get_contents($localPath);
            if ($binary !== false) {
                // Hindari request ke localhost saat render Dompdf.
                $src = 'data:' . $mime . ';base64,' . base64_encode($binary);
            }
        }

        if ($src === '') {
            // Fallback terakhir bila file lokal tidak ditemukan.
            $src = base_url($url);
        }

        $attributes = [
            'src' => $src,
            'alt' => $alt,
        ];

        if ($class !== '') {
            $attributes['class'] = $class;
        }

        $html = '<img';
        foreach ($attributes as $name => $value) {
            $html .= ' ' . $name . '="' . esc($value, 'attr') . '"';
        }

        if ($style !== '') {
            $html .= ' style="' . esc($style, 'attr') . '"';
        }

        $html .= '>';

        return $html;
    }
}

if (! function_exists('normalize_syarat_umum_html')) {
    /**
     * Normalisasi HTML syarat umum agar paragraf menjadi rapat dan aman untuk export.
     */
    function normalize_syarat_umum_html($html): string
    {
        $text = is_string($html) ? $html : '';

        if ($text === '') {
            return '';
        }

        $text = preg_replace('#<(script|style)[^>]*>.*?</\1>#is', '', $text) ?? $text;
        $text = str_replace('&nbsp;', ' ', $text);
        $text = preg_replace('#<p\b[^>]*>#i', '', $text) ?? $text;
        $text = preg_replace('#</p>#i', '<br>', $text) ?? $text;
        $text = preg_replace('#(<br\s*/?>\s*){2,}#i', '<br>', $text) ?? $text;
        $text = preg_replace('#^(?:<br\s*/?>\s*)+#i', '', $text) ?? $text;
        $text = preg_replace('#(?:<br\s*/?>\s*)+$#i', '', $text) ?? $text;

        return trim($text);
    }
}

if (! function_exists('bulan_tahun_indonesia')) {
    /**
     * Format tanggal menjadi "Bulan Tahun" dalam bahasa Indonesia.
     */
    function bulan_tahun_indonesia($date)
    {
        if (empty($date)) {
            return '';
        }

        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $timestamp = strtotime((string) $date);
        if ($timestamp === false) {
            return (string) $date;
        }

        return $bulan[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    }
}
