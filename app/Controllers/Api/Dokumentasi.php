<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\KegiatanLapanganModel;
use App\Services\ApiAuth;
use CodeIgniter\HTTP\ResponseInterface;

class Dokumentasi extends BaseController
{
    private const MENU_LINKS = [
        'admin/dokumentasi/kegiatan-lapangan',
        'admin/dokumentasi/kegiatan',
    ];

    /**
     * Daftar kegiatan dokumentasi beserta kategori dan daftar foto.
     * URL: GET /api/dokumentasi/kegiatan
     */
    public function kegiatan()
    {
        if (! ApiAuth::hasMenuAccess(self::MENU_LINKS, 'view')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses ke menu Dokumentasi.',
            ]);
        }

        $title = trim((string) $this->request->getGet('title'));
        $kategori = trim((string) $this->request->getGet('kategori'));
        $location = trim((string) $this->request->getGet('location'));
        $keyword = trim((string) $this->request->getGet('keyword'));

        $db = db_connect();
        $builder = $db->table('kegiatan_lapangan');

        if ($title !== '') {
            $builder->like('title', $title);
        }
        if ($kategori !== '' && $kategori !== '*') {
            $builder->where('kategori', $kategori);
        }
        if ($location !== '') {
            $builder->like('location', $location);
        }
        if ($keyword !== '') {
            $builder->groupStart()
                ->like('title', $keyword)
                ->orLike('kategori', $keyword)
                ->orLike('location', $keyword)
                ->orLike('created_by', $keyword)
                ->groupEnd();
        }

        $rows = $builder
            ->orderBy('activity_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        $activityIds = array_values(array_filter(array_map(
            static fn (array $row): int => (int) ($row['id'] ?? 0),
            $rows
        )));

        $photosByActivity = [];
        if ($activityIds !== [] && $db->tableExists('kegiatan_lapangan_photos')) {
            $photoRows = $db->table('kegiatan_lapangan_photos')
                ->whereIn('activity_id', $activityIds)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($photoRows as $photo) {
                $actId = (int) ($photo['activity_id'] ?? 0);
                $path = trim((string) ($photo['photo_path'] ?? ''));
                $photosByActivity[$actId][] = [
                    'id' => (int) ($photo['id'] ?? 0),
                    'photo_path' => $path,
                    'photo_url' => $path !== '' ? base_url(ltrim($path, '/')) : null,
                    'photo_name' => $photo['photo_name'] ?? null,
                    'sort_order' => (int) ($photo['sort_order'] ?? 0),
                ];
            }
        }

        $items = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            $photos = $photosByActivity[$id] ?? [];
            $items[] = [
                'id' => $id,
                'title' => $row['title'] ?? '',
                'kategori' => $row['kategori'] ?? 'Kegiatan Lapangan',
                'activity_date' => $row['activity_date'] ?? null,
                'location' => $row['location'] ?? '',
                'created_by' => $row['created_by'] ?? '',
                'photo_count' => count($photos),
                'photos' => $photos,
                'created_at' => $row['created_at'] ?? null,
            ];
        }

        $activityModel = new KegiatanLapanganModel();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'categories' => $activityModel->getCategories(),
                'items' => $items,
            ],
        ]);
    }
}

