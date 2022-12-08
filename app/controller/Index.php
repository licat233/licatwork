<?php
namespace app\controller;

use app\BaseController;
use app\model\User;
use app\model\Safe;
use think\facade\View;
use think\facade\Db;

class Index extends BaseController
{
    function __construct()
    {
      login_Verify();
    }

    public function index(User $user)
    {
      $device="pc";
      if(isMobile()){
        $device = "mb";
      }
      if(!isset($_SESSION['power'])){
        echo "<div style='width:100vw;height:100vh;text-align:center;display:flex;justify-content:center;align-items: center;'><h1 style='display:block'>请清空浏览器缓存，再重新访问</h1></div>";
        return;
      }
      
      $logarr = file('./log/UpdateLog.txt');//获取出来的是數組类型
      return view('index',['demo'=>'这里是首页','updatelog'=>$logarr,'power'=>$_SESSION['power'],'device'=>$device]);
    }

    public function logout()
    {
      if(isset($_POST['logout'])&&$_POST['logout']==true)
      {
        $_SESSION=array();
      }
    }

    public function log()
    {
      //原生方法
      // $log = fopen('log.txt','r');
      // while(!feof($log)){
      //   echo fgets($log);
      // }
      // fclose($log);

      ///TP方法
      $log = file_get_contents('log.txt');//获取出来的是字符串类型
      $rep = str_replace("\r\n",'<br/>',$log);
      // $con = explode(',',$rep);
      return $rep;
    }

    public function hello()
    {
      echo "hello";
    }
    
  function switchweb()
  {
    if (!$_POST) {
      return 0;
    }
    $safe = new Safe();
    $tz_code = $_POST['tz_status'];
    $tz_url = $_POST['tz_url'];
    $data = [
                "tz_code" => $status,
                "tz_url" => $datetime
            ];
    $safe->mdfdata(1, $data);
    return 1;
  }
  
  function getswitchdata()
  {
    $safe = new Safe();
    $data = $safe->getdata(1); //返回一个object
    return json($data);
  }
  
  function newredirect()
  {
    return redirect('http://tools.jb51.net/static/api/js_kexue_jsq/index.html');
    /*
    if (isset($_GET['url'])) {
       $url = $_GET['url'];
       $ch = curl_init();
       $timeout = 5;
       curl_setopt($ch, CURLOPT_URL, $url);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
       $data = curl_exec($ch);
       curl_close($ch);
       echo $data;
    }*/
    
  }
  
  function adweblist()
  {
    return view('adweblist');
  }
  
  function newestlist()
  {
    return view("newestlist");
  }
}
