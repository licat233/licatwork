<?php
namespace app\model;

use think\Model;

class Paas extends Model
{
    protected $table = 'paas';
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

    public function deldata($id)
    {
        deldata($this->table,$id);
    }
}