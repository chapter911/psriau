<?php

/**
 * Custom Helper Functions untuk Export dan Utility
 */

/**
 * Mengubah angka menjadi terbilang mata uang Rupiah
 */
function terbilang_rupiah($number)
{
    return terbilang_angka((int) $number) . ' Rupiah';
}

/**
 * Mengubah angka menjadi terbilang dalam bahasa Indonesia
 */
function terbilang_angka($number)
{
    $number = abs((int) $number);
    $words = array(
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
        1000000000000 => 'satu triliun'
    );

    if ($number == 0) {
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
        if ($hundreds == 1) {
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
        if ($thousands == 1) {
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
        if ($millions == 1) {
            $word = 'satu juta';
        } else {
            $word = terbilang_angka($millions) . ' juta';
        }
        if ($remainder) {
            $word .= $conjunction . terbilang_angka($remainder);
        }
    } elseif ($number >= 1000000000 && $number < 1000000000000) {
        $billions = (int) ($number / 1000000000);
        $remainder = $number % 1000000000;
        if ($billions == 1) {
            $word = 'satu miliar';
        } else {
            $word = terbilang_angka($billions) . ' miliar';
        }
        if ($remainder) {
            $word .= $conjunction . terbilang_angka($remainder);
        }
    } else if ($number >= 1000000000000) {
        $trillions = (int) ($number / 1000000000000);
        $remainder = $number % 1000000000000;
        if ($trillions == 1) {
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

/**
 * Mengubah tanggal ke format Indonesia
 */
function tanggal_indonesia($date)
{
    if (empty($date)) {
        return '';
    }

    $bulan = array(
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
        12 => 'Desember'
    );

    $split = explode('-', $date);
    if (count($split) === 3) {
        return $split[2] . ' ' . $bulan[(int) $split[1]] . ' ' . $split[0];
    }

    return $date;
}

/**
 * Format mata uang Rupiah
 */
function rupiah($nominal)
{
    return 'Rp. ' . number_format($nominal, 0, ',', '.');
}

/**
 * Format tanggal lengkap Indonesia dengan hari
 */
function tanggal_lengkap_indonesia($date)
{
    if (empty($date)) {
        return '';
    }

    $hari = array(
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    );

    $bulan = array(
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
        12 => 'Desember'
    );

    $timestamp = strtotime($date);
    $day_name = $hari[date('l', $timestamp)];
    $split = explode('-', date('Y-m-d', $timestamp));

    return $day_name . ', ' . $split[2] . ' ' . $bulan[(int) $split[1]] . ' ' . $split[0];
}

/**
 * Sanitize string untuk digunakan sebagai nama folder/file
 *
 * - Menghapus karakter yang tidak diizinkan: / \ : * ? " < > |
 * - Mengganti multiple spasi dengan single spasi
 * - Trim whitespace
 * - Membatasi panjang maksimal
 *
 * @param string $name Nama yang akan disanitasi
 * @param int $maxLength Maksimal panjang nama (default 100)
 * @return string Nama yang sudah disanitasi
 */
function sanitizeFileName(string $name, int $maxLength = 100): string
{
    if (empty($name)) {
        return 'unnamed';
    }

    // Hapus karakter yang tidak diizinkan di nama file/folder
    $name = preg_replace('/[\/\\\\:*?"<>|]/', '', $name);

    // Ganti multiple spasi dengan single spasi
    $name = preg_replace('/\s+/', ' ', $name);

    // Trim whitespace
    $name = trim($name);

    // Ganti karakter titik di awal atau akhir (mencegah masalah)
    $name = preg_replace('/^\.+|\.+$/', '', $name);

    // Jika kosong setelah sanitasi, gunakan placeholder
    if (empty($name)) {
        return 'unnamed';
    }

    // Batasi panjang
    if (mb_strlen($name) > $maxLength) {
        $name = mb_substr($name, 0, $maxLength);
        // Trim lagi karena mungkin pemotongan memotong di tengah kata
        $name = trim($name);
    }

    return $name;
}

/**
 * Sanitize path components untuk folder hierarchy
 * Sama seperti sanitizeFileName tapi dengan batas yang lebih panjang
 *
 * @param string $name Nama yang akan disanitasi
 * @return string Nama yang sudah disanitasi
 */
function sanitizeFolderName(string $name): string
{
    return sanitizeFileName($name, 100);
}
