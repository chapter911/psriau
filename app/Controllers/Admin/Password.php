<?php
// app/Controllers/Admin/Password.php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Password extends BaseController
{
    public function index()
    {
        return redirect()->to('/admin')->with('open_password_modal', 1);
    }

    public function update()
    {
        $isAjax = $this->request->isAJAX();
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (! $this->validate($rules)) {
            $message = (string) (reset($this->validator->getErrors()) ?: 'Validasi gagal.');
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => $message,
                    'csrfHash' => csrf_hash(),
                ]);
            }

            return redirect()->back()->withInput()->with('error', 'Validasi gagal.');
        }

        $userId = session()->get('userId');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (! $user || ! password_verify($this->request->getPost('current_password'), $user['password_hash'])) {
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Password lama salah.',
                    'csrfHash' => csrf_hash(),
                ]);
            }

            return redirect()->back()->withInput()->with('error', 'Password lama salah.');
        }

        $userModel->update($userId, [
            'password_hash' => password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT),
        ]);

        if ($isAjax) {
            return $this->response->setJSON([
                'status' => 'ok',
                'message' => 'Password berhasil diubah.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        return redirect()->back()->with('message', 'Password berhasil diubah.');
    }
}
