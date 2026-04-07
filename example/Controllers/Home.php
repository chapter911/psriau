<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index() {
        if(session()->get('username')) {
            return redirect()->to('/Dashboard');
        } else {
            return view('login');
        }
    }

    public function login()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $this->M_AllFunction->Where('mst_user', "username = '$username'");
        if (empty($user)) {
            return redirect()->to('/');
        } else {
            if (password_verify($password, $user[0]->password)) {
                $session = [
                    'username'   => $user[0]->username,
                    'nama'       => $user[0]->nama,
                    'group_id'   => $user[0]->group_id,
                    'jabatan_id' => $user[0]->jabatan_id
                ];
                session()->set($session);

                $data = array(
                    "username"      => $user[0]->username,
                    "is_logged_in"  => 1
                );
                $this->M_AllFunction->Inserts("trn_login", $data);

                return redirect()->to('/Dashboard');
            } else {
                $data = array(
                    "username"      => $user[0]->username,
                    "is_logged_in"  => 0
                );
                $this->M_AllFunction->Inserts("trn_login", $data);
                return redirect()->to('Home')->with('notification', 'Invalid username or password');
            }
        }
    }

    public function logout() {
        session()->destroy();
        return redirect()->to('/');
    }
}
