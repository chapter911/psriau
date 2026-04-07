<?php

/**
 * @property Template $template
 * @property M_AllFunction $M_AllFunction
 */

namespace App\Controllers;

class C_Utility extends BaseController
{
	public function __construct()
	{
		$this->l_auth = new \App\Libraries\L_Auth();
		$this->l_auth->cek_auth();
	}

	public function User()
	{
		$data['data'] = $this->M_AllFunction->Get('vw_mst_user');
		$data['group'] = $this->M_AllFunction->Get('mst_user_group');
		$data['jabatan'] = $this->M_AllFunction->Get('mst_jabatan');
		return $this->template->display('utility/user', $data);
	}

	function getUser()
	{
		$username = $this->request->getPost('username');
		$data = $this->M_AllFunction->Where('vw_mst_user', ['username' => $username]);
		return json_encode($data);
	}

	function saveUser()
	{
		$data = array(
			"username" => $this->request->getPost("username"),
			"nama" => $this->request->getPost("nama"),
			"email" => $this->request->getPost("email"),
			"password" => password_hash($this->request->getPost("password"), PASSWORD_DEFAULT),
			"group_id" => $this->request->getPost("group_id"),
			"jabatan_id" => $this->request->getPost("jabatan_id"),
			"is_active" => $this->request->getPost("is_active"),
			"web_access" => $this->request->getPost("web_access"),
			"android_access" => $this->request->getPost("android_access"),
			"created_by" => session()->get("username")
		);
		$this->M_AllFunction->Inserts("mst_user", $data);
		return redirect()->to('C_Utility/User');
	}

	public function userGroup()
	{
		$data['data'] = $this->M_AllFunction->Get('mst_user_group');
		return $this->template->display('utility/user_group', $data);
	}

	public function getUserGroup()
	{
		$data = $this->M_AllFunction->Where('mst_user_group', "group_id = '" . $this->request->getPost("group_id") . "'");
		echo json_encode($data);
	}

	public function SaveGroup()
	{
		$data = array(
			"group_name" => $this->request->getPost("group_name"),
			"remark" => $this->request->getPost("remark"),
			"is_active" => $this->request->getPost("is_active") !== null ? 1 : 0,
			"created_by" => session()->get("username")
		);
		if ($this->request->getPost("group_id") == 0) {
			$this->M_AllFunction->Inserts("mst_user_group", $data);
		} else {
			$this->M_AllFunction->Updates("mst_user_group", $data, "group_id = '" . $this->request->getPost("group_id") . "'");
		}
		return redirect()->to('C_Utility/userGroup');
	}

	function getGroupAccess()
	{
		$group_id = $this->request->getPost('group_id');
		$data['group_id'] = $group_id;
		$data['lv1'] = $this->M_AllFunction->MenuAkses($group_id, 'menu_lv1');
		$data['lv2'] = $this->M_AllFunction->MenuAkses($group_id, 'menu_lv2');
		$data['lv3'] = $this->M_AllFunction->MenuAkses($group_id, 'menu_lv3');
		return view('utility/user_group_akses', $data);
	}

	function UpdateGroupAccess()
	{
		$group_id = $this->request->getPost('group_id');
		if (count($_POST) > 0) {
			if ((!array_key_exists("99", $_POST) || !array_key_exists("99-02", $_POST)) && $group_id == "1") {
				return redirect()->to('C_Utility/userGroup')->with('failed', 'Akses User Web Administrator Tidak Boleh DiNonAktifkan!');
			} else {
				$menu_id = array_keys($_POST);
				for ($i = 0; $i < count($menu_id); $i++) {
					$add = false;
					$edit = false;
					$delete = false;
					$export = false;
					$import = false;
					$approval = false;
					if (is_array($this->request->getPost($menu_id[$i]))) {
						$add = isset($this->request->getPost($menu_id[$i])['FiturAdd']) ? true : false;
						$edit = isset($this->request->getPost($menu_id[$i])['FiturEdit']) ? true : false;
						$delete = isset($this->request->getPost($menu_id[$i])['FiturDelete']) ? true : false;
						$export = isset($this->request->getPost($menu_id[$i])['FiturExport']) ? true : false;
						$import = isset($this->request->getPost($menu_id[$i])['FiturImport']) ? true : false;
						$approval = isset($this->request->getPost($menu_id[$i])['FiturApproval']) ? true : false;
					}
					$data[$i] = array(
						'group_id' => $group_id,
						'menu_id' => $menu_id[$i],
						'FiturAdd' => $add,
						'FiturEdit' => $edit,
						'FiturDelete' => $delete,
						'FiturExport' => $export,
						'FiturImport' => $import,
						'FiturApproval' => $approval
					);
				}
				$result = $this->M_AllFunction->Deletes('menu_akses', "group_id = '$group_id'");
				if ($result) {
					if ($this->M_AllFunction->InsertBatchs('menu_akses', $data)) {
						return redirect()->to('C_Utility/userGroup')->with('success', 'Berhasil Update Akses.');
					}
				}
			}
		} else {
			if ($group_id == 1) {
				return redirect()->to('C_Utility/userGroup')->with('failed', 'Akses Administrator Tidak Boleh DiNonAktifkan Keseluruhan.');
			} else {
				$this->M_AllFunction->Deletes('menu_akses', "group_id = '$group_id'");
				return redirect()->to('C_Utility/userGroup');
			}
		}
	}

	function LoginHistory()
	{
		$data['data'] = $this->M_AllFunction->CustomQuery('SELECT * FROM trn_login ORDER BY created_date DESC LIMIT 1000');
		return $this->template->display('utility/login_history', $data);
	}
}
