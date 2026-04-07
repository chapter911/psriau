<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\HomeSettingModel;
use App\Models\HomeSlideModel;

class HomeSetting extends BaseController
{
    public function index()
    {
        $settingModel = new HomeSettingModel();
        $slideModel   = new HomeSlideModel();

        $setting = $settingModel->first();

        if (! $setting) {
            $settingId = $settingModel->insert([
                'hero_title'    => 'Satker PPS Kementerian PU',
                'hero_subtitle' => 'Silakan sesuaikan teks ini dari panel admin.',
                'about_intro'   => 'Silakan tulis pengantar tentang instansi Anda.',
                'official_name' => 'Satker PPS Kementerian PU',
                'logo_url' => '',
                'contact_email' => 'info@satkerpps.pu.go.id',
                'contact_phone' => '(0761) 000000',
                'contact_address' => 'Pekanbaru, Riau',
                'contact_map_url' => 'https://maps.google.com',
                'instagram_profile_url' => 'https://www.instagram.com/pu_prasaranastrategis_riau/',
                'default_event_image' => '',
                'default_article_image' => '',
                'updated_at'    => date('Y-m-d H:i:s'),
                'updated_by'    => (int) session()->get('userId'),
            ], true);

            $setting = $settingModel->find($settingId);
        }

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'hero_title'    => 'required|min_length[5]',
                'hero_subtitle' => 'required|min_length[10]',
                'about_intro'   => 'required|min_length[20]',
                'official_name' => 'required|min_length[5]',
                'logo_file' => 'if_exist|is_image[logo_file]|max_size[logo_file,4096]|mime_in[logo_file,image/jpg,image/jpeg,image/png,image/webp,image/svg+xml]',
                'contact_email' => 'permit_empty|valid_email',
                'contact_map_url' => 'permit_empty|valid_url_strict',
                'instagram_profile_url' => 'permit_empty|valid_url_strict',
                'default_event_image_file' => 'if_exist|is_image[default_event_image_file]|max_size[default_event_image_file,4096]|mime_in[default_event_image_file,image/jpg,image/jpeg,image/png,image/webp,image/svg+xml]',
                'default_article_image_file' => 'if_exist|is_image[default_article_image_file]|max_size[default_article_image_file,4096]|mime_in[default_article_image_file,image/jpg,image/jpeg,image/png,image/webp,image/svg+xml]',
            ];

            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('error', 'Data pengaturan home belum valid.');
            }

            $logoPath = $this->uploadImage('logo_file', 'branding');
            $defaultEventImagePath = $this->uploadImage('default_event_image_file', 'defaults');
            $defaultArticleImagePath = $this->uploadImage('default_article_image_file', 'defaults');

            if ($logoPath !== null) {
                $this->deleteLocalImage($setting['logo_url'] ?? null);
            }

            if ($defaultEventImagePath !== null) {
                $this->deleteLocalImage($setting['default_event_image'] ?? null);
            }

            if ($defaultArticleImagePath !== null) {
                $this->deleteLocalImage($setting['default_article_image'] ?? null);
            }

            $settingModel->update($setting['id'], [
                'hero_title'    => $this->request->getPost('hero_title'),
                'hero_subtitle' => $this->request->getPost('hero_subtitle'),
                'about_intro'   => $this->request->getPost('about_intro'),
                'official_name' => $this->request->getPost('official_name'),
                'logo_url' => $logoPath ?? ($setting['logo_url'] ?? ''),
                'contact_email' => $this->request->getPost('contact_email'),
                'contact_phone' => $this->request->getPost('contact_phone'),
                'contact_address' => $this->request->getPost('contact_address'),
                'contact_map_url' => $this->request->getPost('contact_map_url'),
                'instagram_profile_url' => $this->request->getPost('instagram_profile_url'),
                'default_event_image' => $defaultEventImagePath ?? ($setting['default_event_image'] ?? ''),
                'default_article_image' => $defaultArticleImagePath ?? ($setting['default_article_image'] ?? ''),
                'updated_at'    => date('Y-m-d H:i:s'),
                'updated_by'    => (int) session()->get('userId'),
            ]);

            return redirect()->to('/admin/pengaturan-home')->with('message', 'Pengaturan home berhasil diperbarui.');
        }

        $slides = $slideModel->orderBy('sort_order', 'ASC')->findAll();

        return view('admin/home/settings', [
            'setting' => $setting,
            'slides'  => $slides,
        ]);
    }

    public function createSlide()
    {
        $rules = [
            'title'      => 'required|min_length[3]',
            'slide_image'  => 'uploaded[slide_image]|is_image[slide_image]|max_size[slide_image,4096]|mime_in[slide_image,image/jpg,image/jpeg,image/png,image/webp,image/svg+xml]',
            'sort_order' => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data slide tidak valid.');
        }

        $slideImagePath = $this->uploadImage('slide_image', 'slides');
        if ($slideImagePath === null) {
            return redirect()->back()->withInput()->with('error', 'Gagal mengunggah gambar slide.');
        }

        (new HomeSlideModel())->insert([
            'title'      => $this->request->getPost('title'),
            'image_url'  => $slideImagePath,
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active'  => (int) ($this->request->getPost('is_active') ? 1 : 0),
        ]);

        return redirect()->to('/admin/pengaturan-home')->with('message', 'Slide baru berhasil ditambahkan.');
    }

    public function updateSlide(int $id)
    {
        $slideModel = new HomeSlideModel();
        $slide      = $slideModel->find($id);

        if (! $slide) {
            return redirect()->to('/admin/pengaturan-home')->with('error', 'Slide tidak ditemukan.');
        }

        $rules = [
            'title'      => 'required|min_length[3]',
            'slide_image'  => 'if_exist|is_image[slide_image]|max_size[slide_image,4096]|mime_in[slide_image,image/jpg,image/jpeg,image/png,image/webp,image/svg+xml]',
            'sort_order' => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data slide tidak valid.');
        }

        $slideImagePath = $this->uploadImage('slide_image', 'slides');
        if ($slideImagePath !== null) {
            $this->deleteLocalImage($slide['image_url'] ?? null);
        }

        $slideModel->update($id, [
            'title'      => $this->request->getPost('title'),
            'image_url'  => $slideImagePath ?? ($slide['image_url'] ?? ''),
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active'  => (int) ($this->request->getPost('is_active') ? 1 : 0),
        ]);

        return redirect()->to('/admin/pengaturan-home')->with('message', 'Slide berhasil diperbarui.');
    }

    public function deleteSlide(int $id)
    {
        $slideModel = new HomeSlideModel();
        $slide = $slideModel->find($id);

        if ($slide) {
            $this->deleteLocalImage($slide['image_url'] ?? null);
            $slideModel->delete($id);
        }

        return redirect()->to('/admin/pengaturan-home')->with('message', 'Slide berhasil dihapus.');
    }

    private function uploadImage(string $fieldName, string $directory): ?string
    {
        $file = $this->request->getFile($fieldName);

        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $targetDir = FCPATH . 'uploads/' . $directory;
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $newName = $file->getRandomName();
        $file->move($targetDir, $newName);

        return '/uploads/' . $directory . '/' . $newName;
    }

    private function deleteLocalImage(?string $path): void
    {
        if (empty($path) || strpos($path, '/uploads/') !== 0) {
            return;
        }

        $filePath = FCPATH . ltrim($path, '/');
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }

}
