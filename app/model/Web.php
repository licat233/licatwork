<?php
namespace app\model;

class Web extends Common
{
    protected $table = 'web';
    protected $pk = 'id';

    public function get_Url_paas($url)
    {
       $res = get_Url_paas($this->table, $url);
        return $res;
    }
}

