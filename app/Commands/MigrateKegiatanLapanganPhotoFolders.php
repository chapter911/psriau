<?php

namespace App\Commands;

use App\Models\KegiatanLapanganModel;
use App\Models\KegiatanLapanganPhotoModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MigrateKegiatanLapanganPhotoFolders extends BaseCommand
{
    protected $group = 'Maintenance';
    protected $name = 'kegiatan-lapangan:migrate-folders';
    protected $description = 'Memindahkan foto kegiatan lapangan lama ke struktur folder per kegiatan (tahun/bulan/kode-slug).';

    public function run(array $params)
    {
        $activityModel = new KegiatanLapanganModel();
        $photoModel = new KegiatanLapanganPhotoModel();

        $activities = $activityModel->findAll();
        if ($activities === []) {
            CLI::write('Tidak ada kegiatan untuk dimigrasikan.', 'yellow');
            return;
        }

        $moved = 0;
        $updated = 0;
        $skipped = 0;
        $missing = 0;

        foreach ($activities as $activity) {
            $activityId = (int) ($activity['id'] ?? 0);
            if ($activityId <= 0) {
                continue;
            }

            $activityTitle = (string) ($activity['title'] ?? 'kegiatan-lapangan');
            $folderConfig = $this->resolveActivityUploadFolder($activityId, $activityTitle);

            $photos = $photoModel
                ->where('activity_id', $activityId)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();

            if ($photos === []) {
                continue;
            }

            if (! is_dir($folderConfig['absolute']) && ! @mkdir($folderConfig['absolute'], 0775, true) && ! is_dir($folderConfig['absolute'])) {
                CLI::write('Gagal membuat folder: ' . $folderConfig['absolute'], 'red');
                $skipped += count($photos);
                continue;
            }

            foreach ($photos as $photo) {
                $photoId = (int) ($photo['id'] ?? 0);
                $photoPath = (string) ($photo['photo_path'] ?? '');

                if ($photoId <= 0 || $photoPath === '' || strpos($photoPath, '/uploads/') !== 0) {
                    $skipped++;
                    continue;
                }

                if ($this->isAlreadyInTargetFolder($photoPath, $folderConfig['public'])) {
                    $skipped++;
                    continue;
                }

                $sourceAbsolute = FCPATH . ltrim($photoPath, '/');
                if (! is_file($sourceAbsolute)) {
                    $missing++;
                    continue;
                }

                $targetFileName = basename($sourceAbsolute);
                $targetAbsolute = $folderConfig['absolute'] . DIRECTORY_SEPARATOR . $targetFileName;
                $targetAbsolute = $this->resolveUniquePath($targetAbsolute);

                if (! @rename($sourceAbsolute, $targetAbsolute)) {
                    if (! @copy($sourceAbsolute, $targetAbsolute)) {
                        $skipped++;
                        continue;
                    }
                    @unlink($sourceAbsolute);
                }

                $targetPublicPath = rtrim($folderConfig['public'], '/') . '/' . basename($targetAbsolute);
                $photoModel->update($photoId, [
                    'photo_path' => $targetPublicPath,
                ]);

                $moved++;
                $updated++;
            }
        }

        CLI::write('Migrasi selesai.', 'green');
        CLI::write('Dipindahkan: ' . $moved, 'green');
        CLI::write('Diupdate DB: ' . $updated, 'green');
        CLI::write('Dilewati: ' . $skipped, 'yellow');
        CLI::write('File tidak ditemukan: ' . $missing, 'yellow');
    }

    private function resolveActivityUploadFolder(int $activityId, string $activityTitle): array
    {
        $year = date('Y');
        $month = date('m');
        $activityCode = 'kegiatan-' . str_pad((string) $activityId, 6, '0', STR_PAD_LEFT);
        $slug = $this->slugifyFolderName($activityTitle);

        $relative = 'uploads/dokumentasi/kegiatan-lapangan/' . $year . '/' . $month . '/' . $activityCode . '-' . $slug;

        return [
            'absolute' => FCPATH . $relative,
            'public' => '/' . $relative,
        ];
    }

    private function slugifyFolderName(string $text): string
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        if ($slug === '') {
            return 'kegiatan';
        }

        return substr($slug, 0, 48);
    }

    private function isAlreadyInTargetFolder(string $photoPath, string $targetPublicFolder): bool
    {
        $normalized = rtrim($targetPublicFolder, '/') . '/';
        return strpos($photoPath, $normalized) === 0;
    }

    private function resolveUniquePath(string $path): string
    {
        if (! file_exists($path)) {
            return $path;
        }

        $dir = dirname($path);
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $counter = 1;

        while (true) {
            $candidate = $dir . DIRECTORY_SEPARATOR . $filename . '_' . $counter . ($ext !== '' ? '.' . $ext : '');
            if (! file_exists($candidate)) {
                return $candidate;
            }
            $counter++;
        }
    }
}
