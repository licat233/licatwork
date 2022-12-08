<?php
namespace app\model;

use think\Model;
use think\facade\Db;

class Admin extends Model
{
    protected $table = 'admin';
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
    
    public function officeData($office)
    {
       return Db::table($this->table)->getByOffice($office);
    }
    
    public function viewData()
    {
       return Db::table($this->table)->column('password');
    }
}