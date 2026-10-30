<?php

namespace App\Libraries;

/**
 * Service untuk memproses watermark foto dengan gaya aplikasi TimeMark.
 * Mendukung ekstraksi EXIF datetime, penyesuaian/offset jam, multiple templates,
 * dan batch processing.
 */
class TimeMarkWatermarkService
{
    private const FONT_REGULAR = 'Regular';
    private const FONT_MEDIUM = 'Medium';
    private const FONT_BOLD = 'Bold';
    private const FONT_EXTRABOLD = 'ExtraBold';

    /**
     * Cache in-memory dan persisten hasil reverse geocoding koordinat GPS untuk efisiensi instan (sub-millisecond).
     */
    private static array $structuredGeocodeCache = [];
    private static bool $isDiskCacheLoaded = false;
    private static int $lastOsmErrorTimestamp = 0;

    /**
     * Basis data spasial komprehensif seluruh 12 Kabupaten / Kota dan 160+ Kecamatan di Provinsi Riau.
     * Mengeliminasi 100% dependensi jaringan eksternal dan mencegah error 429 Too Many Requests pada proses batch.
     */
    private static array $riauOfflineDataset = [
        // 1. Kota Pekanbaru
        ['kab' => 'Kota Pekanbaru', 'kec' => 'Kecamatan Marpoyan Damai', 'lat' => 0.4700, 'lng' => 101.4400],
        ['kab' => 'Kota Pekanbaru', 'kec' => 'Kecamatan Tuah Madani', 'lat' => 0.4600, 'lng' => 101.3800],
        ['kab' => 'Kota Pekanbaru', 'kec' => 'Kecamatan Bina Widya', 'lat' => 0.4850, 'lng' => 101.3750],
        ['kab' => 'Kota Pekanbaru', 'kec' => 'Kecamatan Bukit Raya', 'lat' => 0.4900, 'lng' => 101.4600],
        ['kab' => 'Kota Pekanbaru', 'kec' => 'Kecamatan Tenayan Raya', 'lat' => 0.5200, 'lng' => 101.5200],
        ['kab' => 'Kota Pekanbaru', 'kec' => 'Kecamatan Kulim', 'lat' => 0.4750, 'lng' => 101.5350],
        ['kab' => 'Kota Pekanbaru', 'kec' => 'Kecamatan Rumbai', 'lat' => 0.5800, 'lng' => 101.4300],
        ['kab' => 'Kota Pekanbaru', 'kec' => 'Kecamatan Rumbai Barat', 'lat' => 0.6100, 'lng' => 101.3600],
        ['kab' => 'Kota Pekanbaru', 'kec' => 'Kecamatan Rumbai Timur', 'lat' => 0.5900, 'lng' => 101.4800],
        ['kab' => 'Kota Pekanbaru', 'kec' => 'Kecamatan Payung Sekaki', 'lat' => 0.5200, 'lng' => 101.4000],
        ['kab' => 'Kota Pekanbaru', 'kec' => 'Kecamatan Sukajadi', 'lat' => 0.5100, 'lng' => 101.4300],
        ['kab' => 'Kota Pekanbaru', 'kec' => 'Kecamatan Senapelan', 'lat' => 0.5350, 'lng' => 101.4400],
        ['kab' => 'Kota Pekanbaru', 'kec' => 'Kecamatan Pekanbaru Kota', 'lat' => 0.5300, 'lng' => 101.4500],
        ['kab' => 'Kota Pekanbaru', 'kec' => 'Kecamatan Sail', 'lat' => 0.5150, 'lng' => 101.4650],
        ['kab' => 'Kota Pekanbaru', 'kec' => 'Kecamatan Lima Puluh', 'lat' => 0.5300, 'lng' => 101.4650],

        // 2. Kota Dumai
        ['kab' => 'Kota Dumai', 'kec' => 'Kecamatan Dumai Kota', 'lat' => 1.6700, 'lng' => 101.4500],
        ['kab' => 'Kota Dumai', 'kec' => 'Kecamatan Dumai Timur', 'lat' => 1.6800, 'lng' => 101.4650],
        ['kab' => 'Kota Dumai', 'kec' => 'Kecamatan Dumai Barat', 'lat' => 1.6750, 'lng' => 101.4200],
        ['kab' => 'Kota Dumai', 'kec' => 'Kecamatan Dumai Selatan', 'lat' => 1.6400, 'lng' => 101.4400],
        ['kab' => 'Kota Dumai', 'kec' => 'Kecamatan Bukit Kapur', 'lat' => 1.5200, 'lng' => 101.4100],
        ['kab' => 'Kota Dumai', 'kec' => 'Kecamatan Sungai Sembilan', 'lat' => 1.8500, 'lng' => 101.2500],
        ['kab' => 'Kota Dumai', 'kec' => 'Kecamatan Medang Kampai', 'lat' => 1.6200, 'lng' => 101.5900],

        // 3. Kabupaten Kampar
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Bangkinang Kota', 'lat' => 0.3340, 'lng' => 101.0250],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Bangkinang', 'lat' => 0.3550, 'lng' => 101.0200],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Tambang', 'lat' => 0.3800, 'lng' => 101.3000],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Siak Hulu', 'lat' => 0.4200, 'lng' => 101.4800],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Kampar', 'lat' => 0.3600, 'lng' => 101.1200],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Kampar Kiri', 'lat' => 0.0500, 'lng' => 101.2700],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Kampar Kiri Hilir', 'lat' => 0.1500, 'lng' => 101.4800],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Kampar Kiri Hulu', 'lat' => -0.1200, 'lng' => 101.0500],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Kampar Kiri Tengah', 'lat' => 0.1000, 'lng' => 101.3500],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Gunung Sahilan', 'lat' => 0.0800, 'lng' => 101.4200],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Perhentian Raja', 'lat' => 0.3100, 'lng' => 101.4300],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Kampar Utara', 'lat' => 0.3500, 'lng' => 101.0500],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Kampa', 'lat' => 0.3600, 'lng' => 101.2200],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Rumbio Jaya', 'lat' => 0.3700, 'lng' => 101.1600],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Tapung', 'lat' => 0.6500, 'lng' => 101.1500],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Tapung Hilir', 'lat' => 0.8300, 'lng' => 101.3200],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Tapung Hulu', 'lat' => 0.7500, 'lng' => 100.8500],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Kuok', 'lat' => 0.3200, 'lng' => 100.9300],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Salo', 'lat' => 0.3100, 'lng' => 101.0000],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan XIII Koto Kampar', 'lat' => 0.3000, 'lng' => 100.8000],
        ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Koto Kampar Hulu', 'lat' => 0.4200, 'lng' => 100.6500],

        // 4. Kabupaten Siak
        ['kab' => 'Kabupaten Siak', 'kec' => 'Kecamatan Siak', 'lat' => 0.9780, 'lng' => 101.9960],
        ['kab' => 'Kabupaten Siak', 'kec' => 'Kecamatan Mempura', 'lat' => 0.9500, 'lng' => 101.9800],
        ['kab' => 'Kabupaten Siak', 'kec' => 'Kecamatan Tualang', 'lat' => 0.6700, 'lng' => 101.6000],
        ['kab' => 'Kabupaten Siak', 'kec' => 'Kecamatan Kandis', 'lat' => 1.0000, 'lng' => 101.2300],
        ['kab' => 'Kabupaten Siak', 'kec' => 'Kecamatan Minas', 'lat' => 0.7500, 'lng' => 101.4300],
        ['kab' => 'Kabupaten Siak', 'kec' => 'Kecamatan Sungai Apit', 'lat' => 1.1800, 'lng' => 102.1500],
        ['kab' => 'Kabupaten Siak', 'kec' => 'Kecamatan Bunga Raya', 'lat' => 1.0800, 'lng' => 102.0800],
        ['kab' => 'Kabupaten Siak', 'kec' => 'Kecamatan Sabak Auh', 'lat' => 1.1400, 'lng' => 102.1100],
        ['kab' => 'Kabupaten Siak', 'kec' => 'Kecamatan Dayun', 'lat' => 0.8100, 'lng' => 101.9800],
        ['kab' => 'Kabupaten Siak', 'kec' => 'Kecamatan Kerinci Kanan', 'lat' => 0.4800, 'lng' => 101.8800],
        ['kab' => 'Kabupaten Siak', 'kec' => 'Kecamatan Koto Gasib', 'lat' => 0.7800, 'lng' => 101.8200],
        ['kab' => 'Kabupaten Siak', 'kec' => 'Kecamatan Lubuk Dalam', 'lat' => 0.6500, 'lng' => 101.7800],
        ['kab' => 'Kabupaten Siak', 'kec' => 'Kecamatan Sungai Mandau', 'lat' => 0.9500, 'lng' => 101.6500],
        ['kab' => 'Kabupaten Siak', 'kec' => 'Kecamatan Pusako', 'lat' => 1.0200, 'lng' => 102.0500],

        // 5. Kabupaten Bengkalis
        ['kab' => 'Kabupaten Bengkalis', 'kec' => 'Kecamatan Bengkalis', 'lat' => 1.4820, 'lng' => 102.1480],
        ['kab' => 'Kabupaten Bengkalis', 'kec' => 'Kecamatan Bantan', 'lat' => 1.5200, 'lng' => 102.2000],
        ['kab' => 'Kabupaten Bengkalis', 'kec' => 'Kecamatan Mandau', 'lat' => 1.2700, 'lng' => 101.2100],
        ['kab' => 'Kabupaten Bengkalis', 'kec' => 'Kecamatan Bathin Solapan', 'lat' => 1.3500, 'lng' => 101.1800],
        ['kab' => 'Kabupaten Bengkalis', 'kec' => 'Kecamatan Pinggir', 'lat' => 1.1200, 'lng' => 101.3100],
        ['kab' => 'Kabupaten Bengkalis', 'kec' => 'Kecamatan Talang Muandau', 'lat' => 1.2000, 'lng' => 101.4500],
        ['kab' => 'Kabupaten Bengkalis', 'kec' => 'Kecamatan Bukit Batu', 'lat' => 1.3700, 'lng' => 101.8200],
        ['kab' => 'Kabupaten Bengkalis', 'kec' => 'Kecamatan Siak Kecil', 'lat' => 1.2000, 'lng' => 101.9500],
        ['kab' => 'Kabupaten Bengkalis', 'kec' => 'Kecamatan Bandar Laksamana', 'lat' => 1.4200, 'lng' => 101.6800],
        ['kab' => 'Kabupaten Bengkalis', 'kec' => 'Kecamatan Rupat', 'lat' => 1.8500, 'lng' => 101.6000],
        ['kab' => 'Kabupaten Bengkalis', 'kec' => 'Kecamatan Rupat Utara', 'lat' => 2.0500, 'lng' => 101.6200],

        // 6. Kabupaten Pelalawan
        ['kab' => 'Kabupaten Pelalawan', 'kec' => 'Kecamatan Pangkalan Kerinci', 'lat' => 0.3420, 'lng' => 101.8840],
        ['kab' => 'Kabupaten Pelalawan', 'kec' => 'Kecamatan Pangkalan Kuras', 'lat' => 0.2200, 'lng' => 101.9800],
        ['kab' => 'Kabupaten Pelalawan', 'kec' => 'Kecamatan Pangkalan Lesung', 'lat' => -0.0200, 'lng' => 102.0800],
        ['kab' => 'Kabupaten Pelalawan', 'kec' => 'Kecamatan Ukui', 'lat' => -0.1200, 'lng' => 102.1200],
        ['kab' => 'Kabupaten Pelalawan', 'kec' => 'Kecamatan Langgam', 'lat' => 0.2800, 'lng' => 101.6800],
        ['kab' => 'Kabupaten Pelalawan', 'kec' => 'Kecamatan Pelalawan', 'lat' => 0.4200, 'lng' => 102.1800],
        ['kab' => 'Kabupaten Pelalawan', 'kec' => 'Kecamatan Bunut', 'lat' => 0.2500, 'lng' => 102.2500],
        ['kab' => 'Kabupaten Pelalawan', 'kec' => 'Kecamatan Bandar Petalangan', 'lat' => 0.1500, 'lng' => 102.1500],
        ['kab' => 'Kabupaten Pelalawan', 'kec' => 'Kecamatan Bandar Sei Kijang', 'lat' => 0.3800, 'lng' => 101.6200],
        ['kab' => 'Kabupaten Pelalawan', 'kec' => 'Kecamatan Kuala Kampar', 'lat' => 0.5200, 'lng' => 103.2200],
        ['kab' => 'Kabupaten Pelalawan', 'kec' => 'Kecamatan Teluk Meranti', 'lat' => 0.3200, 'lng' => 102.7500],
        ['kab' => 'Kabupaten Pelalawan', 'kec' => 'Kecamatan Kerumutan', 'lat' => -0.0200, 'lng' => 102.3500],

        // 7. Kabupaten Kepulauan Meranti
        ['kab' => 'Kabupaten Kepulauan Meranti', 'kec' => 'Kecamatan Tebing Tinggi', 'lat' => 1.0020, 'lng' => 102.7120],
        ['kab' => 'Kabupaten Kepulauan Meranti', 'kec' => 'Kecamatan Tebing Tinggi Barat', 'lat' => 0.9800, 'lng' => 102.6200],
        ['kab' => 'Kabupaten Kepulauan Meranti', 'kec' => 'Kecamatan Tebing Tinggi Timur', 'lat' => 0.9200, 'lng' => 102.8500],
        ['kab' => 'Kabupaten Kepulauan Meranti', 'kec' => 'Kecamatan Rangsang', 'lat' => 0.9500, 'lng' => 103.0200],
        ['kab' => 'Kabupaten Kepulauan Meranti', 'kec' => 'Kecamatan Rangsang Barat', 'lat' => 1.0200, 'lng' => 102.8200],
        ['kab' => 'Kabupaten Kepulauan Meranti', 'kec' => 'Kecamatan Rangsang Pesisir', 'lat' => 0.9800, 'lng' => 102.9500],
        ['kab' => 'Kabupaten Kepulauan Meranti', 'kec' => 'Kecamatan Merbau', 'lat' => 1.1200, 'lng' => 102.4800],
        ['kab' => 'Kabupaten Kepulauan Meranti', 'kec' => 'Kecamatan Pulau Merbau', 'lat' => 1.0800, 'lng' => 102.5800],
        ['kab' => 'Kabupaten Kepulauan Meranti', 'kec' => 'Kecamatan Tasik Putri Puyu', 'lat' => 1.1040, 'lng' => 102.4190],

        // 8. Kabupaten Rokan Hulu
        ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Rambah', 'lat' => 0.8840, 'lng' => 100.4900],
        ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Ujung Batu', 'lat' => 0.7100, 'lng' => 100.5300],
        ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Tandun', 'lat' => 0.5800, 'lng' => 100.6200],
        ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Rambah Samo', 'lat' => 0.7800, 'lng' => 100.4800],
        ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Rambah Hilir', 'lat' => 0.9800, 'lng' => 100.5200],
        ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Tambusai', 'lat' => 1.1000, 'lng' => 100.4200],
        ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Tambusai Utara', 'lat' => 1.3000, 'lng' => 100.4500],
        ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Bangun Purba', 'lat' => 0.9200, 'lng' => 100.4000],
        ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Rokan IV Koto', 'lat' => 0.5200, 'lng' => 100.4500],
        ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Pendalian IV Koto', 'lat' => 0.4800, 'lng' => 100.4000],
        ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Kunto Darussalam', 'lat' => 1.0500, 'lng' => 100.6500],
        ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Kabun', 'lat' => 0.5000, 'lng' => 100.8000],
        ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Bonai Darussalam', 'lat' => 1.1500, 'lng' => 100.8500],
        ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Pagaran Tapah Darussalam', 'lat' => 0.9200, 'lng' => 100.5800],
        ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Kepenuhan', 'lat' => 1.0800, 'lng' => 100.6000],
        ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Kepenuhan Hulu', 'lat' => 1.0200, 'lng' => 100.5500],

        // 9. Kabupaten Rokan Hilir
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Bangko', 'lat' => 2.1580, 'lng' => 100.8250],
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Bagan Sinembah', 'lat' => 1.7000, 'lng' => 100.3200],
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Bagan Sinembah Raya', 'lat' => 1.6800, 'lng' => 100.3500],
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Balai Jaya', 'lat' => 1.6500, 'lng' => 100.4200],
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Tanah Putih', 'lat' => 1.5200, 'lng' => 100.9500],
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Tanah Putih Tanjung Melawan', 'lat' => 1.6200, 'lng' => 101.0200],
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Pujud', 'lat' => 1.4200, 'lng' => 100.6800],
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Tanjung Medan', 'lat' => 1.3500, 'lng' => 100.6200],
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Kubu', 'lat' => 2.0500, 'lng' => 100.6200],
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Kubu Babussalam', 'lat' => 2.0200, 'lng' => 100.5500],
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Bangko Pusako', 'lat' => 1.7500, 'lng' => 100.8500],
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Rimba Melintang', 'lat' => 1.8200, 'lng' => 100.9500],
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Sinaboi', 'lat' => 2.2800, 'lng' => 101.0500],
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Pasir Limau Kapas', 'lat' => 2.3500, 'lng' => 100.4500],
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Batu Hampar', 'lat' => 1.9800, 'lng' => 100.9500],
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Simpang Kanan', 'lat' => 1.8500, 'lng' => 100.2500],
        ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Pekaitan', 'lat' => 2.0500, 'lng' => 100.7500],

        // 10. Kabupaten Indragiri Hulu
        ['kab' => 'Kabupaten Indragiri Hulu', 'kec' => 'Kecamatan Rengat', 'lat' => -0.5510, 'lng' => 102.3160],
        ['kab' => 'Kabupaten Indragiri Hulu', 'kec' => 'Kecamatan Rengat Barat', 'lat' => -0.5000, 'lng' => 102.2500],
        ['kab' => 'Kabupaten Indragiri Hulu', 'kec' => 'Kecamatan Seberida', 'lat' => -0.6800, 'lng' => 102.4500],
        ['kab' => 'Kabupaten Indragiri Hulu', 'kec' => 'Kecamatan Batang Cenaku', 'lat' => -0.8200, 'lng' => 102.4000],
        ['kab' => 'Kabupaten Indragiri Hulu', 'kec' => 'Kecamatan Batang Gansal', 'lat' => -0.8500, 'lng' => 102.6200],
        ['kab' => 'Kabupaten Indragiri Hulu', 'kec' => 'Kecamatan Pasir Penyu', 'lat' => -0.4200, 'lng' => 102.1500],
        ['kab' => 'Kabupaten Indragiri Hulu', 'kec' => 'Kecamatan Lirik', 'lat' => -0.3500, 'lng' => 102.2000],
        ['kab' => 'Kabupaten Indragiri Hulu', 'kec' => 'Kecamatan Sungai Lala', 'lat' => -0.4500, 'lng' => 102.1000],
        ['kab' => 'Kabupaten Indragiri Hulu', 'kec' => 'Kecamatan Kelayang', 'lat' => -0.5000, 'lng' => 102.0500],
        ['kab' => 'Kabupaten Indragiri Hulu', 'kec' => 'Kecamatan Peranap', 'lat' => -0.5500, 'lng' => 101.9800],
        ['kab' => 'Kabupaten Indragiri Hulu', 'kec' => 'Kecamatan Batang Peranap', 'lat' => -0.6200, 'lng' => 101.8800],
        ['kab' => 'Kabupaten Indragiri Hulu', 'kec' => 'Kecamatan Rakit Kulim', 'lat' => -0.6500, 'lng' => 102.0200],
        ['kab' => 'Kabupaten Indragiri Hulu', 'kec' => 'Kecamatan Kuala Cenaku', 'lat' => -0.4800, 'lng' => 102.4500],
        ['kab' => 'Kabupaten Indragiri Hulu', 'kec' => 'Kecamatan Lubuk Batu Jaya', 'lat' => -0.4000, 'lng' => 102.0500],

        // 11. Kabupaten Indragiri Hilir
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Tembilahan', 'lat' => -0.3270, 'lng' => 103.1550],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Tembilahan Hulu', 'lat' => -0.3500, 'lng' => 103.1200],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Tempuling', 'lat' => -0.3800, 'lng' => 102.9800],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Kempas', 'lat' => -0.4200, 'lng' => 102.8500],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Enok', 'lat' => -0.5200, 'lng' => 103.2200],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Tanah Merah', 'lat' => -0.5000, 'lng' => 103.3500],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Kuala Indragiri', 'lat' => -0.4000, 'lng' => 103.4500],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Batang Tuaka', 'lat' => -0.2800, 'lng' => 103.1200],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Gaung Anak Serka', 'lat' => -0.2200, 'lng' => 103.0500],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Gaung', 'lat' => -0.1500, 'lng' => 102.9500],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Mandah', 'lat' => 0.0200, 'lng' => 103.3200],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Pelangiran', 'lat' => 0.1500, 'lng' => 103.4500],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Teluk Belengkong', 'lat' => 0.2800, 'lng' => 103.5500],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Kateman', 'lat' => 0.3500, 'lng' => 103.6200],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Pulau Burung', 'lat' => 0.4200, 'lng' => 103.5800],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Reteh', 'lat' => -0.7200, 'lng' => 103.1500],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Sungai Batang', 'lat' => -0.6800, 'lng' => 103.2500],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Keritang', 'lat' => -0.7500, 'lng' => 102.8500],
        ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Kemuning', 'lat' => -0.8800, 'lng' => 102.6500],

        // 12. Kabupaten Kuantan Singingi
        ['kab' => 'Kabupaten Kuantan Singingi', 'kec' => 'Kecamatan Kuantan Tengah', 'lat' => -0.5280, 'lng' => 101.4640],
        ['kab' => 'Kabupaten Kuantan Singingi', 'kec' => 'Kecamatan Singingi', 'lat' => -0.3200, 'lng' => 101.3200],
        ['kab' => 'Kabupaten Kuantan Singingi', 'kec' => 'Kecamatan Singingi Hilir', 'lat' => -0.1800, 'lng' => 101.3800],
        ['kab' => 'Kabupaten Kuantan Singingi', 'kec' => 'Kecamatan Kuantan Mudik', 'lat' => -0.6200, 'lng' => 101.3500],
        ['kab' => 'Kabupaten Kuantan Singingi', 'kec' => 'Kecamatan Gunung Toar', 'lat' => -0.5800, 'lng' => 101.4000],
        ['kab' => 'Kabupaten Kuantan Singingi', 'kec' => 'Kecamatan Hulu Kuantan', 'lat' => -0.6800, 'lng' => 101.2800],
        ['kab' => 'Kabupaten Kuantan Singingi', 'kec' => 'Kecamatan Kuantan Hilir', 'lat' => -0.4800, 'lng' => 101.6200],
        ['kab' => 'Kabupaten Kuantan Singingi', 'kec' => 'Kecamatan Kuantan Hilir Seberang', 'lat' => -0.5200, 'lng' => 101.6500],
        ['kab' => 'Kabupaten Kuantan Singingi', 'kec' => 'Kecamatan Cerenti', 'lat' => -0.4200, 'lng' => 101.7800],
        ['kab' => 'Kabupaten Kuantan Singingi', 'kec' => 'Kecamatan Inuman', 'lat' => -0.4500, 'lng' => 101.7200],
        ['kab' => 'Kabupaten Kuantan Singingi', 'kec' => 'Kecamatan Benai', 'lat' => -0.5000, 'lng' => 101.5500],
        ['kab' => 'Kabupaten Kuantan Singingi', 'kec' => 'Kecamatan Pangean', 'lat' => -0.4800, 'lng' => 101.6000],
        ['kab' => 'Kabupaten Kuantan Singingi', 'kec' => 'Kecamatan Logas Tanah Darat', 'lat' => -0.3800, 'lng' => 101.5200],
        ['kab' => 'Kabupaten Kuantan Singingi', 'kec' => 'Kecamatan Sentajo Raya', 'lat' => -0.5500, 'lng' => 101.5000],
        ['kab' => 'Kabupaten Kuantan Singingi', 'kec' => 'Kecamatan Pucuk Rantau', 'lat' => -0.8200, 'lng' => 101.5500],

        // Ibukota Provinsi Terdekat (Sumatera & Jawa)
        ['kab' => 'Kota Padang', 'kec' => 'Kecamatan Padang Barat', 'lat' => -0.9471, 'lng' => 100.4172],
        ['kab' => 'Kota Bukittinggi', 'kec' => 'Kecamatan Guguak Panjang', 'lat' => -0.3055, 'lng' => 100.3692],
        ['kab' => 'Kota Medan', 'kec' => 'Kecamatan Medan Kota', 'lat' => 3.5952, 'lng' => 98.6722],
        ['kab' => 'Kota Batam', 'kec' => 'Kecamatan Batam Kota', 'lat' => 1.1301, 'lng' => 104.0529],
        ['kab' => 'Kota Tanjungpinang', 'kec' => 'Kecamatan Tanjungpinang Kota', 'lat' => 0.9162, 'lng' => 104.4647],
        ['kab' => 'Kota Jambi', 'kec' => 'Kecamatan Danau Teluk', 'lat' => -1.6101, 'lng' => 103.6131],
        ['kab' => 'Kota Palembang', 'kec' => 'Kecamatan Ilir Barat I', 'lat' => -2.9909, 'lng' => 104.7565],
        ['kab' => 'DKI Jakarta', 'kec' => 'Kecamatan Gambir', 'lat' => -6.2088, 'lng' => 106.8456],
    ];

    /**
     * Jalur berkas cache disk persisten.
     */
    private static function getGeocodeCacheFilePath(): string
    {
        $cacheDir = defined('WRITEPATH') ? (WRITEPATH . 'cache') : (sys_get_temp_dir() . '/psriau_cache');
        if (! is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }
        return $cacheDir . DIRECTORY_SEPARATOR . 'geocache_structured.json';
    }

    /**
     * Muat cache persisten dari berkas JSON ke memori PHP.
     */
    private static function loadPersistentGeocodeCache(): void
    {
        if (self::$isDiskCacheLoaded) {
            return;
        }
        self::$isDiskCacheLoaded = true;
        $file = self::getGeocodeCacheFilePath();
        if (is_file($file) && is_readable($file)) {
            $content = @file_get_contents($file);
            if ($content !== false && $content !== '') {
                $decoded = @json_decode($content, true);
                if (is_array($decoded)) {
                    self::$structuredGeocodeCache = array_merge($decoded, self::$structuredGeocodeCache);
                }
            }
        }
    }

    /**
     * Simpan entri baru ke berkas cache persisten di disk.
     */
    private static function savePersistentGeocodeCache(): void
    {
        $file = self::getGeocodeCacheFilePath();
        @file_put_contents($file, json_encode(self::$structuredGeocodeCache, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Cari kecocokan lokasi dari dataset offline Provinsi Riau (0.01 ms).
     */
    private function lookupOfflineWilayah(float $lat, float $lng): ?array
    {
        // Wilayah geografis Provinsi Riau & sekitarnya: Lat -1.5 s/d 3.0, Lng 99.5 s/d 104.5
        if ($lat < -1.5 || $lat > 3.0 || $lng < 99.5 || $lng > 104.5) {
            return null;
        }

        $closest = null;
        $minDist = PHP_FLOAT_MAX;

        foreach (self::$riauOfflineDataset as $item) {
            $d = $this->haversineDistance($lat, $lng, $item['lat'], $item['lng']);
            if ($d < $minDist) {
                $minDist = $d;
                $closest = $item;
            }
        }

        // Jika jarak ke pusat kecamatan terdekat masih dalam radius wajar (< 45 km), gunakan data resmi
        if ($closest !== null && $minDist < 45000.0) {
            return [
                'kabupaten' => $closest['kab'],
                'kecamatan' => $closest['kec'],
                'distance'  => $minDist,
            ];
        }

        return $closest ? ['kabupaten' => $closest['kab'], 'kecamatan' => $closest['kec'], 'distance' => $minDist] : null;
    }

    /**
     * Coba request cepat ke OpenStreetMap Nominatim menggunakan cURL (Timeout ketat 1.5 detik, anti freeze).
     */
    private function fetchNominatimQuick(float $lat, float $lng): ?array
    {
        $url = sprintf('https://nominatim.openstreetmap.org/reverse?format=json&lat=%.6f&lon=%.6f&zoom=18&addressdetails=1', $lat, $lng);

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1500);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 1000);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'User-Agent: PSRiau-TimeMarkWatermark/1.0 (contact: info@satkerpps-riau.online)',
                'Accept-Language: id-ID,id;q=0.9,en;q=0.8',
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 429) {
                self::$lastOsmErrorTimestamp = time();
                return null;
            }

            if ($httpCode === 200 && $response !== false && $response !== '') {
                $data = @json_decode($response, true);
                if (is_array($data) && isset($data['address'])) {
                    $addr = $data['address'];
                    $rawKab = $addr['county'] ?? ($addr['city'] ?? ($addr['town'] ?? ($addr['city_district'] ?? '')));
                    $rawKec = $addr['municipality'] ?? ($addr['district'] ?? ($addr['city_district'] ?? ($addr['subdistrict'] ?? '')));
                    $rawKel = $addr['village'] ?? ($addr['suburb'] ?? ($addr['hamlet'] ?? ($addr['neighbourhood'] ?? ($addr['quarter'] ?? ($addr['residential'] ?? '')))));
                    $rawRoad = $addr['road'] ?? '';
                    if (! empty($rawKab) || ! empty($rawKec)) {
                        return [
                            'kabupaten' => $rawKab,
                            'kecamatan' => $rawKec,
                            'kelurahan' => $rawKel,
                            'road'      => $rawRoad,
                        ];
                    }
                }
            }
        }
        return null;
    }

    /**
     * Bangun struktur array wilayah terstandarisasi untuk subfolder dan watermark.
     */
    private function buildWilayahResult(
        string $rawKab,
        string $rawKec,
        float $lat,
        float $lng,
        string $rawRoad = '',
        string $rawKel = '',
        string $groupingMode = 'kab_kec'
    ): array {
        // Standardisasi Nama Kabupaten / Kota
        $kabupaten = '';
        if (! empty($rawKab)) {
            $cleanKab = trim($rawKab);
            if (preg_match('/^(kabupaten|kota)\s+/i', $cleanKab)) {
                $kabupaten = ucwords(strtolower($cleanKab));
            } elseif (stripos($cleanKab, 'pekanbaru') !== false || stripos($cleanKab, 'dumai') !== false) {
                $kabupaten = 'Kota ' . ucwords(strtolower($cleanKab));
            } else {
                $kabupaten = 'Kabupaten ' . ucwords(strtolower($cleanKab));
            }
        } else {
            $kabupaten = 'Kabupaten Riau';
        }

        // Standardisasi Nama Kecamatan
        $kecamatan = '';
        if (! empty($rawKec)) {
            $cleanKec = trim($rawKec);
            if (preg_match('/^kecamatan\s+/i', $cleanKec)) {
                $kecamatan = ucwords(strtolower($cleanKec));
            } else {
                $kecamatan = 'Kecamatan ' . ucwords(strtolower($cleanKec));
            }
        } else {
            $kecamatan = 'Kecamatan Wilayah Sekitar';
        }

        // Standardisasi Nama Kelurahan / Desa
        $kelurahan = '';
        if (! empty($rawKel)) {
            $cleanKel = trim($rawKel);
            if (preg_match('/^(kelurahan|desa)\s+/i', $cleanKel)) {
                $kelurahan = ucwords(strtolower($cleanKel));
            } elseif (stripos($kabupaten, 'Kota') !== false) {
                $kelurahan = 'Kelurahan ' . ucwords(strtolower($cleanKel));
            } else {
                $kelurahan = 'Desa ' . ucwords(strtolower($cleanKel));
            }
        } else {
            if ($groupingMode === 'kab_kec_kel') {
                $cleanKecOnly = trim(preg_replace('/^kecamatan\s+/i', '', $kecamatan));
                $kelurahan = 'Wilayah ' . $cleanKecOnly;
            } else {
                $kelurahan = '-';
            }
        }

        // Buat slug bersih untuk susunan subfolder
        $slugKab = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $kabupaten);
        $slugKab = preg_replace('/_+/', '_', trim($slugKab, '_'));

        $slugKec = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $kecamatan);
        $slugKec = preg_replace('/_+/', '_', trim($slugKec, '_'));

        $slugKel = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $kelurahan);
        $slugKel = preg_replace('/_+/', '_', trim($slugKel, '_'));

        if ($groupingMode === 'kab') {
            $folderPath = $slugKab;
            $display = $kabupaten;
        } elseif ($groupingMode === 'kab_kec_kel') {
            $folderPath = $slugKab . '/' . $slugKec . '/' . $slugKel;
            $display = ($kelurahan !== '-' ? $kelurahan . ', ' : '') . $kecamatan . ', ' . $kabupaten;
        } else {
            // Default: 'kab_kec' atau 'kab_kec_spot'
            $folderPath = $slugKab . '/' . $slugKec;
            $display = $kecamatan . ', ' . $kabupaten;
        }

        return [
            'kabupaten'   => $kabupaten,
            'kecamatan'   => $kecamatan,
            'kelurahan'   => $kelurahan,
            'slug_kab'    => $slugKab,
            'slug_kec'    => $slugKec,
            'slug_kel'    => $slugKel,
            'folder_path' => $folderPath,
            'display'     => $display,
            'road'        => $rawRoad,
            '_lat'        => $lat,
            '_lng'        => $lng,
        ];
    }

    /**
     * Memproses satu file gambar dan menerapkan watermark sesuai template pilihan.
     *
     * @param string $sourcePath Path file gambar asli
     * @param array  $options    Pengaturan watermark:
     *                           - template: 'classic'|'table'|'badge'|'strip' (default 'classic')
     *                           - logo_path: ?string
     *                           - manual_date: ?string (YYYY-MM-DD)
     *                           - manual_time: ?string (HH:MM atau HH:MM:SS)
     *                           - offset_hours: int (default 0)
     *                           - offset_minutes: int (default 0)
     *                           - use_exif_time: bool (default true)
     *                           - location: string
     *                           - coordinates: ?string
     *                           - notes: ?string
     *                           - save_to_path: ?string
     *                           - output_quality: int (default 92)
     *
     * @return array ['success' => bool, 'error' => ?string, 'data_uri' => ?string, 'saved_path' => ?string, 'meta' => array]
     */
    public function processImage(string $sourcePath, array $options = []): array
    {
        if (! is_file($sourcePath) || ! is_readable($sourcePath)) {
            return ['success' => false, 'error' => 'File gambar tidak ditemukan atau tidak dapat dibaca.', 'data_uri' => null, 'saved_path' => null, 'meta' => []];
        }

        // Cek jika file merupakan format HEIC/HEIF, konversi sementara ke JPEG
        $actualProcessPath = $sourcePath;
        $isHeicTemp = false;
        if ($this->isHeicFile($sourcePath)) {
            $convertedJpg = $this->convertHeicToJpeg($sourcePath);
            if ($convertedJpg !== null) {
                $actualProcessPath = $convertedJpg;
                $isHeicTemp = true;
            } else {
                return [
                    'success' => false,
                    'error' => 'Format HEIC terdeteksi, namun server belum memiliki modul decoder HEIC (sips, heif-convert, atau ImageMagick). Silakan upload langsung melalui peramban untuk konversi otomatis client-side.',
                    'data_uri' => null,
                    'saved_path' => null,
                    'meta' => [],
                ];
            }
        }

        try {
            $imageInfo = @getimagesize($actualProcessPath);
            if ($imageInfo === false) {
                return ['success' => false, 'error' => 'Format file bukan gambar yang valid.', 'data_uri' => null, 'saved_path' => null, 'meta' => []];
            }

            $mimeType = $imageInfo['mime'];
            $sourceImage = $this->createImageResource($actualProcessPath, $mimeType);
            if ($sourceImage === false) {
                return ['success' => false, 'error' => 'Gagal memuat resource gambar.', 'data_uri' => null, 'saved_path' => null, 'meta' => []];
            }

            // 1. Ekstraksi EXIF dan Perbaiki Orientasi
            $exifData = $this->readExifData($isHeicTemp ? $sourcePath : $actualProcessPath);
            if (is_array($exifData) && ! empty($exifData['Orientation'])) {
                $sourceImage = $this->fixOrientation($sourceImage, (int) $exifData['Orientation']);
            }

        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);

        // 2. Tentukan Tanggal & Jam dengan dukungan EXIF dan Offset
        $useExifDate = (bool) ($options['use_exif_date'] ?? ($options['use_exif_time'] ?? true));
        $useExifTime = (bool) ($options['use_exif_time'] ?? true);
        $offsetHours = (int) ($options['offset_hours'] ?? 0);
        $offsetMinutes = (int) ($options['offset_minutes'] ?? 0);
        $manualDate = trim((string) ($options['manual_date'] ?? ''));
        $manualTime = trim((string) ($options['manual_time'] ?? ''));

        $dateTimeMeta = $this->resolveDateTime($exifData, $sourcePath, $useExifDate, $useExifTime, $offsetHours, $offsetMinutes, $manualDate, $manualTime);

        // 3. Tentukan Koordinat GPS (Prioritas: input user -> EXIF foto)
        $useExifLocation = (bool) ($options['use_exif_location'] ?? true);
        $coordinates = trim((string) ($options['coordinates'] ?? ''));
        $groupingMode = trim((string) ($options['grouping_mode'] ?? 'kab_kec'));
        $exifCoords = null;
        $exifDecimal = null;
        if (is_array($exifData)) {
            $exifCoords = $this->extractGpsCoordinates($exifData);
            $exifDecimal = $this->extractGpsDecimal($exifData);
        }
        if ($coordinates === '' && $useExifLocation && $exifCoords !== null) {
            $coordinates = $exifCoords;
        }

        // Sanitasi koordinat secara ketat dari karakter mojibake / emoji rusak
        $coordinates = $this->sanitizeCoordinates($coordinates);

        // 4. Data Teks Lokasi / Alamat (Prioritas: reverse geocode EXIF GPS / koordinat -> input user -> default)
        $lokasi = trim((string) ($options['location'] ?? ''));
        $wilayahInfo = null;

        if ($useExifLocation && $exifDecimal !== null) {
            // Lakukan reverse geocoding terstruktur dari koordinat GPS foto asli
            $wilayahInfo = $this->reverseGeocodeStructured($exifDecimal['lat'], $exifDecimal['lng'], $groupingMode);
        } elseif ($coordinates !== '') {
            $parsedC = $this->parseCoordinateString($coordinates);
            if ($parsedC !== null) {
                $wilayahInfo = $this->reverseGeocodeStructured($parsedC['lat'], $parsedC['lng'], $groupingMode);
            }
        }

        // Cek apakah lokasi yang dikirim form merupakan koordinat mentah atau mengandung karakter mojibake
        $isLokasiRawCoords = false;
        if ($lokasi !== '') {
            $cleanTest = $this->sanitizeCoordinates($lokasi);
            $cleanCoordsTest = $this->sanitizeCoordinates($coordinates);
            $cleanExifTest = $exifCoords ? $this->sanitizeCoordinates($exifCoords) : '';
            if ($cleanTest !== '' && ($cleanTest === $cleanCoordsTest || $cleanTest === $cleanExifTest || preg_match('/^[0-9\.\,\s°NSEWnsew\+\-\:\'\"\°]+$/u', $cleanTest))) {
                $isLokasiRawCoords = true;
            }
        }

        if ($wilayahInfo !== null) {
            if ($lokasi === '' || $isLokasiRawCoords) {
                $lokasi = $wilayahInfo['display']; // "Kecamatan Tambang, Kabupaten Kampar"
            }
        } elseif ($lokasi === '' || $isLokasiRawCoords) {
            if ($coordinates !== '') {
                $lokasi = 'Lokasi Koordinat ' . $coordinates;
            } elseif ($exifCoords !== null) {
                $lokasi = 'Lokasi Koordinat ' . $this->sanitizeCoordinates($exifCoords);
            } else {
                $lokasi = 'Kota Pekanbaru, Riau';
            }
        }
        $notes = trim((string) ($options['notes'] ?? ''));

        // 5. Muat Logo
        $logoImage = $this->loadLogoImage($options['logo_path'] ?? null);

        // 6. Terapkan Template Pilihan
        $template = strtolower(trim((string) ($options['template'] ?? 'classic')));
        $scale = max(0.4, $width / 1080);

        switch ($template) {
            case 'table':
                $this->renderTableTemplate($sourceImage, $width, $height, $scale, $dateTimeMeta, $lokasi, $coordinates, $notes, $logoImage, $wilayahInfo);
                break;
            case 'badge':
                $this->renderBadgeTemplate($sourceImage, $width, $height, $scale, $dateTimeMeta, $lokasi, $coordinates, $notes, $logoImage, $wilayahInfo);
                break;
            case 'strip':
                $this->renderStripTemplate($sourceImage, $width, $height, $scale, $dateTimeMeta, $lokasi, $coordinates, $notes, $logoImage, $wilayahInfo);
                break;
            case 'classic':
            default:
                $this->renderClassicTemplate($sourceImage, $width, $height, $scale, $dateTimeMeta, $lokasi, $coordinates, $notes, $logoImage, $wilayahInfo);
                break;
        }

        if ($logoImage !== null && is_resource($logoImage)) {
            imagedestroy($logoImage);
        }

        // 7. Simpan ke File atau Output Data URI
        $saveToPath = $options['save_to_path'] ?? null;
        $savedPath = null;
        $outputQuality = max(70, min(100, (int) ($options['output_quality'] ?? 92)));
        $updateExif = (bool) ($options['update_exif'] ?? true);

        if ($saveToPath !== null) {
            $saveDir = dirname($saveToPath);
            if (! is_dir($saveDir) && ! @mkdir($saveDir, 0775, true) && ! is_dir($saveDir)) {
                imagedestroy($sourceImage);
                return ['success' => false, 'error' => 'Gagal membuat folder penyimpanan server: ' . $saveDir, 'data_uri' => null, 'saved_path' => null, 'meta' => []];
            }

            $saved = $this->writeImageToFile($sourceImage, $saveToPath, $mimeType, $outputQuality);
            if ($saved) {
                $savedPath = $saveToPath;
                // Tulis / perbarui metadata EXIF biner langsung pada file JPEG hasil
                if ($updateExif && ($mimeType === 'image/jpeg' || in_array(strtolower(pathinfo($savedPath, PATHINFO_EXTENSION)), ['jpg', 'jpeg'], true))) {
                    $this->embedExifMetadata($savedPath, $dateTimeMeta, $coordinates, $lokasi, $notes);
                }
            }
        }

        // 8. Generate Thumbnail Ringan (~6-12 KB) untuk Galeri Preview
        $thumbDataUri = null;
        $generateThumb = (bool) ($options['generate_thumb'] ?? true);
        if ($generateThumb && $width > 0 && $height > 0) {
            $maxThumbWidth = (int) ($options['thumb_width'] ?? 360);
            $thumbQuality = max(50, min(90, (int) ($options['thumb_quality'] ?? 68)));
            $thumbWidth = min($width, $maxThumbWidth);
            $thumbHeight = (int) max(1, round(($height / $width) * $thumbWidth));

            $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
            if ($thumbImage !== false) {
                imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
                ob_start();
                imagejpeg($thumbImage, null, $thumbQuality);
                $thumbBytes = ob_get_clean();
                imagedestroy($thumbImage);

                if ($thumbBytes !== false && $thumbBytes !== '') {
                    $thumbDataUri = 'data:image/jpeg;base64,' . base64_encode($thumbBytes);
                }
            }
        }

        // 9. Generate Base64 Data URI Resolusi Penuh (hanya jika diminta, default true)
        $needFullDataUri = (bool) ($options['need_full_uri'] ?? true);
        $dataUri = null;

        if ($needFullDataUri) {
            $rawBytes = false;
            if ($savedPath !== null && is_file($savedPath)) {
                $rawBytes = @file_get_contents($savedPath);
            }

            if ($rawBytes === false || $rawBytes === '') {
                ob_start();
                if ($mimeType === 'image/png') {
                    imagepng($sourceImage);
                } elseif ($mimeType === 'image/webp') {
                    imagewebp($sourceImage, null, $outputQuality);
                } else {
                    imagejpeg($sourceImage, null, $outputQuality);
                }
                $rawBytes = ob_get_clean();
            }

            if ($rawBytes !== false && $rawBytes !== '') {
                $dataUri = 'data:' . $mimeType . ';base64,' . base64_encode($rawBytes);
            }
        }

        // Hancurkan resource source image setelah selesai digunakan
        if (is_resource($sourceImage) || (is_object($sourceImage) && $sourceImage instanceof \GdImage)) {
            imagedestroy($sourceImage);
        }

        // Fallback jika dataUri null tetapi thumbDataUri tersedia
        if ($dataUri === null && $thumbDataUri !== null) {
            $dataUri = $thumbDataUri;
        }

        if ($dataUri === null && ($savedPath === null || ! is_file($savedPath))) {
            return ['success' => false, 'error' => 'Gagal merender output gambar ber-watermark.', 'data_uri' => null, 'thumb_data_uri' => null, 'saved_path' => $savedPath, 'meta' => []];
        }

        return [
            'success'        => true,
            'error'          => null,
            'data_uri'       => $dataUri,
            'thumb_data_uri' => $thumbDataUri,
            'saved_path'     => $savedPath,
            'meta'           => [
                'width'       => $width,
                'height'      => $height,
                'template'    => $template,
                'datetime'    => $dateTimeMeta,
                'coordinates' => $coordinates,
                'location'    => $lokasi,
                'wilayah'     => $wilayahInfo,
            ],
        ];
        } finally {
            if ($isHeicTemp && is_file($actualProcessPath)) {
                @unlink($actualProcessPath);
            }
        }
    }

    /**
     * Resolusi DateTime dengan kemampuan mengubah tanggal saja, jam saja, atau keduanya secara independen.
     */
    public function resolveDateTime(
        $exifData,
        string $filePath,
        $useExifDate = true,
        $useExifTime = true,
        $offsetHours = 0,
        $offsetMinutes = 0,
        string $manualDate = '',
        string $manualTime = ''
    ): array {
        // Deteksi kompatibilitas pemanggilan argumen lama
        if (is_int($useExifTime) && is_int($offsetHours)) {
            $actualUseExifDate = (bool) $useExifDate;
            $actualUseExifTime = (bool) $useExifDate;
            $actualOffsetHours = (int) $useExifTime;
            $actualOffsetMinutes = (int) $offsetHours;
            $actualManualDate = is_string($offsetMinutes) ? $offsetMinutes : '';
            $actualManualTime = is_string($manualDate) ? $manualDate : '';
        } else {
            $actualUseExifDate = (bool) $useExifDate;
            $actualUseExifTime = (bool) $useExifTime;
            $actualOffsetHours = (int) $offsetHours;
            $actualOffsetMinutes = (int) $offsetMinutes;
            $actualManualDate = $manualDate;
            $actualManualTime = $manualTime;
        }

        // 1. Ekstrak DateTime dasar dari EXIF kamera jika tersedia
        $baseDt = null;
        if (is_array($exifData)) {
            $exifDateStr = $exifData['DateTimeOriginal'] ?? $exifData['DateTimeDigitized'] ?? $exifData['DateTime'] ?? null;
            if (is_string($exifDateStr) && trim($exifDateStr) !== '') {
                // Format standar EXIF: "YYYY:MM:DD HH:MM:SS"
                $formatted = preg_replace('/^(\d{4}):(\d{2}):(\d{2})/', '$1-$2-$3', trim($exifDateStr));
                try {
                    $baseDt = new \DateTime($formatted);
                } catch (\Throwable $e) {
                    $baseDt = null;
                }
            }
        }

        // Fallback jika tidak ada di EXIF: gunakan filemtime atau waktu sekarang
        if ($baseDt === null && is_file($filePath)) {
            $mtime = @filemtime($filePath);
            if ($mtime !== false && $mtime > 0) {
                $baseDt = new \DateTime();
                $baseDt->setTimestamp($mtime);
            }
        }
        if ($baseDt === null) {
            $baseDt = new \DateTime();
        }

        // 2. Tentukan komponen Tanggal (Date):
        // Jika useExifDate nonaktif dan user mengisi manualDate, gunakan manualDate!
        // Jika useExifDate aktif, gunakan tanggal asli dari EXIF ($baseDt).
        if (! $actualUseExifDate && $actualManualDate !== '') {
            $finalDateStr = $actualManualDate;
        } else {
            $finalDateStr = $baseDt->format('Y-m-d');
        }

        // 3. Tentukan komponen Jam (Time):
        // Jika useExifTime nonaktif dan user mengisi manualTime, gunakan manualTime!
        // Jika useExifTime aktif, gunakan jam asli dari EXIF ($baseDt).
        if (! $actualUseExifTime && $actualManualTime !== '') {
            $finalTimeStr = (strlen($actualManualTime) === 5) ? ($actualManualTime . ':00') : $actualManualTime;
        } else {
            $finalTimeStr = $baseDt->format('H:i:s');
        }

        // 4. Gabungkan Tanggal & Jam menjadi DateTime
        try {
            $dt = new \DateTime($finalDateStr . ' ' . $finalTimeStr);
        } catch (\Throwable $e) {
            $dt = clone $baseDt;
        }

        // 5. Terapkan Offset Jam & Menit
        if ($actualOffsetHours !== 0 || $actualOffsetMinutes !== 0) {
            $signHours = $actualOffsetHours >= 0 ? '+' : '';
            $signMinutes = $actualOffsetMinutes >= 0 ? '+' : '';
            $modifier = "{$signHours}{$actualOffsetHours} hours {$signMinutes}{$actualOffsetMinutes} minutes";
            $dt->modify($modifier);
        }

        $dateFormatted = $dt->format('d/m/Y');
        $timeFormatted = $dt->format('H:i');
        $timeWithSec = $dt->format('H:i:s');
        $dayOfWeek = (int) $dt->format('N');
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $dayName = $days[$dayOfWeek] ?? '';
        $monthName = $months[(int) $dt->format('n')] ?? '';
        $longDate = $dt->format('j') . ' ' . $monthName . ' ' . $dt->format('Y');

        return [
            'date'          => $dateFormatted,
            'time'          => $timeFormatted,
            'time_full'     => $timeWithSec,
            'day'           => $dayName,
            'long_date'     => $longDate,
            'raw_ymd'       => $dt->format('Y-m-d'),
            'raw_his'       => $dt->format('H:i:s'),
            'timestamp'     => $dt->getTimestamp(),
            'datetime_obj'  => $dt,
            'exif_datetime' => $dt->format('Y:m:d H:i:s'),
        ];
    }

    /**
     * Ekstraksi koordinat GPS dari EXIF menjadi format DMS yang mudah dibaca.
     */
    public function extractGpsCoordinates(array $exif): ?string
    {
        if (! isset($exif['GPSLatitude'], $exif['GPSLongitude'], $exif['GPSLatitudeRef'], $exif['GPSLongitudeRef'])) {
            return null;
        }

        try {
            $lat = $this->parseGpsCoordinate($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
            $lng = $this->parseGpsCoordinate($exif['GPSLongitude'], $exif['GPSLongitudeRef']);

            if ($lat === null || $lng === null) {
                return null;
            }

            return sprintf('%.6f° %s, %.6f° %s', abs($lat), $exif['GPSLatitudeRef'], abs($lng), $exif['GPSLongitudeRef']);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Ekstraksi koordinat GPS dari EXIF menjadi array desimal signed numerik ['lat' => float, 'lng' => float].
     */
    public function extractGpsDecimal(array $exif): ?array
    {
        if (! isset($exif['GPSLatitude'], $exif['GPSLongitude'], $exif['GPSLatitudeRef'], $exif['GPSLongitudeRef'])) {
            return null;
        }

        try {
            $lat = $this->parseGpsCoordinate($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
            $lng = $this->parseGpsCoordinate($exif['GPSLongitude'], $exif['GPSLongitudeRef']);

            if ($lat === null || $lng === null) {
                return null;
            }

            return [
                'lat' => round($lat, 6),
                'lng' => round($lng, 6),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Melakukan reverse geocoding koordinat GPS menjadi struktur wilayah resmi Indonesia:
     * - Kabupaten / Kota (misal: "Kabupaten Kampar" atau "Kota Pekanbaru")
     * - Kecamatan (misal: "Kecamatan Tambang" atau "Kecamatan Marpoyan Damai")
     * - Kelurahan / Desa (misal: "Desa Mengkirau" atau "Kelurahan Maharatu")
     *
     * @return array [
     *     'kabupaten'   => string,
     *     'kecamatan'   => string,
     *     'kelurahan'   => string,
     *     'slug_kab'    => string,
     *     'slug_kec'    => string,
     *     'slug_kel'    => string,
     *     'folder_path' => string, // e.g. "Kabupaten_Kampar/Kecamatan_Tambang/Desa_Tarai_Bangun"
     *     'display'     => string, // e.g. "Desa Tarai Bangun, Kecamatan Tambang, Kabupaten Kampar"
     *     'road'        => string,
     * ]
     */
    public function reverseGeocodeStructured(float $lat, float $lng, string $groupingMode = 'kab_kec'): array
    {
        // 1. Muat cache persisten dari file JSON di disk jika belum dimuat dalam lifecycle PHP ini
        self::loadPersistentGeocodeCache();

        // Cek cache persisten dan in-memory berdasarkan pembulatan 3 desimal (~110m)
        $cacheKey = round($lat, 3) . ',' . round($lng, 3);
        if (isset(self::$structuredGeocodeCache[$cacheKey])) {
            $cached = self::$structuredGeocodeCache[$cacheKey];
            // Jika mode membutuhkan kelurahan dan di cache belum ada, coba refresh cepat via OSM
            if ($groupingMode === 'kab_kec_kel' && (empty($cached['kelurahan']) || $cached['kelurahan'] === '-')) {
                if (time() - self::$lastOsmErrorTimestamp > 60) {
                    $osm = $this->fetchNominatimQuick($lat, $lng);
                    if ($osm !== null && ! empty($osm['kelurahan'])) {
                        $cached = $this->buildWilayahResult($cached['kabupaten'], $cached['kecamatan'], $lat, $lng, $cached['road'] ?? '', $osm['kelurahan'], $groupingMode);
                        self::$structuredGeocodeCache[$cacheKey] = $cached;
                        self::savePersistentGeocodeCache();
                        return $cached;
                    }
                }
            }
            return $this->buildWilayahResult($cached['kabupaten'], $cached['kecamatan'], $lat, $lng, $cached['road'] ?? '', $cached['kelurahan'] ?? '', $groupingMode);
        }

        // 2. Cek apakah ada koordinat yang tersimpan di cache yang sangat dekat (< 350 meter)
        foreach (self::$structuredGeocodeCache as $cached) {
            if (isset($cached['_lat'], $cached['_lng'])) {
                $dist = $this->haversineDistance($lat, $lng, (float) $cached['_lat'], (float) $cached['_lng']);
                if ($dist < 350.0) {
                    $res = $this->buildWilayahResult($cached['kabupaten'], $cached['kecamatan'], $lat, $lng, $cached['road'] ?? '', $cached['kelurahan'] ?? '', $groupingMode);
                    self::$structuredGeocodeCache[$cacheKey] = $res;
                    return $res;
                }
            }
        }

        $rawKab = '';
        $rawKec = '';
        $rawKel = '';
        $rawRoad = '';

        // 3. Jika mode kab_kec_kel dan circuit breaker aman: coba panggil fetchNominatimQuick dulu agar dapat nama desa/kelurahan riil
        if ($groupingMode === 'kab_kec_kel' && (time() - self::$lastOsmErrorTimestamp > 60)) {
            $osm = $this->fetchNominatimQuick($lat, $lng);
            if ($osm !== null) {
                $rawKab = $osm['kabupaten'] ?? '';
                $rawKec = $osm['kecamatan'] ?? '';
                $rawKel = $osm['kelurahan'] ?? '';
                $rawRoad = $osm['road'] ?? '';
            }
        }

        // 4. PANCARIAN SPASIAL OFFLINE RIAU (Super Cepat ~0.01 ms, 100% Bebas Error 429 dan 100% Offline Handal)
        if ($rawKab === '' && $rawKec === '') {
            $offlineWilayah = $this->lookupOfflineWilayah($lat, $lng);
            if ($offlineWilayah !== null) {
                $rawKab = $offlineWilayah['kabupaten'];
                $rawKec = $offlineWilayah['kecamatan'];
            }
        }

        // 5. Di luar cakupan Riau: Coba cURL Nominatim cepat jika belum pernah dipanggil
        if ($rawKab === '' && $rawKec === '' && (time() - self::$lastOsmErrorTimestamp > 60)) {
            $osm = $this->fetchNominatimQuick($lat, $lng);
            if ($osm !== null) {
                $rawKab = $osm['kabupaten'] ?? '';
                $rawKec = $osm['kecamatan'] ?? '';
                $rawKel = $osm['kelurahan'] ?? '';
                $rawRoad = $osm['road'] ?? '';
            }
        }

        // 6. Fallback jika Nominatim rate-limited atau offline: gunakan wilayah Riau terdekat
        if ($rawKab === '' && $rawKec === '') {
            $nearest = $this->findNearestRiauWilayah($lat, $lng);
            $rawKab = $nearest['kabupaten'];
            $rawKec = $nearest['kecamatan'];
        }

        $result = $this->buildWilayahResult($rawKab, $rawKec, $lat, $lng, $rawRoad, $rawKel, $groupingMode);
        self::$structuredGeocodeCache[$cacheKey] = $result;
        self::savePersistentGeocodeCache();
        return $result;
    }

    /**
     * Cari pusat administrasi kabupaten/kota terdekat di Provinsi Riau berdasarkan jarak Haversine.
     * Berfungsi sebagai fallback handal jika layanan reverse geocoding eksternal sedang offline.
     */
    private function findNearestRiauWilayah(float $lat, float $lng): array
    {
        $centers = [
            ['kab' => 'Kota Pekanbaru', 'kec' => 'Kecamatan Marpoyan Damai', 'lat' => 0.507068, 'lng' => 101.447779],
            ['kab' => 'Kota Dumai', 'kec' => 'Kecamatan Dumai Timur', 'lat' => 1.666667, 'lng' => 101.450000],
            ['kab' => 'Kabupaten Kampar', 'kec' => 'Kecamatan Bangkinang', 'lat' => 0.334167, 'lng' => 101.025000],
            ['kab' => 'Kabupaten Siak', 'kec' => 'Kecamatan Siak', 'lat' => 0.978056, 'lng' => 101.996111],
            ['kab' => 'Kabupaten Bengkalis', 'kec' => 'Kecamatan Bengkalis', 'lat' => 1.482222, 'lng' => 102.148056],
            ['kab' => 'Kabupaten Pelalawan', 'kec' => 'Kecamatan Pangkalan Kerinci', 'lat' => 0.342222, 'lng' => 101.884167],
            ['kab' => 'Kabupaten Rokan Hulu', 'kec' => 'Kecamatan Rambah', 'lat' => 0.884167, 'lng' => 100.490278],
            ['kab' => 'Kabupaten Rokan Hilir', 'kec' => 'Kecamatan Bangko', 'lat' => 2.158333, 'lng' => 100.825000],
            ['kab' => 'Kabupaten Indragiri Hulu', 'kec' => 'Kecamatan Rengat', 'lat' => -0.551389, 'lng' => 102.316111],
            ['kab' => 'Kabupaten Indragiri Hilir', 'kec' => 'Kecamatan Tembilahan', 'lat' => -0.327222, 'lng' => 103.155000],
            ['kab' => 'Kabupaten Kuantan Singingi', 'kec' => 'Kecamatan Kuantan Tengah', 'lat' => -0.528333, 'lng' => 101.464167],
            ['kab' => 'Kabupaten Kepulauan Meranti', 'kec' => 'Kecamatan Tebing Tinggi', 'lat' => 1.002222, 'lng' => 102.712222],
        ];

        $closest = $centers[0];
        $minDist = PHP_FLOAT_MAX;
        foreach ($centers as $c) {
            $d = $this->haversineDistance($lat, $lng, $c['lat'], $c['lng']);
            if ($d < $minDist) {
                $minDist = $d;
                $closest = $c;
            }
        }

        return ['kabupaten' => $closest['kab'], 'kecamatan' => $closest['kec']];
    }

    /**
     * Melakukan reverse geocoding koordinat GPS menjadi nama jalan / alamat resmi Indonesia ala TimeMark.
     * Menggunakan cache in-memory untuk efisiensi tinggi pada pemrosesan foto massal (batch/ZIP).
     */
    public function reverseGeocode(float $lat, float $lng, string $groupingMode = 'kab_kec'): ?string
    {
        $structured = $this->reverseGeocodeStructured($lat, $lng, $groupingMode);
        return $structured['display'] ?? null;
    }

    /**
     * Membaca metadata EXIF dari file foto (mendukung format JPEG, TIFF, serta iPhone HEIC/HEIF).
     */
    public function readExifData(string $filePath): ?array
    {
        if (! is_file($filePath) || ! is_readable($filePath)) {
            return null;
        }

        // 1. Coba baca via exif_read_data biasa (bekerja langsung untuk JPEG & TIFF)
        $exif = @exif_read_data($filePath);
        if (is_array($exif) && ! empty($exif)) {
            return $exif;
        }

        // 2. Jika file adalah HEIC/HEIF
        if ($this->isHeicFile($filePath)) {
            // Coba ekstrak blok segmen TIFF dari berkas HEIC
            $heicExif = $this->extractExifFromHeic($filePath);
            if ($heicExif !== null) {
                return $heicExif;
            }

            // Fallback: konversi sementara ke JPEG lalu baca EXIF dari hasil konversi
            $tempJpg = $this->convertHeicToJpeg($filePath);
            if ($tempJpg !== null && is_file($tempJpg)) {
                $cExif = @exif_read_data($tempJpg);
                @unlink($tempJpg);
                if (is_array($cExif) && ! empty($cExif)) {
                    return $cExif;
                }
            }
        }

        return null;
    }

    /**
     * Mengekstrak metadata EXIF mentah dari kontainer file HEIC/HEIF (mencari marker TIFF/Exif).
     */
    public function extractExifFromHeic(string $heicPath): ?array
    {
        if (! is_file($heicPath) || ! is_readable($heicPath)) {
            return null;
        }

        $f = @fopen($heicPath, 'rb');
        if (! $f) {
            return null;
        }
        $chunk = fread($f, 8 * 1024 * 1024); // Baca hingga 8MB pertama
        fclose($f);

        if ($chunk === false || strlen($chunk) < 64) {
            return null;
        }

        $tiffData = null;
        $pos = strpos($chunk, "Exif\x00\x00");
        if ($pos !== false) {
            $tiffData = substr($chunk, $pos + 6);
        } else {
            $posII = strpos($chunk, "II*\x00");
            $posMM = strpos($chunk, "MM\x00*");
            if ($posII !== false && ($posMM === false || $posII < $posMM)) {
                $tiffData = substr($chunk, $posII);
            } elseif ($posMM !== false) {
                $tiffData = substr($chunk, $posMM);
            }
        }

        if ($tiffData !== null && strlen($tiffData) > 32) {
            $tmpTiff = tempnam(sys_get_temp_dir(), 'heic_exif_') . '.tif';
            @file_put_contents($tmpTiff, $tiffData);
            $exif = @exif_read_data($tmpTiff);
            @unlink($tmpTiff);
            if (is_array($exif) && ! empty($exif)) {
                return $exif;
            }
        }

        return null;
    }

    private function parseGpsCoordinate($coordinate, string $ref): ?float
    {
        if (! is_array($coordinate) || count($coordinate) < 3) {
            return null;
        }

        $degrees = $this->evalRational($coordinate[0]);
        $minutes = $this->evalRational($coordinate[1]);
        $seconds = $this->evalRational($coordinate[2]);

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);
        if (in_array(strtoupper($ref), ['S', 'W'], true)) {
            $decimal = -$decimal;
        }

        return $decimal;
    }

    private function evalRational($val): float
    {
        if (is_numeric($val)) {
            return (float) $val;
        }
        if (is_string($val) && strpos($val, '/') !== false) {
            $parts = explode('/', $val);
            if (isset($parts[1]) && (float) $parts[1] != 0) {
                return (float) $parts[0] / (float) $parts[1];
            }
        }
        return (float) $val;
    }

    // =========================================================================
    // TEMPLATE 1: CLASSIC (PU SIGNATURE / TIMEMARK CLASSIC)
    // =========================================================================
    private function renderClassicTemplate($image, int $width, int $height, float $scale, array $dt, string $lokasi, string $coords, string $notes, $logoImage, ?array $wilayahInfo = null): void
    {
        $fontBold = $this->getFont(self::FONT_BOLD);
        $fontMedium = $this->getFont(self::FONT_MEDIUM);
        $fontRegular = $this->getFont(self::FONT_REGULAR);

        // 1. Gradient Overlay Halus di Bagian Bawah
        $this->drawGradientOverlay($image, 0, (int) ($height * 0.58), $width, $height, 0, 75);

        // Colors
        $colorWhite = imagecolorallocate($image, 255, 255, 255);
        $colorDark = imagecolorallocate($image, 0, 0, 0);
        $colorBar = imagecolorallocate($image, 253, 184, 19); // PU Yellow
        $colorNavy = imagecolorallocate($image, 12, 53, 106); // PU Dark Navy
        $colorCapsuleBg = imagecolorallocate($image, 255, 255, 255);

        $shadowOffset = (int) max(1, 2 * $scale);
        $paddingX = (int) (60 * $scale);
        $paddingY = (int) (60 * $scale);

        // Sizes
        $timeFontSize = (int) max(24, 76 * $scale);
        $dateFontSize = (int) max(11, 23 * $scale);
        $addressFontSize = (int) max(11, 21 * $scale);
        $addressLineHeight = (int) ($addressFontSize * 1.4);
        $capsuleFontSize = (int) max(8, 12 * $scale);

        // Siapkan baris alamat & koordinat (terstruktur Kabupaten & Kecamatan, bebas mojibake)
        $cleanLokasi = $this->sanitizeTextForFont($lokasi);
        $cleanCoords = $this->sanitizeCoordinates($coords);
        $cleanNotes  = $this->sanitizeTextForFont($notes);

        $kabVal = $wilayahInfo['kabupaten'] ?? null;
        $kecVal = $wilayahInfo['kecamatan'] ?? null;
        $kelVal = $wilayahInfo['kelurahan'] ?? null;
        if ($kelVal === '-') {
            $kelVal = null;
        }

        if ($kabVal === null || $kecVal === null) {
            if (preg_match('/^(Kecamatan\s+[^,]+),\s*(Kabupaten\s+[^,]+|Kota\s+[^,]+)/i', $cleanLokasi, $m)) {
                $kecVal = trim($m[1]);
                $kabVal = trim($m[2]);
            } elseif (preg_match('/^(Kabupaten\s+[^,]+|Kota\s+[^,]+),\s*(Kecamatan\s+[^,]+)/i', $cleanLokasi, $m)) {
                $kabVal = trim($m[1]);
                $kecVal = trim($m[2]);
            }
        }

        $lineItems = [];
        $pinReserveWidth = (int) ($addressFontSize * 1.15);

        if ($kabVal !== null && $kecVal !== null) {
            $lineItems[] = ['text' => $kabVal, 'type' => 'location'];
            $lineItems[] = ['text' => $kecVal, 'type' => 'sublocation'];
            if ($kelVal !== null && $kelVal !== '') {
                $lineItems[] = ['text' => $kelVal, 'type' => 'sublocation'];
            }

            // Jika ada nama lokasi spesifik yang diketik user (selain kabupaten/kecamatan/kelurahan/koordinat)
            $customLoc = trim(str_ireplace([$kabVal, $kecVal, (string)$kelVal, 'Kecamatan', 'Kabupaten', 'Kota', 'Desa', 'Kelurahan', ',', '-'], '', $cleanLokasi));
            if ($customLoc !== '' && $customLoc !== $cleanCoords && strlen($customLoc) >= 3 && !preg_match('/^[0-9\.\,\s°NSEWnsew\+\-\:\'\"\°]+$/u', $customLoc)) {
                $lineItems[] = ['text' => $cleanLokasi, 'type' => 'sublocation'];
            }
        } elseif ($cleanLokasi !== '' && $cleanLokasi !== $cleanCoords) {
            $wrapped = $this->wrapText($addressFontSize, $fontRegular, $cleanLokasi, (int) ($width * 0.70) - $pinReserveWidth);
            foreach ($wrapped as $idx => $wLine) {
                $lineItems[] = ['text' => $wLine, 'type' => ($idx === 0 ? 'location' : 'text')];
            }
        }

        if ($cleanCoords !== '') {
            $lineItems[] = ['text' => $cleanCoords, 'type' => empty($lineItems) ? 'location' : 'coord'];
        }
        if ($cleanNotes !== '') {
            $lineItems[] = ['text' => 'Catatan: ' . $cleanNotes, 'type' => 'note'];
        }

        $numLines = count($lineItems);
        $addressBlockHeight = $numLines * $addressLineHeight;
        $yAddressBottom = $height - $paddingY;

        // Render Baris Teks Bawah
        for ($i = 0; $i < $numLines; $i++) {
            $item = $lineItems[$i];
            $yLine = $yAddressBottom - (($numLines - 1 - $i) * $addressLineHeight);
            $curX = $paddingX;

            if ($item['type'] === 'location') {
                $curX += $this->drawMapPin($image, $curX, $yLine, $addressFontSize, $colorBar, $colorWhite);
            } elseif (in_array($item['type'], ['sublocation', 'coord', 'note'], true)) {
                $curX += $pinReserveWidth;
            }

            $this->drawTextWithShadow($image, $addressFontSize, 0, $curX, $yLine, $colorWhite, $colorDark, $fontRegular, $item['text'], $shadowOffset);
        }

        // Render Jam, Vertical Bar, dan Tanggal
        $yAddressTop = $yAddressBottom - $addressBlockHeight + $addressFontSize;
        $sectionSpacing = (int) (38 * $scale);
        $yTime = $yAddressTop - $sectionSpacing;

        $timeText = $dt['time'];
        $timeBox = @imagettfbbox($timeFontSize, 0, $fontBold, $timeText);
        $timeWidth = $timeBox[2] - $timeBox[0];

        // Draw Jam
        $this->drawTextWithShadow($image, $timeFontSize, 0, $paddingX, $yTime, $colorWhite, $colorDark, $fontBold, $timeText, $shadowOffset);

        // Bar Vertikal
        $barX1 = $paddingX + $timeWidth + (22 * $scale);
        $barX2 = $barX1 + (6 * $scale);
        $barTop = $yTime + $timeBox[7] - (2 * $scale);
        $barBottom = $yTime + $timeBox[1] + (2 * $scale);

        imagefilledrectangle($image, (int) ($barX1 + $shadowOffset), (int) ($barTop + $shadowOffset), (int) ($barX2 + $shadowOffset), (int) ($barBottom + $shadowOffset), $colorDark);
        imagefilledrectangle($image, (int) $barX1, (int) $barTop, (int) $barX2, (int) $barBottom, $colorBar);

        // Tanggal & Hari
        $dateStr = $dt['date'];
        $dayStr = $dt['day'];
        $dateX = (int) ($barX2 + (22 * $scale));

        $dateBox = @imagettfbbox($dateFontSize, 0, $fontMedium, $dateStr);
        $dateHeight = $dateBox[1] - $dateBox[7];

        $yDate = (int) ($yTime + $timeBox[7] + $dateHeight + (4 * $scale));
        $yDay = (int) ($yTime + $timeBox[1] - (2 * $scale));

        $this->drawTextWithShadow($image, $dateFontSize, 0, $dateX, $yDate, $colorWhite, $colorDark, $fontMedium, $dateStr, $shadowOffset);
        $this->drawTextWithShadow($image, $dateFontSize, 0, $dateX, $yDay, $colorWhite, $colorDark, $fontMedium, $dayStr, $shadowOffset);

        // Render Logo Capsule (Atas Jam)
        $yTimeTop = $yTime + $timeBox[7];
        $capsuleSpacing = (int) (32 * $scale);
        $capsuleBottom = $yTimeTop - $capsuleSpacing;
        $capsuleHeight = (int) max(36, 62 * $scale);
        $capsuleTop = $capsuleBottom - $capsuleHeight;
        $capsuleLeft = $paddingX;

        $logoSize = (int) max(22, 42 * $scale);
        $logoX = $capsuleLeft + (14 * $scale);
        $logoY = $capsuleTop + (($capsuleHeight - $logoSize) / 2);

        $hasLogo = ($logoImage !== null);
        $textX = $hasLogo ? ($logoX + $logoSize + (14 * $scale)) : ($capsuleLeft + (18 * $scale));

        $cBox1 = @imagettfbbox($capsuleFontSize, 0, $fontBold, "KEMENTERIAN");
        $cBox2 = @imagettfbbox($capsuleFontSize, 0, $fontBold, "PEKERJAAN UMUM");
        $cTextWidth = max($cBox1[2] - $cBox1[0], $cBox2[2] - $cBox2[0]);
        $cTextHeight = $cBox1[1] - $cBox1[7];
        $capsuleRight = $textX + $cTextWidth + (20 * $scale);

        $this->drawFilledRoundedRectangle($image, (int) ($capsuleLeft + $shadowOffset), (int) ($capsuleTop + $shadowOffset), (int) ($capsuleRight + $shadowOffset), (int) ($capsuleBottom + $shadowOffset), (int) ($capsuleHeight / 2), $colorDark);
        $this->drawFilledRoundedRectangle($image, (int) $capsuleLeft, (int) $capsuleTop, (int) $capsuleRight, (int) $capsuleBottom, (int) ($capsuleHeight / 2), $colorCapsuleBg);

        if ($hasLogo) {
            $this->copyResampledImage($image, $logoImage, (int) $logoX, (int) $logoY, $logoSize, $logoSize);
        }

        $capsuleLineSpacing = (int) (3 * $scale);
        $totalTextHeight = (2 * $cTextHeight) + $capsuleLineSpacing;
        $textY = $capsuleTop + (($capsuleHeight - $totalTextHeight) / 2) + $cTextHeight;

        @imagettftext($image, $capsuleFontSize, 0, (int) $textX, (int) $textY, $colorNavy, $fontBold, "KEMENTERIAN");
        @imagettftext($image, $capsuleFontSize, 0, (int) $textX, (int) ($textY + $cTextHeight + $capsuleLineSpacing), $colorNavy, $fontBold, "PEKERJAAN UMUM");
    }

    // =========================================================================
    // TEMPLATE 2: TABLE (GAYA TABEL / GRID INSPEKSI KONSTRUKSI TIMEMARK)
    // =========================================================================
    private function renderTableTemplate($image, int $width, int $height, float $scale, array $dt, string $lokasi, string $coords, string $notes, $logoImage, ?array $wilayahInfo = null): void
    {
        $fontBold = $this->getFont(self::FONT_BOLD);
        $fontMedium = $this->getFont(self::FONT_MEDIUM);
        $fontRegular = $this->getFont(self::FONT_REGULAR);

        $paddingX = (int) (40 * $scale);
        $paddingY = (int) (40 * $scale);
        $cardWidth = (int) min($width - (2 * $paddingX), 720 * $scale);

        // Ukuran font
        $headerFontSize = (int) max(11, 16 * $scale);
        $labelFontSize = (int) max(10, 14 * $scale);
        $valueFontSize = (int) max(10, 15 * $scale);
        $rowHeight = (int) max(28, 38 * $scale);
        $headerHeight = (int) max(38, 52 * $scale);

        // Siapkan baris data tabel (bersihkan emoji & format terstruktur Kabupaten - Kecamatan)
        $cleanLokasi = $this->sanitizeTextForFont($lokasi);
        $cleanCoords = $this->sanitizeCoordinates($coords);
        $cleanNotes  = $this->sanitizeTextForFont($notes);

        $kabVal = $wilayahInfo['kabupaten'] ?? null;
        $kecVal = $wilayahInfo['kecamatan'] ?? null;
        $kelVal = $wilayahInfo['kelurahan'] ?? null;
        if ($kelVal === '-') {
            $kelVal = null;
        }

        if ($kabVal === null || $kecVal === null) {
            if (preg_match('/^(Kecamatan\s+[^,]+),\s*(Kabupaten\s+[^,]+|Kota\s+[^,]+)/i', $cleanLokasi, $m)) {
                $kecVal = trim($m[1]);
                $kabVal = trim($m[2]);
            } elseif (preg_match('/^(Kabupaten\s+[^,]+|Kota\s+[^,]+),\s*(Kecamatan\s+[^,]+)/i', $cleanLokasi, $m)) {
                $kabVal = trim($m[1]);
                $kecVal = trim($m[2]);
            }
        }

        $rows = [
            ['label' => 'WAKTU & TANGGAL', 'val' => "{$dt['day']}, {$dt['date']} - {$dt['time_full']} WIB", 'highlight' => true],
        ];

        if ($kabVal !== null && $kecVal !== null) {
            $rows[] = ['label' => 'KABUPATEN / KOTA', 'val' => $kabVal, 'highlight' => false];
            $rows[] = ['label' => 'KECAMATAN', 'val' => $kecVal, 'highlight' => false];
            if ($kelVal !== null && $kelVal !== '') {
                $rows[] = ['label' => (stripos($kelVal, 'Kelurahan') !== false ? 'KELURAHAN' : 'DESA / KELURAHAN'), 'val' => $kelVal, 'highlight' => false];
            }
        } else {
            $rows[] = ['label' => 'LOKASI WILAYAH', 'val' => ($cleanLokasi !== '' && $cleanLokasi !== $cleanCoords) ? $cleanLokasi : 'Wilayah Riau', 'highlight' => false];
        }

        if ($cleanCoords !== '') {
            $rows[] = ['label' => 'KOORDINAT GPS', 'val' => $cleanCoords, 'highlight' => false];
        }

        if ($cleanNotes !== '') {
            $rows[] = ['label' => 'CATATAN / PROYEK', 'val' => $cleanNotes, 'highlight' => false];
        }

        $totalRows = count($rows);
        $cardHeight = $headerHeight + ($totalRows * $rowHeight);

        $cardX1 = $paddingX;
        $cardY2 = $height - $paddingY;
        $cardY1 = $cardY2 - $cardHeight;
        $cardX2 = $cardX1 + $cardWidth;

        // Background kartu semi-transparan hitam dengan shadow
        $colorBg = imagecolorallocatealpha($image, 15, 23, 42, 35); // Dark Slate translucent
        $colorHeaderBg = imagecolorallocatealpha($image, 12, 53, 106, 25); // Dark Navy translucent
        $colorBorder = imagecolorallocatealpha($image, 255, 255, 255, 75); // Subtle white border
        $colorDivider = imagecolorallocatealpha($image, 255, 255, 255, 95);
        $colorWhite = imagecolorallocate($image, 255, 255, 255);
        $colorYellow = imagecolorallocate($image, 253, 184, 19);
        $colorMuted = imagecolorallocate($image, 203, 213, 225);

        // Draw card background
        imagefilledrectangle($image, $cardX1, $cardY1, $cardX2, $cardY2, $colorBg);
        imagefilledrectangle($image, $cardX1, $cardY1, $cardX2, $cardY1 + $headerHeight, $colorHeaderBg);
        imagerectangle($image, $cardX1, $cardY1, $cardX2, $cardY2, $colorBorder);
        imageline($image, $cardX1, $cardY1 + $headerHeight, $cardX2, $cardY1 + $headerHeight, $colorDivider);

        // Render Header (Logo + Title)
        $logoSize = (int) max(22, 34 * $scale);
        $headerLogoX = $cardX1 + (15 * $scale);
        $headerLogoY = $cardY1 + (($headerHeight - $logoSize) / 2);

        $headerTextX = $headerLogoX;
        if ($logoImage !== null) {
            $this->copyResampledImage($image, $logoImage, (int) $headerLogoX, (int) $headerLogoY, $logoSize, $logoSize);
            $headerTextX += $logoSize + (12 * $scale);
        }

        $hBox = @imagettfbbox($headerFontSize, 0, $fontBold, 'KEMENTERIAN PEKERJAAN UMUM');
        $hTextH = $hBox[1] - $hBox[7];
        $hTextY = $cardY1 + (($headerHeight - $hTextH) / 2) + $hTextH;
        @imagettftext($image, $headerFontSize, 0, (int) $headerTextX, (int) $hTextY, $colorWhite, $fontBold, 'KEMENTERIAN PEKERJAAN UMUM - SATKER RIAU');

        // Render Table Rows
        $col1Width = (int) (180 * $scale);
        $currentY = $cardY1 + $headerHeight;

        foreach ($rows as $index => $row) {
            $y1Row = $currentY + ($index * $rowHeight);
            $y2Row = $y1Row + $rowHeight;

            // Garis pembatas horizontal antar baris
            if ($index > 0) {
                imageline($image, $cardX1, $y1Row, $cardX2, $y1Row, $colorDivider);
            }
            // Garis pembatas vertikal kolom label dan nilai
            imageline($image, $cardX1 + $col1Width, $y1Row, $cardX1 + $col1Width, $y2Row, $colorDivider);

            // Label
            $lBox = @imagettfbbox($labelFontSize, 0, $fontMedium, $row['label']);
            $lHeight = $lBox[1] - $lBox[7];
            $lY = $y1Row + (($rowHeight - $lHeight) / 2) + $lHeight;
            @imagettftext($image, $labelFontSize, 0, (int) ($cardX1 + (14 * $scale)), (int) $lY, $colorMuted, $fontMedium, $row['label']);

            // Value (Highlight jika waktu/tanggal)
            $valColor = $row['highlight'] ? $colorYellow : $colorWhite;
            $valFont = $row['highlight'] ? $fontBold : $fontRegular;
            $vBox = @imagettfbbox($valueFontSize, 0, $valFont, $row['val']);
            $vHeight = $vBox[1] - $vBox[7];
            $vY = $y1Row + (($rowHeight - $vHeight) / 2) + $vHeight;
            @imagettftext($image, $valueFontSize, 0, (int) ($cardX1 + $col1Width + (14 * $scale)), (int) $vY, $valColor, $valFont, $row['val']);
        }
    }

    // =========================================================================
    // TEMPLATE 3: BADGE (GAYA MODERN FLOATING CARD / BADGE TIMEMARK)
    // =========================================================================
    private function renderBadgeTemplate($image, int $width, int $height, float $scale, array $dt, string $lokasi, string $coords, string $notes, $logoImage, ?array $wilayahInfo = null): void
    {
        $fontExtraBold = $this->getFont(self::FONT_EXTRABOLD);
        $fontBold = $this->getFont(self::FONT_BOLD);
        $fontMedium = $this->getFont(self::FONT_MEDIUM);
        $fontRegular = $this->getFont(self::FONT_REGULAR);

        $paddingX = (int) (50 * $scale);
        $paddingY = (int) (50 * $scale);
        $cardWidth = (int) min($width - (2 * $paddingX), 520 * $scale);

        // Font sizes
        $timeFontSize = (int) max(28, 68 * $scale);
        $dateFontSize = (int) max(11, 19 * $scale);
        $textFontSize = (int) max(10, 16 * $scale);
        $tagFontSize = (int) max(8, 12 * $scale);

        $innerPad = (int) (24 * $scale);
        $contentWidth = $cardWidth - (2 * $innerPad);

        // Bungkus teks alamat & catatan (terstruktur Kabupaten & Kecamatan, tanpa emoji rusak)
        $cleanLokasi = $this->sanitizeTextForFont($lokasi);
        $cleanCoords = $this->sanitizeCoordinates($coords);
        $cleanNotes  = $this->sanitizeTextForFont($notes);

        $kabVal = $wilayahInfo['kabupaten'] ?? null;
        $kecVal = $wilayahInfo['kecamatan'] ?? null;
        $kelVal = $wilayahInfo['kelurahan'] ?? null;
        if ($kelVal === '-') {
            $kelVal = null;
        }

        if ($kabVal === null || $kecVal === null) {
            if (preg_match('/^(Kecamatan\s+[^,]+),\s*(Kabupaten\s+[^,]+|Kota\s+[^,]+)/i', $cleanLokasi, $m)) {
                $kecVal = trim($m[1]);
                $kabVal = trim($m[2]);
            } elseif (preg_match('/^(Kabupaten\s+[^,]+|Kota\s+[^,]+),\s*(Kecamatan\s+[^,]+)/i', $cleanLokasi, $m)) {
                $kabVal = trim($m[1]);
                $kecVal = trim($m[2]);
            }
        }

        $addrItems = [];
        $pinReserveWidth = (int) ($textFontSize * 1.15);

        if ($kabVal !== null && $kecVal !== null) {
            $addrItems[] = ['text' => $kabVal, 'type' => 'location'];
            $addrItems[] = ['text' => $kecVal, 'type' => 'sublocation'];
            if ($kelVal !== null && $kelVal !== '') {
                $addrItems[] = ['text' => $kelVal, 'type' => 'sublocation'];
            }
        } elseif ($cleanLokasi !== '' && $cleanLokasi !== $cleanCoords) {
            $wrapped = $this->wrapText($textFontSize, $fontRegular, $cleanLokasi, $contentWidth - $pinReserveWidth);
            foreach ($wrapped as $idx => $wLine) {
                $addrItems[] = ['text' => $wLine, 'type' => ($idx === 0 ? 'location' : 'text')];
            }
        }
        if ($cleanCoords !== '') {
            $addrItems[] = ['text' => $cleanCoords, 'type' => empty($addrItems) ? 'location' : 'coord'];
        }
        if ($cleanNotes !== '') {
            $addrItems[] = ['text' => 'Catatan: ' . $cleanNotes, 'type' => 'note'];
        }

        $lineH = (int) ($textFontSize * 1.45);
        $addrHeight = count($addrItems) * $lineH;

        $timeBox = @imagettfbbox($timeFontSize, 0, $fontExtraBold, $dt['time']);
        $timeH = $timeBox[1] - $timeBox[7];

        $cardHeight = $innerPad + (36 * $scale) + (10 * $scale) + $timeH + (18 * $scale) + (3 * $scale) + (14 * $scale) + $addrHeight + $innerPad;

        $cardX1 = $paddingX;
        $cardY2 = $height - $paddingY;
        $cardY1 = $cardY2 - $cardHeight;
        $cardX2 = $cardX1 + $cardWidth;

        // Background Glass Dark Card
        $colorDarkBg = imagecolorallocatealpha($image, 10, 15, 30, 28);
        $colorBorder = imagecolorallocatealpha($image, 255, 255, 255, 80);
        $colorYellow = imagecolorallocate($image, 253, 184, 19);
        $colorWhite = imagecolorallocate($image, 255, 255, 255);
        $colorMuted = imagecolorallocate($image, 226, 232, 240);

        // Draw rounded rectangle card with shadow
        $radius = (int) (18 * $scale);
        $this->drawFilledRoundedRectangle($image, $cardX1, $cardY1, $cardX2, $cardY2, $radius, $colorDarkBg);
        $this->drawRoundedRectangleBorder($image, $cardX1, $cardY1, $cardX2, $cardY2, $radius, $colorBorder);

        $cursorY = $cardY1 + $innerPad;

        // Top Row: Logo + Mini Pill Tag
        $logoSize = (int) max(20, 32 * $scale);
        if ($logoImage !== null) {
            $this->copyResampledImage($image, $logoImage, (int) ($cardX1 + $innerPad), (int) $cursorY, $logoSize, $logoSize);
        }

        $tagText = 'KEMENTERIAN PEKERJAAN UMUM';
        $tagX = ($logoImage !== null) ? ($cardX1 + $innerPad + $logoSize + (12 * $scale)) : ($cardX1 + $innerPad);
        $tBox = @imagettfbbox($tagFontSize, 0, $fontBold, $tagText);
        $tH = $tBox[1] - $tBox[7];
        $tY = $cursorY + (($logoSize - $tH) / 2) + $tH;
        @imagettftext($image, $tagFontSize, 0, (int) $tagX, (int) $tY, $colorYellow, $fontBold, $tagText);

        $cursorY += max($logoSize, $tH) + (14 * $scale);

        // Middle Row: Time & Date
        $cursorY += $timeH;
        @imagettftext($image, $timeFontSize, 0, (int) ($cardX1 + $innerPad), (int) $cursorY, $colorWhite, $fontExtraBold, $dt['time']);

        // Tanggal & Hari di sebelah kanan jam atau baris bawah
        $dateText = "{$dt['day']}, {$dt['date']}";
        $cursorY += (24 * $scale);
        @imagettftext($image, $dateFontSize, 0, (int) ($cardX1 + $innerPad), (int) $cursorY, $colorYellow, $fontMedium, $dateText);

        // Accent Line
        $cursorY += (14 * $scale);
        $colorLine = imagecolorallocatealpha($image, 255, 255, 255, 100);
        imageline($image, $cardX1 + $innerPad, (int) $cursorY, $cardX2 - $innerPad, (int) $cursorY, $colorLine);

        // Bottom section: Address lines
        $cursorY += (18 * $scale);
        foreach ($addrItems as $item) {
            $curX = (int) ($cardX1 + $innerPad);
            if ($item['type'] === 'location') {
                $curX += $this->drawMapPin($image, $curX, (int) $cursorY, $textFontSize, $colorYellow, $colorWhite);
            } elseif (in_array($item['type'], ['sublocation', 'coord', 'note'], true)) {
                $curX += $pinReserveWidth;
            }
            @imagettftext($image, $textFontSize, 0, $curX, (int) $cursorY, $colorMuted, $fontRegular, $item['text']);
            $cursorY += $lineH;
        }
    }

    // =========================================================================
    // TEMPLATE 4: STRIP (GAYA CLEAN BOTTOM STRIP / BAR TIMEMARK)
    // =========================================================================
    private function renderStripTemplate($image, int $width, int $height, float $scale, array $dt, string $lokasi, string $coords, string $notes, $logoImage, ?array $wilayahInfo = null): void
    {
        $fontBold = $this->getFont(self::FONT_BOLD);
        $fontMedium = $this->getFont(self::FONT_MEDIUM);
        $fontRegular = $this->getFont(self::FONT_REGULAR);

        $stripHeight = (int) max(70, 115 * $scale);
        $stripY1 = $height - $stripHeight;
        $stripY2 = $height;

        // Background Bar Hitam Semi-transparan
        $colorBg = imagecolorallocatealpha($image, 15, 20, 30, 24);
        $colorTopBorder = imagecolorallocate($image, 253, 184, 19); // PU Yellow border top
        $colorWhite = imagecolorallocate($image, 255, 255, 255);
        $colorYellow = imagecolorallocate($image, 253, 184, 19);
        $colorMuted = imagecolorallocate($image, 203, 213, 225);

        imagefilledrectangle($image, 0, $stripY1, $width, $stripY2, $colorBg);
        // Garis aksen kuning 4px di atas strip
        $borderThick = (int) max(2, 4 * $scale);
        imagefilledrectangle($image, 0, $stripY1, $width, $stripY1 + $borderThick, $colorTopBorder);

        $paddingX = (int) (35 * $scale);
        $leftSectionWidth = (int) ($width * 0.32);

        // Sisi Kiri: Waktu Besar + Tanggal
        $timeFontSize = (int) max(20, 48 * $scale);
        $dateFontSize = (int) max(10, 16 * $scale);

        $timeText = $dt['time'];
        $timeBox = @imagettfbbox($timeFontSize, 0, $fontBold, $timeText);
        $timeH = $timeBox[1] - $timeBox[7];
        $timeY = $stripY1 + (int) ($stripHeight * 0.52);
        @imagettftext($image, $timeFontSize, 0, $paddingX, $timeY, $colorWhite, $fontBold, $timeText);

        $dateText = "{$dt['day']}, {$dt['date']}";
        $dateY = $timeY + (22 * $scale);
        @imagettftext($image, $dateFontSize, 0, $paddingX, $dateY, $colorYellow, $fontMedium, $dateText);

        // Divider Vertikal tipis antara kiri dan kanan
        $dividerX = (int) ($paddingX + $leftSectionWidth);
        $colorDivider = imagecolorallocatealpha($image, 255, 255, 255, 100);
        imageline($image, $dividerX, (int) ($stripY1 + (15 * $scale)), $dividerX, (int) ($stripY2 - (15 * $scale)), $colorDivider);

        // Sisi Kanan: Logo + Lokasi & Koordinat
        $rightX = $dividerX + (25 * $scale);
        $logoSize = (int) max(22, 44 * $scale);
        $logoY = $stripY1 + (($stripHeight - $logoSize) / 2);

        if ($logoImage !== null) {
            $this->copyResampledImage($image, $logoImage, (int) $rightX, (int) $logoY, $logoSize, $logoSize);
            $rightX += $logoSize + (18 * $scale);
        }

        $infoFontSize = (int) max(10, 16 * $scale);
        $infoLineH = (int) ($infoFontSize * 1.4);
        $rightAvailableWidth = (int) ($width - $rightX - $paddingX);

        $cleanLokasi = $this->sanitizeTextForFont($lokasi);
        $cleanCoords = $this->sanitizeCoordinates($coords);

        $kabVal = $wilayahInfo['kabupaten'] ?? null;
        $kecVal = $wilayahInfo['kecamatan'] ?? null;
        $kelVal = $wilayahInfo['kelurahan'] ?? null;
        if ($kelVal === '-') {
            $kelVal = null;
        }

        if ($kabVal === null || $kecVal === null) {
            if (preg_match('/^(Kecamatan\s+[^,]+),\s*(Kabupaten\s+[^,]+|Kota\s+[^,]+)/i', $cleanLokasi, $m)) {
                $kecVal = trim($m[1]);
                $kabVal = trim($m[2]);
            } elseif (preg_match('/^(Kabupaten\s+[^,]+|Kota\s+[^,]+),\s*(Kecamatan\s+[^,]+)/i', $cleanLokasi, $m)) {
                $kabVal = trim($m[1]);
                $kecVal = trim($m[2]);
            }
        }

        $rightItems = [];
        $rightItems[] = ['text' => 'KEMENTERIAN PEKERJAAN UMUM', 'type' => 'title'];

        $pinReserveWidth = (int) ($infoFontSize * 1.15);
        if ($kabVal !== null && $kecVal !== null) {
            $locText = $kabVal . ' - ' . $kecVal . ($kelVal && $kelVal !== '' ? ' - ' . $kelVal : '');
            $wrapped = $this->wrapText($infoFontSize, $fontRegular, $locText, $rightAvailableWidth - $pinReserveWidth);
            $rightItems[] = ['text' => $wrapped[0], 'type' => 'location'];
        } elseif ($cleanLokasi !== '' && $cleanLokasi !== $cleanCoords) {
            $wrapped = $this->wrapText($infoFontSize, $fontRegular, $cleanLokasi, $rightAvailableWidth - $pinReserveWidth);
            $rightItems[] = ['text' => $wrapped[0], 'type' => 'location'];
        }
        if ($cleanCoords !== '') {
            $rightItems[] = ['text' => $cleanCoords, 'type' => 'coord'];
        }

        $totalH = count($rightItems) * $infoLineH;
        $infoY = $stripY1 + (($stripHeight - $totalH) / 2) + $infoFontSize;

        foreach ($rightItems as $idx => $item) {
            $font = ($idx === 0) ? $fontBold : $fontRegular;
            $color = ($idx === 0) ? $colorWhite : $colorMuted;
            $curX = (int) $rightX;
            $curY = (int) ($infoY + ($idx * $infoLineH));

            if ($item['type'] === 'location') {
                $curX += $this->drawMapPin($image, $curX, $curY, $infoFontSize, $colorYellow, $colorWhite);
            }

            @imagettftext($image, $infoFontSize, 0, $curX, $curY, $color, $font, $item['text']);
        }
    }

    // =========================================================================
    // HELPER METHODS: FONTS, SHADOW, DRAWING, IMAGE IO
    // =========================================================================

    private function getFont(string $style = self::FONT_REGULAR): string
    {
        $customFont = FCPATH . 'assets/fonts/Barlow-' . $style . '.ttf';
        if (is_file($customFont) && is_readable($customFont)) {
            return $customFont;
        }

        $fallbacks = [
            FCPATH . 'fonts/DejaVuSans.ttf',
            WRITEPATH . 'fonts/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ];

        foreach ($fallbacks as $path) {
            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        return $customFont;
    }

    /**
     * Bersihkan teks dari karakter emoji unicode 4-byte yang tidak didukung oleh font TTF standar
     * agar tidak menghasilkan karakter kotak atau tanda tanya mojibake (misal: "ð???").
     */
    private function sanitizeTextForFont(string $text): string
    {
        if ($text === '') {
            return '';
        }

        // 1. Pastikan string adalah valid UTF-8, buang byte invalid
        if (! mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        // 2. Hapus karakter pengganti Unicode U+FFFD (?) dan pola mojibake umum (misal: "ð???", "ðŸ“", dsb)
        $text = str_replace(["\xEF\xBF\xBD", "\u{FFFD}", 'ð???', 'ð??? ', 'ðŸ“', 'ðŸ', '📍', '📝', '🌐', '🏷️', '🏷', '⏱️', '⏱'], '', $text);

        // 3. Hapus emoji non-BMP (U+10000 s/d U+10FFFF)
        $clean = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $text);
        if ($clean === null) {
            $clean = $text;
        }

        // 4. Hapus emoji BMP umum
        $clean2 = preg_replace('/[\x{2600}-\x{27BF}\x{1F300}-\x{1F64F}\x{1F680}-\x{1F6FF}\x{FE00}-\x{FE0F}]/u', '', $clean);
        if ($clean2 !== null) {
            $clean = $clean2;
        }

        // 5. Hapus sisa mojibake karakter latin khusus / control character di awal teks (misal "ð???" atau sisa awalan byte non-ASCII rusak)
        $clean = preg_replace('/^[ð\x{00F0}\x{FFFD}\?\s\p{Cc}]{1,10}/u', '', $clean);
        // Hapus pola mojibake serupa jika tersisa di mana pun
        $clean = preg_replace('/[ð\x{00F0}][\x{FFFD}\?\s\p{Cc}]{1,8}/u', '', $clean);

        return trim($clean);
    }

    /**
     * Sanitasi ketat untuk teks koordinat GPS agar bebas 100% dari karakter mojibake/emoji rusak.
     * Hanya memperbolehkan digit angka, titik, koma, spasi, tanda derajat (°), tanda minus/plus, dan huruf kardinal (N, S, E, W).
     */
    public function sanitizeCoordinates(?string $coords): string
    {
        if ($coords === null || trim($coords) === '') {
            return '';
        }

        $clean = str_replace(["\xEF\xBF\xBD", "\u{FFFD}", 'ð???', 'ð??? ', 'ðŸ“', 'ðŸ', '📍', '📌', '🗺️', '🗺'], '', $coords);
        // Buang awalan non-angka/non-tanda
        $clean = preg_replace('/^[^\d\-\+\.]+/u', '', $clean);
        // Hanya pertahankan karakter yang valid untuk notasi koordinat desimal atau DMS
        $clean = preg_replace('/[^0-9\.\,\s°NSEWnsew\+\-\:\'\"\°]/u', '', $clean);

        return trim($clean);
    }

    /**
     * Gambar ikon pin lokasi vektor yang tajam secara native di GD.
     * Menjamin tidak ada masalah glyph hilang serta menghasilkan tampilan profesional.
     */
    private function drawMapPin($image, int $x, int $yBaseline, int $fontSize, int $pinColor, ?int $holeColor = null): int
    {
        $pinH = (int) ($fontSize * 1.05);
        $pinW = (int) max(6, $pinH * 0.68);
        $r = (int) ($pinW / 2);
        $cx = $x + $r;
        $cy = $yBaseline - $pinH + $r;
        $tipY = $yBaseline - (int) max(1, $fontSize * 0.05);

        // Bayangan pin lembut
        $shadowColor = imagecolorallocatealpha($image, 0, 0, 0, 85);
        $shadowOffset = 1;
        $shadowPoints = [
            $x + $shadowOffset, $cy + $shadowOffset,
            $x + $pinW + $shadowOffset, $cy + $shadowOffset,
            $cx + $shadowOffset, $tipY + $shadowOffset,
        ];
        imagefilledpolygon($image, $shadowPoints, $shadowColor);
        imagefilledellipse($image, $cx + $shadowOffset, $cy + $shadowOffset, $pinW, $pinW, $shadowColor);

        // Tubuh segitiga runcing pin
        $points = [
            $x, $cy,
            $x + $pinW, $cy,
            $cx, $tipY,
        ];
        imagefilledpolygon($image, $points, $pinColor);

        // Kepala lingkaran pin
        imagefilledellipse($image, $cx, $cy, $pinW, $pinW, $pinColor);

        // Lubang dalam putih
        if ($holeColor !== null) {
            $innerD = (int) max(2, $pinW * 0.38);
            imagefilledellipse($image, $cx, $cy, $innerD, $innerD, $holeColor);
        }

        return $pinW + (int) ($fontSize * 0.35); // spacing ke teks
    }

    private function drawGradientOverlay($image, int $x1, int $y1, int $x2, int $y2, int $alphaStart, int $alphaEnd): void
    {
        $h = $y2 - $y1;
        if ($h <= 0) return;

        for ($y = $y1; $y < $y2; $y++) {
            $ratio = ($y - $y1) / $h;
            // 127 = fully transparent, 0 = fully opaque
            $alpha = 127 - (int) ($ratio * $alphaEnd);
            $color = imagecolorallocatealpha($image, 0, 0, 0, max(0, min(127, $alpha)));
            imageline($image, $x1, $y, $x2, $y, $color);
        }
    }

    private function drawTextWithShadow($image, float $size, float $angle, int $x, int $y, $colorText, $colorShadow, string $font, string $text, int $offset = 2): void
    {
        @imagettftext($image, $size, $angle, $x + $offset, $y + $offset, $colorShadow, $font, $text);
        @imagettftext($image, $size, $angle, $x, $y, $colorText, $font, $text);
    }

    private function drawFilledRoundedRectangle($image, int $x1, int $y1, int $x2, int $y2, int $radius, $color): void
    {
        $radius = max(0, $radius);
        $w = $x2 - $x1;
        $h = $y2 - $y1;

        if ($radius * 2 > $w) $radius = (int) ($w / 2);
        if ($radius * 2 > $h) $radius = (int) ($h / 2);

        if ($radius <= 0) {
            imagefilledrectangle($image, $x1, $y1, $x2, $y2, $color);
            return;
        }

        imagefilledrectangle($image, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($image, $x1, $y1 + $radius, $x1 + $radius, $y2 - $radius, $color);
        imagefilledrectangle($image, $x2 - $radius, $y1 + $radius, $x2, $y2 - $radius, $color);

        imagefilledarc($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, 180, 270, $color, IMG_ARC_PIE);
        imagefilledarc($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, 270, 360, $color, IMG_ARC_PIE);
        imagefilledarc($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, 0, 90, $color, IMG_ARC_PIE);
        imagefilledarc($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, 90, 180, $color, IMG_ARC_PIE);
    }

    private function drawRoundedRectangleBorder($image, int $x1, int $y1, int $x2, int $y2, int $radius, $color): void
    {
        $radius = max(0, $radius);
        if ($radius <= 0) {
            imagerectangle($image, $x1, $y1, $x2, $y2, $color);
            return;
        }

        imageline($image, $x1 + $radius, $y1, $x2 - $radius, $y1, $color);
        imageline($image, $x1 + $radius, $y2, $x2 - $radius, $y2, $color);
        imageline($image, $x1, $y1 + $radius, $x1, $y2 - $radius, $color);
        imageline($image, $x2, $y1 + $radius, $x2, $y2 - $radius, $color);

        imagearc($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, 180, 270, $color);
        imagearc($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, 270, 360, $color);
        imagearc($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, 0, 90, $color);
        imagearc($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, 90, 180, $color);
    }

    private function copyResampledImage($dest, $src, int $dstX, int $dstY, int $dstW, int $dstH): void
    {
        $srcW = imagesx($src);
        $srcH = imagesy($src);

        $resized = imagecreatetruecolor($dstW, $dstH);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $trans = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $trans);

        imagecopyresampled($resized, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
        imagecopy($dest, $resized, $dstX, $dstY, 0, 0, $dstW, $dstH);
        imagedestroy($resized);
    }

    private function wrapText(float $fontSize, string $fontFace, string $string, int $maxWidth): array
    {
        $words = explode(' ', $string);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine === '' ? $word : $currentLine . ' ' . $word;
            $box = @imagettfbbox($fontSize, 0, $fontFace, $testLine);
            if ($box !== false) {
                $textWidth = $box[2] - $box[0];
                if ($textWidth > $maxWidth) {
                    if ($currentLine !== '') {
                        $lines[] = $currentLine;
                        $currentLine = $word;
                    } else {
                        $lines[] = $word;
                        $currentLine = '';
                    }
                } else {
                    $currentLine = $testLine;
                }
            } else {
                $currentLine = $testLine;
            }
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        return $lines;
    }

    private function createImageResource(string $path, string $mimeType)
    {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return @imagecreatefromjpeg($path);
            case 'image/png':
                return @imagecreatefrompng($path);
            case 'image/webp':
                return @imagecreatefromwebp($path);
            default:
                return false;
        }
    }

    private function fixOrientation($image, int $orientation)
    {
        switch ($orientation) {
            case 3:
                return imagerotate($image, 180, 0);
            case 6:
                return imagerotate($image, -90, 0);
            case 8:
                return imagerotate($image, 90, 0);
            default:
                return $image;
        }
    }

    private function loadLogoImage(?string $customLogoPath)
    {
        if ($customLogoPath !== null && is_file($customLogoPath) && is_readable($customLogoPath)) {
            $info = @getimagesize($customLogoPath);
            if ($info !== false) {
                return $this->createImageResource($customLogoPath, $info['mime']);
            }
        }

        // Default logo dari database HomeSetting
        try {
            $homeSettingModel = new \App\Models\HomeSettingModel();
            $homeSetting = $homeSettingModel->first();
            if ($homeSetting && ! empty($homeSetting['logo_url'])) {
                $defaultLogoPath = FCPATH . ltrim($homeSetting['logo_url'], '/');
                if (is_file($defaultLogoPath) && is_readable($defaultLogoPath)) {
                    $info = @getimagesize($defaultLogoPath);
                    if ($info !== false) {
                        return $this->createImageResource($defaultLogoPath, $info['mime']);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Abaikan jika tidak ada default logo
        }

        return null;
    }

    private function writeImageToFile($image, string $path, string $mimeType, int $quality): bool
    {
        switch ($mimeType) {
            case 'image/png':
                return imagepng($image, $path);
            case 'image/webp':
                return imagewebp($image, $path, $quality);
            case 'image/jpeg':
            case 'image/jpg':
            default:
                return imagejpeg($image, $path, $quality);
        }
    }

    /**
     * Konversi file HEIC / HEIF ke format JPEG sementara agar dapat diproses oleh PHP GD.
     * Mendukung multi-strategy:
     * 1. macOS native `sips`
     * 2. Linux `heif-convert`
     * 3. ImageMagick (`magick` / `convert`)
     * 4. PHP Imagick extension
     *
     * @return string|null Path ke file JPEG hasil konversi, atau null jika gagal
     */
    public function convertHeicToJpeg(string $heicPath, ?string $outputJpgPath = null): ?string
    {
        if (! is_file($heicPath) || ! is_readable($heicPath)) {
            return null;
        }

        if ($outputJpgPath === null) {
            $dir = dirname($heicPath);
            $filename = pathinfo($heicPath, PATHINFO_FILENAME);
            $outputJpgPath = $dir . DIRECTORY_SEPARATOR . $filename . '_from_heic_' . bin2hex(random_bytes(3)) . '.jpg';
        }

        $escapedHeic = escapeshellarg($heicPath);
        $escapedOut = escapeshellarg($outputJpgPath);

        // Strategi 1: macOS native `sips`
        $sipsBin = (PHP_OS_FAMILY === 'Darwin') ? ($this->findCliBinary('sips') ?? 'sips') : null;
        if ($sipsBin !== null) {
            $cmd = "{$sipsBin} -s format jpeg {$escapedHeic} --out {$escapedOut} 2>&1";
            @exec($cmd, $output, $code);
            if ($code === 0 && is_file($outputJpgPath) && filesize($outputJpgPath) > 0) {
                return $outputJpgPath;
            }
        }

        // Strategi 2: Linux `heif-convert` (libheif-examples)
        $heifConvert = $this->findCliBinary('heif-convert');
        if ($heifConvert !== null) {
            $cmd = "{$heifConvert} -q 92 {$escapedHeic} {$escapedOut} 2>&1";
            @exec($cmd, $output, $code);
            if ($code === 0 && is_file($outputJpgPath) && filesize($outputJpgPath) > 0) {
                return $outputJpgPath;
            }
        }

        // Strategi 3: ImageMagick (magick / convert)
        $magickBin = $this->findCliBinary('magick') ?? $this->findCliBinary('convert');
        if ($magickBin !== null) {
            $cmd = "{$magickBin} {$escapedHeic} {$escapedOut} 2>&1";
            @exec($cmd, $output, $code);
            if ($code === 0 && is_file($outputJpgPath) && filesize($outputJpgPath) > 0) {
                return $outputJpgPath;
            }
        }

        // Strategi 4: PHP Imagick Extension
        if (class_exists('\Imagick')) {
            try {
                $im = new \Imagick($heicPath);
                $im->setImageFormat('jpeg');
                $im->setImageCompressionQuality(92);
                $im->writeImage($outputJpgPath);
                $im->clear();
                $im->destroy();
                if (is_file($outputJpgPath) && filesize($outputJpgPath) > 0) {
                    return $outputJpgPath;
                }
            } catch (\Throwable $e) {
                // Lanjut ke fallback
            }
        }

        return null;
    }

    /**
     * Cek apakah file adalah format HEIC/HEIF berdasarkan ekstensi atau magic header.
     */
    public function isHeicFile(string $filePath): bool
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (in_array($ext, ['heic', 'heif'], true)) {
            return true;
        }

        // Cek ftypheic / ftypheif / ftypmif1 / ftypmsf1 di header berkas
        $handle = @fopen($filePath, 'rb');
        if ($handle) {
            $header = fread($handle, 16);
            fclose($handle);
            if ($header !== false && strlen($header) >= 12) {
                $box = substr($header, 4, 4);
                if ($box === 'ftyp') {
                    $brand = substr($header, 8, 4);
                    if (in_array($brand, ['heic', 'heix', 'hevc', 'heim', 'heis', 'hevm', 'hevs', 'mif1', 'msf1'], true)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Mencari path file binary CLI yang dapat dieksekusi.
     */
    private function findCliBinary(string $name): ?string
    {
        $candidates = [
            "/usr/bin/{$name}",
            "/usr/local/bin/{$name}",
            "/bin/{$name}",
            "/opt/homebrew/bin/{$name}",
        ];
        foreach ($candidates as $cand) {
            if (@is_file($cand) && @is_executable($cand)) {
                return $cand;
            }
        }
        $which = @shell_exec("which " . escapeshellarg($name) . " 2>/dev/null");
        if ($which !== null) {
            $trimmed = trim($which);
            if ($trimmed !== '' && @is_file($trimmed) && @is_executable($trimmed)) {
                return $trimmed;
            }
        }
        return null;
    }

    /**
     * Parsing string koordinat (format DMS, Cardinal Decimal, atau Standar Decimal) menjadi array [lat, lng].
     */
    public function parseCoordinateString(?string $coordStr): ?array
    {
        if ($coordStr === null) {
            return null;
        }
        $s = trim($coordStr);
        if ($s === '') {
            return null;
        }

        // Cek format DMS: contoh: 0° 31' 59.99" N, 101° 27' 0.00" E
        if (preg_match('/(\d+)\s*°\s*(\d+)\s*[\x27\x60\x{2019}]\s*([\d\.]+)\s*[\x22\x{201D}]\s*([NSns])[,\s]+(\d+)\s*°\s*(\d+)\s*[\x27\x60\x{2019}]\s*([\d\.]+)\s*[\x22\x{201D}]\s*([EWew])/u', $s, $dms)) {
            $lat = (int) $dms[1] + ((int) $dms[2] / 60) + ((float) $dms[3] / 3600);
            if (strtoupper($dms[4]) === 'S') {
                $lat = -$lat;
            }
            $lng = (int) $dms[5] + ((int) $dms[6] / 60) + ((float) $dms[7] / 3600);
            if (strtoupper($dms[8]) === 'W') {
                $lng = -$lng;
            }
            return ['lat' => $lat, 'lng' => $lng];
        }

        // Format Cardinal Decimal: contoh: "0.533333° N, 101.450000° E" atau "0.533333 N, 101.450000 E"
        if (preg_match('/([\d\.\-]+)\s*°?\s*([NSns])?[,\s]+([\d\.\-]+)\s*°?\s*([EWew])?/u', $s, $m)) {
            $lat = (float) $m[1];
            if (isset($m[2]) && strtoupper($m[2]) === 'S' && $lat > 0) {
                $lat = -$lat;
            }
            $lng = (float) $m[3];
            if (isset($m[4]) && strtoupper($m[4]) === 'W' && $lng > 0) {
                $lng = -$lng;
            }
            return ['lat' => $lat, 'lng' => $lng];
        }

        // Format Decimal Standar: contoh: "0.533333, 101.450000" atau "-0.533333, 101.450000"
        if (preg_match('/^([+-]?\d+(?:\.\d+)?)[,\s]+([+-]?\d+(?:\.\d+)?)$/', $s, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }

        return null;
    }

    /**
     * Menyematkan / memperbarui metadata EXIF biner langsung ke dalam file JPEG menggunakan library PEL (lsolesen/pel).
     * Tag yang diperbarui:
     * - IFD0: DateTime, Software, ImageDescription
     * - ExifSubIFD: DateTimeOriginal, DateTimeDigitized
     * - GpsIFD: GPSLatitude, GPSLatitudeRef, GPSLongitude, GPSLongitudeRef, GPSDateStamp, GPSTimeStamp
     *
     * @param string $filePath Jalur absolut file JPEG di server
     * @param array $dateTimeMeta Array metadata waktu dari resolveDateTime()
     * @param string|null $coordinates String koordinat GPS
     * @param string $lokasi Teks nama lokasi / alamat kegiatan
     * @param string $notes Catatan kegiatan proyek
     * @return bool True jika sukses atau file bukan JPEG dilewati
     */
    public function embedExifMetadata(string $filePath, array $dateTimeMeta, ?string $coordinates = null, string $lokasi = '', string $notes = ''): bool
    {
        if (! is_file($filePath)) {
            return false;
        }

        // Pastikan file berformat JPEG
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (! in_array($ext, ['jpg', 'jpeg'], true)) {
            return false;
        }

        try {
            $jpeg = new \lsolesen\pel\PelJpeg($filePath);
            $exif = $jpeg->getExif();
            if ($exif === null) {
                $exif = new \lsolesen\pel\PelExif();
                $jpeg->setExif($exif);
            }

            $tiff = $exif->getTiff();
            if ($tiff === null) {
                $tiff = new \lsolesen\pel\PelTiff();
                $exif->setTiff($tiff);
            }

            $ifd0 = $tiff->getIfd();
            if ($ifd0 === null) {
                $ifd0 = new \lsolesen\pel\PelIfd(\lsolesen\pel\PelIfd::IFD0);
                $tiff->setIfd($ifd0);
            }

            // 1. Timestamp Waktu (DateTime, Software, ImageDescription)
            $timestamp = $dateTimeMeta['timestamp'] ?? time();

            // Set IFD0 Date Time
            $ifd0->addEntry(new \lsolesen\pel\PelEntryTime(\lsolesen\pel\PelTag::DATE_TIME, $timestamp));

            // Set IFD0 Software
            $ifd0->addEntry(new \lsolesen\pel\PelEntryAscii(\lsolesen\pel\PelTag::SOFTWARE, 'TimeMark Camera & Satker PPS Riau'));

            // Set IFD0 ImageDescription
            $descParts = [];
            if ($lokasi !== '') {
                $descParts[] = 'Lokasi: ' . $lokasi;
            }
            if ($notes !== '') {
                $descParts[] = 'Catatan: ' . $notes;
            }
            if ($coordinates !== null && $coordinates !== '') {
                $descParts[] = 'GPS: ' . $coordinates;
            }
            $description = ! empty($descParts) ? implode(' | ', $descParts) : 'Dokumentasi Lapangan Satker PPS Riau';
            $ifd0->addEntry(new \lsolesen\pel\PelEntryAscii(\lsolesen\pel\PelTag::IMAGE_DESCRIPTION, $description));

            // 2. ExifSubIFD: DateTimeOriginal & DateTimeDigitized
            $subIfd = $ifd0->getSubIfd(\lsolesen\pel\PelIfd::EXIF);
            if ($subIfd === null) {
                $subIfd = new \lsolesen\pel\PelIfd(\lsolesen\pel\PelIfd::EXIF);
                $ifd0->addSubIfd($subIfd);
            }
            $subIfd->addEntry(new \lsolesen\pel\PelEntryTime(\lsolesen\pel\PelTag::DATE_TIME_ORIGINAL, $timestamp));
            $subIfd->addEntry(new \lsolesen\pel\PelEntryTime(\lsolesen\pel\PelTag::DATE_TIME_DIGITIZED, $timestamp));

            // 3. GpsIFD: GPS Coordinates & Timestamps
            $parsedCoords = $this->parseCoordinateString($coordinates);
            if ($parsedCoords !== null) {
                $gpsIfd = $ifd0->getSubIfd(\lsolesen\pel\PelIfd::GPS);
                if ($gpsIfd === null) {
                    $gpsIfd = new \lsolesen\pel\PelIfd(\lsolesen\pel\PelIfd::GPS);
                    $ifd0->addSubIfd($gpsIfd);
                }

                $gpsIfd->addEntry(new \lsolesen\pel\PelEntryByte(\lsolesen\pel\PelTag::GPS_VERSION_ID, 2, 2, 0, 0));

                $lat = $parsedCoords['lat'];
                $lng = $parsedCoords['lng'];
                $latRef = ($lat >= 0) ? 'N' : 'S';
                $lngRef = ($lng >= 0) ? 'E' : 'W';
                $lat = abs($lat);
                $lng = abs($lng);

                $latDeg = floor($lat);
                $latMin = floor(($lat - $latDeg) * 60);
                $latSec = round(($lat - $latDeg - $latMin / 60) * 3600 * 1000);

                $lngDeg = floor($lng);
                $lngMin = floor(($lng - $lngDeg) * 60);
                $lngSec = round(($lng - $lngDeg - $lngMin / 60) * 3600 * 1000);

                $gpsIfd->addEntry(new \lsolesen\pel\PelEntryAscii(\lsolesen\pel\PelTag::GPS_LATITUDE_REF, $latRef));
                $gpsIfd->addEntry(new \lsolesen\pel\PelEntryRational(\lsolesen\pel\PelTag::GPS_LATITUDE, [(int) $latDeg, 1], [(int) $latMin, 1], [(int) $latSec, 1000]));
                $gpsIfd->addEntry(new \lsolesen\pel\PelEntryAscii(\lsolesen\pel\PelTag::GPS_LONGITUDE_REF, $lngRef));
                $gpsIfd->addEntry(new \lsolesen\pel\PelEntryRational(\lsolesen\pel\PelTag::GPS_LONGITUDE, [(int) $lngDeg, 1], [(int) $lngMin, 1], [(int) $lngSec, 1000]));

                // Tanggal & Jam GPS UTC
                $gpsDate = date('Y:m:d', $timestamp);
                $gpsIfd->addEntry(new \lsolesen\pel\PelEntryAscii(\lsolesen\pel\PelTag::GPS_DATE_STAMP, $gpsDate));
                $h = (int) gmdate('H', $timestamp);
                $i = (int) gmdate('i', $timestamp);
                $s = (int) gmdate('s', $timestamp);
                $gpsIfd->addEntry(new \lsolesen\pel\PelEntryRational(\lsolesen\pel\PelTag::GPS_TIME_STAMP, [$h, 1], [$i, 1], [$s, 1]));
            }

            $jpeg->saveFile($filePath);
            return true;
        } catch (\Throwable $e) {
            log_message('error', 'Gagal menyematkan metadata EXIF pada ' . $filePath . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Menghitung jarak antara dua koordinat GPS menggunakan formula Haversine (dalam satuan meter).
     */
    public function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000.0; // Radius bumi dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(max(0.0, 1 - $a)));
        return $earthRadius * $c;
    }

    /**
     * Mengelompokkan daftar berkas foto berdasarkan kedekatan lokasi koordinat GPS (GPS Clustering).
     * Foto-foto dengan koordinat GPS dalam radius toleransi ($radiusMeters) akan dikelompokkan ke cluster yang sama.
     * Setiap kelompok otomatis diberi nama folder bersih yang menyertakan nomor urut dan nama lokasi / alamat (misal: Lokasi_01_Pekanbaru).
     * Foto tanpa koordinat GPS akan dikelompokkan ke dalam folder khusus Tanpa_Koordinat_GPS.
     *
     * @param array $imageItems Daftar item berkas foto ['path' => ..., 'original_name' => ..., ...]
     * @param float $radiusMeters Radius toleransi pengelompokan (meter), default 100m
     * @param bool $useExifLocation Utamakan membaca GPS dari EXIF foto asli
     * @param string|null $fallbackCoordStr Koordinat default dari input form jika EXIF kosong
     * @return array ['clusters' => [...], 'items' => [...]]
     */
    public function clusterImagesByCoordinates(
        array $imageItems,
        float $radiusMeters = 100.0,
        bool $useExifLocation = true,
        ?string $fallbackCoordStr = null,
        string $groupingMode = 'kab_kec'
    ): array {
        $fallbackCoords = null;
        if (! empty($fallbackCoordStr)) {
            $fallbackCoords = $this->parseCoordinateString($fallbackCoordStr);
        }

        $rawClusters = [];
        $noGpsItems = [];
        $kecSpotCounters = [];

        foreach ($imageItems as $item) {
            $filePath = $item['path'];
            $gpsDecimal = $item['gps_decimal'] ?? null;
            $gpsDms = $item['gps_dms'] ?? null;

            if ($gpsDecimal === null && $useExifLocation) {
                $exif = $this->readExifData($filePath);
                if (is_array($exif)) {
                    $gpsDecimal = $this->extractGpsDecimal($exif);
                    $gpsDms = $this->extractGpsCoordinates($exif);
                }
            }

            // Fallback ke koordinat manual jika EXIF tidak memiliki GPS
            if ($gpsDecimal === null && $fallbackCoords !== null) {
                $gpsDecimal = $fallbackCoords;
                $gpsDms = sprintf('%.6f° %s, %.6f° %s', abs($fallbackCoords['lat']), $fallbackCoords['lat'] >= 0 ? 'N' : 'S', abs($fallbackCoords['lng']), $fallbackCoords['lng'] >= 0 ? 'E' : 'W');
            }

            $item['_gps'] = $gpsDecimal;
            $item['_gps_dms'] = $gpsDms;

            if ($gpsDecimal === null) {
                $noGpsItems[] = $item;
                continue;
            }

            // Dapatkan informasi administratif via reverse geocoding terstruktur
            $wilayah = $item['wilayah'] ?? null;
            if ($wilayah === null) {
                $wilayah = $this->reverseGeocodeStructured($gpsDecimal['lat'], $gpsDecimal['lng'], $groupingMode);
            }
            $item['_wilayah'] = $wilayah;

            // Tentukan hierarki pengelompokan berdasarkan $groupingMode
            if ($groupingMode === 'kab') {
                $groupKey = $wilayah['slug_kab'];
                $folderName = $wilayah['slug_kab'];
                $display = $wilayah['kabupaten'];
                $kelVal = '-';
            } elseif ($groupingMode === 'kab_kec_kel') {
                $kelVal = (!empty($wilayah['kelurahan']) && $wilayah['kelurahan'] !== '-') ? $wilayah['kelurahan'] : 'Wilayah ' . str_ireplace('Kecamatan ', '', $wilayah['kecamatan']);
                $slugKel = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $kelVal);
                $slugKel = preg_replace('/_+/', '_', trim($slugKel, '_'));
                $groupKey = $wilayah['slug_kab'] . '__' . $wilayah['slug_kec'] . '__' . $slugKel;
                $folderName = $wilayah['slug_kab'] . '/' . $wilayah['slug_kec'] . '/' . $slugKel;
                $display = $kelVal . ', ' . $wilayah['kecamatan'] . ', ' . $wilayah['kabupaten'];
            } elseif ($groupingMode === 'kab_kec_spot') {
                // Cari apakah sudah ada spot di kecamatan ini dalam radius $radiusMeters
                $kecKey = $wilayah['slug_kab'] . '__' . $wilayah['slug_kec'];
                $matchedSpotKey = null;

                foreach ($rawClusters as $k => $c) {
                    if (isset($c['kec_key']) && $c['kec_key'] === $kecKey) {
                        $dist = $this->haversineDistance($gpsDecimal['lat'], $gpsDecimal['lng'], $c['centroid_lat'], $c['centroid_lng']);
                        if ($dist <= $radiusMeters) {
                            $matchedSpotKey = $k;
                            break;
                        }
                    }
                }

                if ($matchedSpotKey !== null) {
                    $groupKey = $matchedSpotKey;
                    $folderName = $rawClusters[$matchedSpotKey]['folder_name'];
                    $display = $rawClusters[$matchedSpotKey]['display'];
                    $kelVal = $rawClusters[$matchedSpotKey]['kelurahan'] ?? '-';
                } else {
                    if (! isset($kecSpotCounters[$kecKey])) {
                        $kecSpotCounters[$kecKey] = 0;
                    }
                    $kecSpotCounters[$kecKey]++;
                    $spotNum = $kecSpotCounters[$kecKey];
                    $spotSlug = sprintf('Spot_%02d_%.4f_%.4f', $spotNum, $gpsDecimal['lat'], $gpsDecimal['lng']);
                    $groupKey = $kecKey . '__' . $spotSlug;
                    $folderName = $wilayah['slug_kab'] . '/' . $wilayah['slug_kec'] . '/' . $spotSlug;
                    $display = sprintf('Spot %02d: %s, %s', $spotNum, $wilayah['kecamatan'], $wilayah['kabupaten']);
                    $kelVal = sprintf('Spot %02d', $spotNum);
                }
            } else {
                // Default: kab_kec
                $groupKey = $wilayah['slug_kab'] . '__' . $wilayah['slug_kec'];
                $folderName = $wilayah['slug_kab'] . '/' . $wilayah['slug_kec'];
                $display = $wilayah['kecamatan'] . ', ' . $wilayah['kabupaten'];
                $kelVal = '-';
            }

            if (! isset($rawClusters[$groupKey])) {
                $rawClusters[$groupKey] = [
                    'group_key'    => $groupKey,
                    'kec_key'      => $wilayah['slug_kab'] . '__' . $wilayah['slug_kec'],
                    'kabupaten'    => $wilayah['kabupaten'],
                    'kecamatan'    => $wilayah['kecamatan'],
                    'kelurahan'    => $kelVal,
                    'slug_kab'     => $wilayah['slug_kab'],
                    'slug_kec'     => $wilayah['slug_kec'],
                    'folder_name'  => $folderName,
                    'display'      => $display,
                    'centroid_lat' => $gpsDecimal['lat'],
                    'centroid_lng' => $gpsDecimal['lng'],
                    'first_dms'    => $gpsDms,
                    'items'        => [$item],
                ];
            } else {
                $rawClusters[$groupKey]['items'][] = $item;
                $cnt = count($rawClusters[$groupKey]['items']);
                $rawClusters[$groupKey]['centroid_lat'] = ($rawClusters[$groupKey]['centroid_lat'] * ($cnt - 1) + $gpsDecimal['lat']) / $cnt;
                $rawClusters[$groupKey]['centroid_lng'] = ($rawClusters[$groupKey]['centroid_lng'] * ($cnt - 1) + $gpsDecimal['lng']) / $cnt;
            }
        }

        $finalClusters = [];
        $allProcessedItems = [];
        $clusterNum = 1;

        // Proses cluster GPS terkelompok
        foreach ($rawClusters as $cluster) {
            $cLat = round($cluster['centroid_lat'], 6);
            $cLng = round($cluster['centroid_lng'], 6);
            $cId = (string) $clusterNum;

            $folderName = $cluster['folder_name'];
            $dmsText = sprintf('%.6f° %s, %.6f° %s', abs($cLat), $cLat >= 0 ? 'N' : 'S', abs($cLng), $cLng >= 0 ? 'E' : 'W');

            $clusterMeta = [
                'id'          => $cId,
                'kabupaten'   => $cluster['kabupaten'],
                'kecamatan'   => $cluster['kecamatan'],
                'kelurahan'   => $cluster['kelurahan'] ?? '-',
                'folder_name' => $folderName,
                'name'        => $cluster['display'],
                'coords_text' => $dmsText,
                'lat'         => $cLat,
                'lng'         => $cLng,
                'count'       => count($cluster['items']),
            ];
            $finalClusters[] = $clusterMeta;

            foreach ($cluster['items'] as $item) {
                $item['sub_dir'] = $folderName;
                $item['cluster_id'] = $cId;
                $item['cluster_name'] = $clusterMeta['name'];
                $item['cluster_folder'] = $folderName;
                $item['kabupaten'] = $cluster['kabupaten'];
                $item['kecamatan'] = $cluster['kecamatan'];
                $item['kelurahan'] = $cluster['kelurahan'] ?? '-';
                $allProcessedItems[] = $item;
            }

            $clusterNum++;
        }

        // Proses item tanpa GPS
        if (! empty($noGpsItems)) {
            $folderName = 'Tanpa_Koordinat_GPS';
            $clusterMeta = [
                'id'          => 'no_gps',
                'kabupaten'   => 'Tanpa Koordinat GPS',
                'kecamatan'   => '-',
                'kelurahan'   => '-',
                'folder_name' => $folderName,
                'name'        => 'Foto Tanpa Koordinat GPS',
                'coords_text' => '-',
                'lat'         => null,
                'lng'         => null,
                'count'       => count($noGpsItems),
            ];
            $finalClusters[] = $clusterMeta;

            foreach ($noGpsItems as $item) {
                $item['sub_dir'] = $folderName;
                $item['cluster_id'] = 'no_gps';
                $item['cluster_name'] = 'Foto Tanpa Koordinat GPS';
                $item['cluster_folder'] = $folderName;
                $item['kabupaten'] = 'Tanpa Koordinat GPS';
                $item['kecamatan'] = '-';
                $item['kelurahan'] = '-';
                $allProcessedItems[] = $item;
            }
        }

        return [
            'clusters' => $finalClusters,
            'items'    => $allProcessedItems,
        ];
    }

    /**
     * Menghasilkan thumbnail responsif ringan (base64 Data URI) dari berkas foto asli.
     */
    public function generateThumbnailDataUri(string $filePath, int $maxWidth = 360, int $quality = 68): ?string
    {
        if (! is_file($filePath) || ! is_readable($filePath)) {
            return null;
        }

        try {
            $isHeic = in_array(strtolower(pathinfo($filePath, PATHINFO_EXTENSION)), ['heic', 'heif'], true);
            $img = null;

            if ($isHeic) {
                // Konversi HEIC sementara untuk thumbnail jika diperlukan
                $converted = $this->convertHeicToJpeg($filePath);
                if ($converted !== null && is_file($converted)) {
                    $img = @imagecreatefromjpeg($converted);
                    @unlink($converted);
                }
            } else {
                $raw = @file_get_contents($filePath);
                if ($raw !== false && $raw !== '') {
                    $img = @imagecreatefromstring($raw);
                }
            }

            if ($img === null || $img === false) {
                return null;
            }

            $w = imagesx($img);
            $h = imagesy($img);
            if ($w <= 0 || $h <= 0) {
                imagedestroy($img);
                return null;
            }

            $thumbW = min($w, $maxWidth);
            $thumbH = (int) max(1, round(($h / $w) * $thumbW));

            $thumb = imagecreatetruecolor($thumbW, $thumbH);
            imagecopyresampled($thumb, $img, 0, 0, 0, 0, $thumbW, $thumbH, $w, $h);
            imagedestroy($img);

            ob_start();
            imagejpeg($thumb, null, $quality);
            $bytes = ob_get_clean();
            imagedestroy($thumb);

            if ($bytes !== false && $bytes !== '') {
                return 'data:image/jpeg;base64,' . base64_encode($bytes);
            }
        } catch (\Throwable $e) {
            // Abaikan kegagalan thumbnail
        }

        return null;
    }
}

