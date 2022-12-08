<?php
namespace app\controller;
use app\BaseController;
use app\model\Admin;
use think\facade\Db;
use think\facade\Log;

class Demo extends BaseController
{
    function __construct() 
    {
      header("Access-Control-Allow-Origin: *"); //解决远程无法访问控制器问题
      Log::close();
    }
  
    public function index()
    {
      /*$info = $admin->officedata('412');
      //var_dump($info);
      print_r($info);
      $ip = getHostIp();
      print_r($ip);*/
      echo "hi!";
    }
    private function skintest()
    {
        if (!$_POST) {
            return 'only post';
        }
        $origin = $_POST['origin'];
        $paper = $_POST['paper'];
        $code = substr(md5(time().mt_rand(1,1000000)),8,8);
        $data = [
            'code'=>$code,
            'paper'=>$paper,
            'origin'=>$origin,
        ];
        Db::name('skintestpaper')->save($data);
        $res = [
            'code'=>$code,
            'msg'=> "successful test"
            ];
        return json($res);
    }
    
    public function searchskintest()
    {
        if (!$_GET) {
            return 'error';
        }
        $code = $_GET['code'];
        $res = Db::table('skintestpaper')->where('code', $code)->find();
        if($res==null) {
            return json(['result'=>null,'msg'=>'沒有找到！']);
        }
        return json(['result'=>$res,'msg'=>'找到了！']);
    }
}
