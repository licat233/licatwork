<?php

namespace app\model;

use think\Model;
use think\facade\Db;

class User extends Model
{
    protected $table = 'user';
    protected $pk = 'id';
    public function getdata($id = null, $name = null)
    {
        $res = getdata($this->table, $id, $name);
        return $res;
    }
    public function adddata($data)
    {
        adddata($this->table,$data);
    }

    public function mdfdata($id,$data)
    {
        mdfdata($this->table,$id,$data);
    }

    public function deldata($ids)
    {
        deldata($this->table,$id);
    }

    public function paas_get_user($paas_id)
    {
        $res = paas_get_user($this->table,$paas_id);
        return $res;
    }
}
