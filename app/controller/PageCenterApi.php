<?php

namespace app\controller;
use app\BaseController;
use think\facade\Db;

class PageCenterApi extends BaseController
{
    private $ourip;
    private $remoteip;
  
    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Request-Methods:GET, POST");
        $this->ourip = ["150.109.64.110","150.109.61.90"];
        $this->remoteip = $_SERVER['REMOTE_ADDR'];
    }

    function index()
    {
        return  json([
            'status' => 0,
            'msg' => '你没有权限访问',
        ]);
    }

    /* 预览窗口 */
    public function viewLandingpage($id)
    {
      if($this->ipVerify()||isLogin()){
        echo Db::table('landingpage')
            ->where('id', $id)
            ->value('html');
      }else{
        return  json([
            'status' => 0,
            'msg' => '你没有权限访问',
        ]);
      }
    }

    public function viewAuditpage($id)
    {
      if($this->ipVerify()||isLogin()){
        echo Db::table('auditpage')
            ->where('id', $id)
            ->value('html');
      }else{
        return  json([
            'status' => 0,
            'msg' => '你没有权限访问',
        ]);
      }
    } 
    
    private function ipVerify()
    {
      return true;
      //return in_array($this->remoteip,$this->ourip);
    }
}
