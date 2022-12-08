<?php
namespace app\controller;

use app\BaseController;
use app\model\User;
use think\facade\View;
use think\facade\Log;

class Fans extends BaseController
{
    function __construct() 
    {
    //   if (strpos($_SERVER['SERVER_NAME'], "cat") == false) die();
      Log::close();
      login_Verify();
    }
  
    public function index()
    {
      View::assign('power', $_SESSION['power']);
      return view('index',['power'=>$_SESSION['power']]);
    }
  
    public function userdata(User $user)
    {
       $data = [];
        $user_json_data = format_json($user, USER_JSON);
        foreach ($user_json_data['data'] as $a) {
          if($a['hide']==1) continue; //过滤amy账号
          if(strpos($a['account'],"@")===false) continue; //过滤和非line@账号
          if($_SESSION['power']==7||$_SESSION['power']==1||$a['office']==$_SESSION['office']){
              $a['color'] = paasColor($a["paas_id"]);
              if($a['status'] === 0) $a['color']=$a['color']."85";
              array_push($data,$a);
           }
        }
        $user_json_data['data']=$data;
        return json($user_json_data);
    }
}
