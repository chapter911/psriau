<?php

namespace App\Models;

class M_AllFunction extends BaseModel
{
    function __construct() {
        parent::__construct();
    }

    public function CekTabel($table) {
        return $this->db->tableExists($table);
    }

    public function CountAll($table) {
        return $this->db->table($table)->countAllResults();
    }

    public function CountWhere($table, $where) {
        $this->db->table($table)->where($where);
        return $this->db->table($table)->countAllResults();
    }

    public function Deletes($table, $where) {
        return $this->db->table($table)->delete($where);
    }

    public function DeletesAll($table) {
        return $this->db->table($table)->delete();
    }

    public function Get($table) {
        return $this->db->table($table)->get()->getResult();
    }

    public function Inserts($table, $data) {
        $builder = $this->db->table($table);
        return $builder->insert($data);
    }

    public function InsertBatchs($table, $data) {
        return $this->db->table($table)->insertBatch($data);
    }

    public function InsertGetId($table, $data) {
        $builder = $this->db->table($table);
        $builder->insert($data);
        return $this->db->insertID();
    }

    public function Join($table1, $table2, $on) {
        return $this->db->table($table1)->join($table2, $table1 . "." . $on . "=" . $table2 . "." .  $on)->get()->getResult();
    }

    public function JoinWhere($table1, $table2, $on, $where) {
        return $this->db->table($table1)->join($table2, $table1 . "." . $on . "=" . $table2 . "." .  $on)->where($where)->get()->getResult();
    }

    public function Menu($table) {
        $where = session()->group_id;
        return  $this->db->table('menu_akses')->select('*')
            ->join($table, 'menu_akses.menu_id = ' . $table . '.id')
            ->where('menu_akses.group_id', $where)
            ->orderBy($table . '.ordering', "asc")->get()->getResult();
    }

    public function cekAkses($group_id, $link) {
        $query = "WITH menu AS (
                    SELECT id, link FROM menu_lv2
                    UNION
                    SELECT id, link FROM menu_lv3
                )
                SELECT
                    menu.id,
                    menu.link,
                    CASE WHEN menu_akses.menu_id IS NULL THEN 0 ELSE 1 END AS akses
                FROM menu
                LEFT JOIN menu_akses
                ON menu.id = menu_akses.menu_id
                WHERE menu.link = '$link' AND menu_akses.group_id = $group_id";
        return $this->db->query($query)->getResult();
    }

    public function MenuAkses($group_id, $menu_lv) {
        $query = "SELECT $menu_lv.*,
            CASE WHEN menu_akses.group_id IS NOT NULL THEN True ELSE False END AS active,
            CASE WHEN menu_akses.FiturAdd IS NOT NULL THEN menu_akses.FiturAdd ELSE False END AS FiturAdd,
            CASE WHEN menu_akses.FiturEdit IS NOT NULL THEN menu_akses.FiturEdit ELSE False END AS FiturEdit,
            CASE WHEN menu_akses.FiturDelete IS NOT NULL THEN menu_akses.FiturDelete ELSE False END AS FiturDelete,
            CASE WHEN menu_akses.FiturImport IS NOT NULL THEN menu_akses.FiturImport ELSE False END AS FiturImport,
            CASE WHEN menu_akses.FiturExport IS NOT NULL THEN menu_akses.FiturExport ELSE False END AS FiturExport,
            CASE WHEN menu_akses.FiturApproval IS NOT NULL THEN menu_akses.FiturApproval ELSE False END AS FiturApproval
            FROM $menu_lv
            LEFT JOIN
            (SELECT * FROM menu_akses WHERE group_id = '$group_id') AS menu_akses
            ON $menu_lv.id = menu_akses.menu_id
            ORDER BY ordering";
        return $this->db->query($query)->getResult();
    }

    public function cekOperation($link) {
        $query = "SELECT *
        FROM menu_akses
        WHERE
        group_id = " . session()->get('group_id') . "
        AND
        (
            menu_id = (SELECT id FROM menu_lv2 WHERE link = '" . explode('/', $link)[0] . "')
            OR
            menu_id = (SELECT id FROM menu_lv2 WHERE link = '$link')
            OR
            menu_id = (SELECT header FROM menu_lv3 WHERE link = '" . explode('/', $link)[0] . "')
            OR
            menu_id = (SELECT header FROM menu_lv3 WHERE link = '$link')
        )";
        return $this->db->query($query)->getResult();
    }

    public function Replaces($table, $data) {
        return $this->db->table($table)->replace($data);
    }

    public function Updates($table, $data, $where) {

        return $this->db->table($table)->update($data, $where);
    }

    public function Where($table, $where) {
        return $this->db->table($table)->where($where)->get()->getResult();
    }

    public function CustomQuery($query) {
        return $this->db->query($query)->getResult();
    }

    public function SaveDeletedHistory($table, $controller, $function, $id, $parameter, $data) {
        $data = [
            "table_name"          => $table,
            "controller_name"     => $controller,
            "controller_function" => $function,
            "deleted_id"          => $id,
            "deleted_by"          => session()->get('username'),
            "parameter"           => $parameter,
            "data"                => json_encode($data)
        ];
        return $this->db->table('tb_deleted_history')->insert($data);
    }

    public function SaveEditedHistory($table, $controller, $function, $id, $parameter, $data) {
        $data = [
            "table_name"          => $table,
            "controller_name"     => $controller,
            "controller_function" => $function,
            "edited_id"           => $id,
            "edited_by"           => session()->get('username'),
            "parameter"           => $parameter,
            "data"                => json_encode($data)
        ];
        return $this->db->table('tb_edited_history')->insert($data);
    }

    public function CheckDuplicateLogin() {
        $cek = $this->db->query("SELECT login_id FROM user_login_history
            WHERE username = '" . session()->get('username') .
            "' AND is_logged_in = '1' ORDER BY id DESC LIMIT 1")->getResult();
        return session()->get('login_id') == $cek[0]->login_id;
    }

    function sendMail($to, $cc, $message, $title) {
        $email = service('email');
        $email->setFrom("mail@agungj.com", "agungj@noreply");
        $email->setTo($to);
        $email->setCc($cc);

        $email->setSubject($title);
        $email->setMessage($message);

        $email->send();
    }
}