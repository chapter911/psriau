<?php

namespace App\Models;
use CodeIgniter\Model;

class BaseModel extends Model {
    protected $db;
    protected $request;
    function __construct(){
        $this->db = db_connect();
        $this->request = \Config\Services::request();
    }
}

