<?php

namespace app\controller;

use app\BaseController;
use app\model\Web;
use app\model\User;
use think\facade\Log;

class Getdata extends BaseController
{
    function __construct() 
    {
    //   if (strpos($_SERVER['SERVER_NAME'], "cat") == false) {
    //       die();
    //     }
      Log::close();
    }
  
    function line()
    {
        header("Access-Control-Allow-Origin: *"); //解决远程无法访问控制器问题
        if ($_GET && isset($_GET['mm']) && $_GET['mm'] === "qsHPLIuO6O9cxQ8Z9aaa") {
            $web = new Web();
            $user = new User();
            $web_url = $_GET['web'];
            $amy = "amyamy.net";
          
            if (strpos($web_url, 'www') !== false) {
                $web_urls = str_replace("www.", "", $web_url);
            } else {
                $web_urls = 'www.' . $web_url;
            }

            $web_paas = $web->get_Url_paas($web_url); //获取url所在的平台ID
            if (!is_numeric($web_paas)) {
                $web_paas = $web->get_Url_paas($web_urls);
                if (!is_numeric($web_paas) && strpos($web_url, $amy) == false) { //如果链接不存在数据库，且不属于amy官网
                  logURL($web_urls);
                  //当code = 5 时，将跳转至我们的落地页
                    $data = [
                        'code'=>1,
                        'tz_url' => "https://www.skincare-info.com/news"
                    ];
                    return json($data);
                    die();
                }
            }
          $data = ['code'=> 1];
                    return json($data);
                    die();
        } else {
            return "<h1><center>嗨！别来无恙啊！</center></h1>" . "<script>console.log('嗨！别来无恙啊！')</script>";
        }
    }
}
