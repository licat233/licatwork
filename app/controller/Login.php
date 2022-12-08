<?php
namespace app\controller;
use app\BaseController;
use think\facade\View;
use think\facade\Db;
use app\model\Admin;
// use think\facade\Session;

class Login extends BaseController {
  public static $myip = array("220.246.113.60","113.66.55.99");
  
	public function index() {
	    if(isLogin()){
	        return header('refresh:0;url=' . WEBHOST . '/');
	    }
		return view('index',['demo'=>'这里是登录页']);
	}
	
	public function simple() {
	    if (isset($_POST['simplesubmit'])) { //登录请求
            $password = $_POST['password']; 
            if ($password === "xianggou888888") {
                // Session::set('simplelogin','true');
                $_SESSION['simplelogin'] = true;
                echo"<script>alert('登录成功！');</script>";
                header('refresh:0;url=/shop_order');
                die();
            }else{
                echo"<script>alert('登录失败！');</script>";
            }
        }
		return view('simple-login',['demo'=>'这里是登录页']);
	}
  
  //平台登录接口
	public function verify(Admin $admin) {
        //$data1=input('post.');
        //print_r($data1);
        //die();
		$ip = getHostIp();
		$login_status = 0;
		$login_office = trim($_POST['office']);
		$login_name = trim($_POST['username']);
		$login_psw = trim($_POST['password']);
		//登录错误次数验证，最多10次
		$failed_count = Db::table('login_log')->where([['login_ip','=', $ip],['main_mark','=',1]])->value('error_count');
		if ($failed_count != null && $failed_count > 10) {
			return 4;
		}
		//登录信息验证
        $officeData = $admin->officeData($login_office);
        if(empty($officeData)){
          return 0;
        }
        if(($login_name === $officeData['username'])&&($login_psw===$officeData['password'])){
            $timer = 9 * 3600; //早上9:30上班，到下午18:30下班，9个小时
            session_set_cookie_params($timer);
            session_start();
        //   Session::set('username',$login_name);
        //   Session::set('islogin',1);
        //   Session::set('mykey',KEY);
        //   Session::set('power',$officeData['power']);
        //   Session::set('office',$officeData['office']);
          
          $_SESSION['username'] = $login_name;
          $_SESSION['islogin'] = 1;
          $_SESSION['mykey'] = KEY;
          $_SESSION['power'] = $officeData['power'];
          $_SESSION['office'] = $officeData['office'];
          $login_status = 1;
        }
        /* 旧的方案
		if(isset($_POST['office']) && (trim($_POST['office']) == '412'||trim($_POST['office']) == '707')) {
			$admindata = $admin->getdata('1');
			if($admindata['username'] == trim($_POST['username'])&&$admindata['password'] == trim($_POST['password'])) {
				session_start();
				$_SESSION['username'] = trim($_POST['username']);
				$_SESSION['islogin'] = 1;
				$_SESSION['mykey'] = KEY;
				$login_status = 1;
			}
		}
        */
		//白名单
		if (($ip === '45.77.17.164')||($ip === '220.246.161.68')) {
			return $login_status;
		}
		//记录登录记录
		$login_info = '【office:'.$login_office.'】【name:'.$login_name.'】【password:'.$login_psw.'】';
		$loginData = ['login_ip'=> $ip,'login_info' => $login_info];
		if ($failed_count === null) {
			$loginData['main_mark'] = 1;
		}
		Db::name('login_log')->save($loginData);
		if ($login_status == 1) {
			Db::table('login_log') ->where([['login_ip','=', $ip],['main_mark','=',1]])->inc('success_count')->update();
			return 1;
		} else {
			Db::table('login_log') ->where([['login_ip','=', $ip],['main_mark','=',1]])->inc('error_count')->update();
            //loginLog('Login failed',$login_info);
            $errorData = ['login_ip'=> $ip,'login_info' => $login_info];
            Db::name('login_error_log')->save($errorData);
			return 0;
		}
	}
  
  //游客登录接口
  public function viewVerify(Admin $admin) {
		$ip = getHostIp();
		$login_status = 0;
		$login_psw = trim($_POST['password']);
		//登录错误次数验证，最多10次，如果是多次错误的，则不允许再登录
		$failed_count = Db::table('login_log')->where([['login_ip','=', $ip],['main_mark','=',1]])->value('error_count');
		if (($failed_count != null && $failed_count > 10) || !in_array(getHostIp(),self::$myip)) {
			return 4;
		}
		//登录信息验证
        $userData = $admin->viewData();
        if(in_array($login_psw,$userData)){
          session_start();
        //   Session::set('viewislogin',1);
          $_SESSION['viewislogin'] = 1;
          $login_status = 1;
        }
       
		//白名单
		if (($ip === '45.77.17.164')||($ip === '220.246.161.68')) {
			return $login_status;
		}
		//记录登录记录
		$login_info = '【登录地址：'.$_SERVER['PHP_SELF'].'】【password:'.$login_psw.'】';
		$loginData = ['login_ip'=> $ip,'login_info' => $login_info];
		if ($failed_count === null) {
			$loginData['main_mark'] = 1;
		}
		Db::name('login_log')->save($loginData);
		if ($login_status == 1) {
			Db::table('login_log') ->where([['login_ip','=', $ip],['main_mark','=',1]])->inc('success_count')->update();
			return 1;
		} else {
			Db::table('login_log') ->where([['login_ip','=', $ip],['main_mark','=',1]])->inc('error_count')->update();
            //loginLog('Login failed',$login_info);
            $errorData = ['login_ip'=> $ip,'login_info' => $login_info];
            Db::name('login_error_log')->save($errorData);
			return 0;
		}
	}
}