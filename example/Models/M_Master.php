<?php

namespace App\Models;

class M_Master extends BaseModel
{
    protected $table = 'vw_wilayah_administratif';
    protected $primaryKey = 'id'; // Change 'id' to your actual primary key if different
    protected $allowedFields = []; // Add allowed fields if you want to use insert/update

    public function getDataKelurahan()
    {
        $builder = $this->builder();

        $builder->limit($this->request->getPost('length'), $this->request->getPost('start'));

        return $builder->get()->getResult();
    }

    public function getTotalFiltered()
    {
        return $this->countAllResults();
    }

    public function getTotal()
    {
        $db = \Config\Database::connect();
        return $db->table('vw_wilayah_administratif')->countAllResults();
    }
}
