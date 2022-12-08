<?php
// 应用公共文件
use think\facade\Db;
use think\facade\Cache;
use think\Request;
// use app\model\Paas;
// use app\model\Web;
// use app\model\User;
//网站标题
define('WEBTITLE', '享购广告管理系统');
define('WEBHOST', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME']);
define('KEY', "zaqxswcdevfrbgtnhymjukilop");
define('USER_JSON', 'static/json/user.json');
define('PAAS_JSON', 'static/json/paas.json');
define('WEB_JSON', 'static/json/web.json');
define('OFFICE', '412');
//精简查询函数
function getdata($table, $id = null, $name = null)
{
    if (preg_match('/^\d\S?$/', $id)) {
        if ($name) {
            //查询指定ID的指定列数据
            $res = Db::table($table)->where('id', $id)->value($name); //null
        } else {
            //查询指定ID数据
            $res = Db::table($table)->where('id', $id)->find(); //返回一个object
        }
    } else {
        $name = $id;
        if ($name) {
            //查询某一列数据
            $res = Db::table($table)->column($name, 'id'); //返回一个数组

        } else {
            //查询指定表的数据
            switch ($table) {
                case 'user':
                    $res = Db::table($table)->order(['status' => 'desc', 'paas_id' => 'asc'])->select()->toArray(); //返回一个对象转化为数组
                    break;
                case 'paas':
                    $res = Db::table($table)->order('name', 'asc')->select()->toArray(); //返回一个对象转化为数组
                    break;
                default:
                    $res = Db::table($table)->select()->toArray(); //返回一个对象转化为数组
            }
        }
    }
    return $res;
}


////精简添加函数
function adddata($table, $data) //$data必须为二维数组
{
    Db::name($table)->insertAll($data);
}

// //精简修改函数
function mdfdata($table, $id, $data)
{
    Db::name($table)->where('id', $id)->update($data); //$data必须为一维数组
}

// ///精简批量更新函数
// function batchmdfdata($data)
// {
//     Db::save($data);
// }


/////精简删除函数
function deldata($table, $id)
{
    Db::table($table)->delete($id); //$id必须为一维数组
}

////查询web_url所在paas的code
function get_code($paas_id,$codename)
{
    $res = Db::table('paas')->where('id',$paas_id)->value($codename);
    return $res;
}

///查询web_url的paas_id
function get_Url_paas($table, $url)
{
    $res = Db::table($table)->where('url', $url)->value('paas_id'); //返回一个整数
    return $res;
}

///查询web_url的js_plugin
function get_url_data($url)
{
    $res = Db::table('web')->where('url', $url)->find();//SELECT * FROM `web` WHERE  `url` = 'www.annatw.com' LIMIT 1
    return $res;
}

//查询web_url是否存在
function isset_web_url($url)
{
    $res = Db::table('web')->where('url', $url)->value('id'); //返回一个布尔类型
    return is_numeric($res);
}


////查询指定paas_id的user
function paas_get_user($table, $paas_id)
{
    $res = Db::table($table)->where([['paas_id','=', $paas_id],['status','=',1],['hide','=',0]])->select()->toArray(); //返回一个对象转化为数组
    return $res;
}

////查询指定user_id的status
function userid_get_status($userid)
{
    $res = DB::table("user")->where('id',$userid)->value('status');//value 方法查询结果不存在，返回 null,否则返回一个整数
    return $res;
}

////查询指定user_id的paas_id
function userid_get_paasid($userid)
{
    $res = DB::table("user")->where('id',$userid)->value('paas_id');//value 方法查询结果不存在，返回 null,否则返回一个整数
    return $res;
}

////查询指定user_id的last_paas_id
function get_lastpaasid($userid)
{
    $res = DB::table("user")->where('id',$userid)->value('last_paas_id');//value 方法查询结果不存在，返回 null,否则返回一个整数
    return $res;
}

////平台ID转化平台名称
function idTopaasname($id)
{
    $res = Db::table('paas')->where('id', $id)->value('name'); //null
    if($res === null) $res = "已删除的平台";
    return $res;
}

////平台名称转化平台ID
function paasnameToid($paasname)
{
    $res = Db::table('paas')->where('name', $paasname)->value('id'); //null
    return $res;
}

////用户ID转化用户名
function idToUname($userid)
{
    $res = Db::table('user')->where('id', $userid)->value('name'); //null
    return $res;
}

////用户ID转化用户account
function idToUaccount($userid)
{
    $res = Db::table('user')->where('id', $userid)->value('account'); //null
    return $res;
}

//解析状态值
function getstatus($status)
{
    switch ($status) {
        case 0:
            return '离线';
            break;
        case 1:
            return '在线';
            break;
        default:
            return '离线';
    }
}

//解析状态值
function getstatus_id($status)
{
    switch ($status) {
        case '离线':
            return 0;
            break;
        case '在线':
            return 1;
            break;
        default:
            return 0;
    }
}

////////json加密/////////
function lock_json($txt, $key)
{
    $txt = $txt . $key;
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+";
    $nh = rand(0, 64);
    $ch = $chars[$nh];
    $mdKey = md5($key . $ch);
    $mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
    $txt = base64_encode($txt);
    $tmp = '';
    $i = 0;
    $j = 0;
    $k = 0;
    for ($i = 0; $i < strlen($txt); $i++) {
        $k = $k == strlen($mdKey) ? 0 : $k;
        $j = ($nh + strpos($chars, $txt[$i]) + ord($mdKey[$k++])) % 64;
        $tmp .= $chars[$j];
    }
    return urlencode(base64_encode($ch . $tmp));
}

///////json解密//////////
function unlock_json($txt, $key)
{
    $txt = base64_decode(urldecode($txt));
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+";
    $ch = $txt[0];
    $nh = strpos($chars, $ch);
    $mdKey = md5($key . $ch);
    $mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
    $txt = substr($txt, 1);
    $tmp = '';
    $i = 0;
    $j = 0;
    $k = 0;
    for ($i = 0; $i < strlen($txt); $i++) {
        $k = $k == strlen($mdKey) ? 0 : $k;
        $j = strpos($chars, $txt[$i]) - $nh - ord($mdKey[$k++]);
        while ($j < 0) $j += 64;
        $tmp .= $chars[$j];
    }
    return trim(base64_decode($tmp), $key);
}

/////更新json文件/////
function update_json($item, $url)
{
    ///当前已去除json文件
    // $jsonData = json_encode(format_json($item, $url));
    // file_put_contents($url, $jsonData);
}

/////格式化json数据,返回数组/////
function format_json($item, $url = null)
{
    Cache::clear();
    $result = $item->getdata();
    if ($url === USER_JSON) {
        foreach ($result as $k => $row) {
            $result[$k]['paas_name'] = idTopaasname($row['paas_id'])."<=".idTopaasname($row['last_paas_id']);
            $result[$k]['prob_PCT'] = prob_PCT($result[$k]['id']);
            // $result[$k]['status']=getstatus($row['status']);
        }
    }
    $json_data['code'] = 0;
    $json_data['msg'] = "";
    $json_data['count'] = count($result);
    $json_string = $result;
    // $json_string_lock =lock_json($json_string,KEY);
    $json_data['data'] = $json_string;
    // return json_encode($json_data);
    return $json_data;
    // $res = file_get_contents($json_url);
    // $res_unlock = unlock_json($res,KEY);
}

////获取json文件里的数据////
function getdata_json($json_url)
{
    $res = file_get_contents($json_url);
    return $res;
}

/////检查json文件，若不存在则创建
function json_file_status($user = null, $paas = null, $web = null)
{

    ///当前已去除json文件
    // if (!file_exists(USER_JSON) && $user !== null) {
    //     update_json($user, USER_JSON);
    // }

    // if (!file_exists(PAAS_JSON) && $paas !== null) {
    //     update_json($paas, PAAS_JSON);
    // }

    // if (!file_exists(WEB_JSON) && $web !== null) {
    //     update_json($web, WEB_JSON);
    // }
}

/////判断是否已登录//////
function login_Verify()
{
    session_start();
    if (!isset($_SESSION['username']) && empty($_SESSION['username']) && !isset($_SESSION['islogin']) && empty($_SESSION['islogin'])) {
        echo "<script>alert('您未登录，请先登录！');</script>";
        header('refresh:0;url=' . WEBHOST . '/login');
        die();
    } elseif ($_SESSION['islogin'] !== 1 && $_SESSION['islogin'] !== 3) {
        echo "<script>alert('您未登录，请先登录！');</script>";
        header('refresh:0;url=' . WEBHOST . '/login');
        die();
    } elseif ($_SESSION['islogin'] == 3 && $_SESSION['mykey'] == KEY) {
        header('refresh:0;url=' . WEBHOST . '/lock');
        die();
    } elseif (!isset($_SESSION['mykey']) || $_SESSION['mykey'] !== KEY) {
        echo "<script>alert('您的登录已失效，请重新登录！');</script>";
        header('refresh:0;url=' . WEBHOST . '/login');
        die();
    }
}

function isLogin()
{
    session_start();
    return isset($_SESSION['username'])&&isset($_SESSION['islogin'])&&isset($_SESSION['islogin'])&&$_SESSION['islogin']===1&&isset($_SESSION['mykey'])&&$_SESSION['mykey'] === KEY;
}

////计算其它在线人数的总幸运值
function paas_othertotal_prob($paas_id,$id)
{
    $prob_arr = Db::table('user')
        ->where([
            ['paas_id', '=', $paas_id], ['status', '=', 1]
        ])
        ->column('prob', 'id');   //column方法查询结果不存在，返回空数组
    $total_prob = array_sum($prob_arr);
    $id_prob = Db::table('user')->where('id',$id)->value('prob');
    if($id_prob==null){$id_prob = 0;}
    $other_total_prob = $total_prob - $id_prob;
    return $other_total_prob;
}

////计算平台在线人数的总幸运值
function paas_total_prob($paas_id)
{
    $prob_arr = Db::table('user')
        ->where([
            ['paas_id', '=', $paas_id], ['status', '=', 1]
        ])
        ->column('prob', 'id');   //column方法查询结果不存在，返回空数组
    $total_prob = array_sum($prob_arr);
    return $total_prob;
}

///计算某业务号的概率
function prob_PCT($id)
{
    $userdata = Db::table('user')->where('id', $id)->find(); //find方法查询结果不存在，返回 null，否则返回结果数组
    $prob_PCT = 0;
    if ($userdata['status'] == 0) {
        return $prob_PCT;
    }
    $paas_id = $userdata['paas_id'];
    $total_prob = paas_total_prob($paas_id);
    if ($total_prob - $userdata['prob'] == 0 ) {
       Db::name('user')->where('id', $id)->update(['prob'=>'100']); //$data必须为一维数组
       return 100;
    }
    if ($total_prob == 0) {
         $prob_PCT = 0;
    } else {
         $prob_PCT = ($userdata['prob'] / $total_prob) * 100;
    }
    return $prob_PCT;
    
}

////根据概率计算幸运值
function toProb($prob_PCT,$paas_id,$id)
{
    if($prob_PCT >= 100 || paas_othertotal_prob($paas_id,$id) <= 0|| $prob_PCT == 0)
    {
        $prob = 100;
    }else{
        $a = paas_othertotal_prob($paas_id,$id)*$prob_PCT; //paas_othertotal_prob可能为 0 
        $b = 100 - $prob_PCT;
        $prob=$a/$b; 
    }
    return $prob;
}

//如果不是我方域名，有可能是我方网站被盗用，记录其url
function logURL($url)
{
  //$filename = './log/'.date('Y-m-d',time()).'_LogIP.txt';
  $filename = './log/logURL.txt';
  //$ip = getHostIp();
  if($fp = @fopen($filename,'a+')) {
    if(flock($fp,LOCK_EX)) {
      rewind($fp);
      $ipdata = date('Y-m-d H:i:s',time()).'==>>'.$url."\n";
      @fputs($fp,$ipdata);
      flock($fp,LOCK_UN);
      fclose($fp);
    }
  }
}

function getHostIp()
{
//tp内置函数获取客户端ip
$IP = request()->ip();
return $IP;
}

//控制台登录日志
function loginLog($loginstatus,$logininfo)
{
  $filename = './log/loginLog.txt';
  $ip = getHostIp();
  if($fp = @fopen($filename,'a+')) {
    if(flock($fp,LOCK_EX)) {
      rewind($fp);
      $logindata = "\n".date('Y-m-d H:i:s',time()).'==>>'.$ip.'==>>'.$loginstatus.'==>>'.$logininfo;
      @fputs($fp,$logindata);
      flock($fp,LOCK_UN);
      fclose($fp);
    }
  }
}


//业务号平台切换日志
function lineSwitchPaas($userid,$lastpaasid,$newestpaasid)
{
  $lineOffice = Db::table('user')->where('id', $userid)->value('office');
  $linename = idToUname($userid);
  $account = idToUaccount($userid);
  $lastpaas = idTopaasname($lastpaasid);
  $newestpaas = idTopaasname($newestpaasid);
  /*
  $switchdata = $time.' 【'.$linename.'- '.$account.'】 '.$lastpaas.' ==>> '.$newestpaas."\n";
  $filename = './log/switchLog.txt';
  if($fp = @fopen($filename,'a+')) {
    if(flock($fp,LOCK_EX)) {
      rewind($fp);
      @fputs($fp,$switchdata);
      flock($fp,LOCK_UN);
      fclose($fp);
    }
  }
  */
  $content = $lastpaas.' ==>> '.$newestpaas;
  $data = ["name"=>$linename,"account"=>$account,"content"=>$content,"office"=>$lineOffice,"userid"=>$userid];
  Db::name('user_switch_log')->save($data);
}

//业务号上下线日志
function lineSwitchStatus($userid,$paasid,$status)
{
  $lineOffice = Db::table('user')->where('id', $userid)->value('office');
  //$paas_id = userid_get_paasid($userid);
  $paasname = idTopaasname($paasid);
  $linename = idToUname($userid);
  $account = idToUaccount($userid);
  $statusname = $status == 1 ? '上线 ↑':'下线 ↓';
  /*$switchdata = $time.' 【'.$linename.'- '.$account.'】 '.$paasname.'--> '.$statusname."\n";
  $filename = './log/switchLog.txt';
  if($fp = @fopen($filename,'a+')) {
    if(flock($fp,LOCK_EX)) {
      rewind($fp);
      @fputs($fp,$switchdata);
      flock($fp,LOCK_UN);
      fclose($fp);
    }
  }*/
  $content = $paasname.'--> '.$statusname;
  $data = ["name"=>$linename,"account"=>$account,"content"=>$content,"office"=>$lineOffice,"userid"=>$userid];
  Db::name('user_switch_log')->save($data);
}

function lineSwitchProb($userid,$now_prob_PCT,$prob_PCT)
{
  $lineOffice = Db::table('user')->where('id', $userid)->value('office');
  $linename = idToUname($userid);
  $account = idToUaccount($userid);
  $content = round($now_prob_PCT,2).'% ↑==>> '.round($prob_PCT,2).'%';
  if($now_prob_PCT>$prob_PCT)
  {
    $content = round($now_prob_PCT,2).'% ↓==>> '.round($prob_PCT,2).'%';
  }
   $data = ["name"=>$linename,"account"=>$account,"content"=>$content,"office"=>$lineOffice,"userid"=>$userid];
   Db::name('user_switch_log')->save($data);
}

function lineProbRecover($userid)
{
  $lineOffice = Db::table('user')->where('id', $userid)->value('office');
  $linename = idToUname($userid);
  $account = idToUaccount($userid);
  $now_prob_PCT = prob_PCT($userid);
  $content = round($now_prob_PCT,2).'% ==>> 概率重置';
  $data = ["name"=>$linename,"account"=>$account,"content"=>$content,"office"=>$lineOffice,"userid"=>$userid];
  Db::name('user_switch_log')->save($data);
}

//用户落地页留言
function writeComment($comment)
{
  $filename = './log/usercomment.txt';
  if($fp = @fopen($filename,'a+')) {
    if(flock($fp,LOCK_EX)) {
      rewind($fp);
      $commentdata = date('Y-m-d H:i:s',time()).'==>>'.$comment."\n";
      @fputs($fp,$commentdata);
      flock($fp,LOCK_UN);
      fclose($fp);
    }
  }
}

//判断是否为移动端访问
function isMobile() { 
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
      return true;
    } 
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset($_SERVER['HTTP_VIA'])) { 
      // 找不到为flase,否则为true
      return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    } 
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
      $clientkeywords = array('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile','MicroMessenger'); 
      // 从HTTP_USER_AGENT中查找手机浏览器的关键字
      if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
        return true;
      } 
    } 
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) { 
      // 如果只支持wml并且不支持html那一定是移动设备
      // 如果支持wml和html但是wml在html之前则是移动设备
      if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
        return true;
      } 
    } 
    return false;
  }

//根据平台ID获取颜色
function paasColor($pid)
{
    $color = "#D0CECE";
    switch ((int)$pid % 9) {
        case 1:
            $color = "#FF9900";
            break;
        case 2:
            $color = "#CC0033";
            break;
        case 3:
            $color = "#0099CC";
            break;
        case 4:
            $color = "#666666";
            break;
        case 5:
            $color = "#009933";
            break;
        case 6:
            $color = "#CC0066";
            break;
        case 7:
            $color = "#009999";
            break;
        case 8:
            $color = "#003399";
            break;
        case 9:
            $color = "#FF6666";
            break;
        case 0:
            $color = "#663399";
            break;
        default:
            $color = "#D0CECE";
    }
    return $color;
}


//禁止游客
function banVisitor()
{
  /*
  if (!session_id()) session_start();
  if (!key_exists('power',$_SESSION)||(key_exists('power',$_SESSION)&&$_SESSION['power']==4)){
    echo '你没有权限访问';
    die();
  }
  */
}

//获取权限值
function getPower()
{
if (!session_id()) session_start();
if(isset($_SESSION['power']))
{
  return $_SESSION['power'];
}else{
  return 0;
}
}