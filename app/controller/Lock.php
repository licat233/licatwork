<?php

namespace app\controller;

use app\BaseController;
use think\facade\View;

class Lock extends BaseController
{

    public function index()
    {
        session_start();
    if(!isset($_SESSION['username']) && empty($_SESSION['username']) &&!isset($_SESSION['islogin']) && empty($_SESSION['islogin']))
    {
        echo "<script>alert('您未登录，请先登录！');</script>";
        header('refresh:0;url=./login');
        die();
    }elseif($_SESSION['islogin']!==1 && $_SESSION['islogin'] !==3){
        echo "<script>alert('您未登录，请先登录！');</script>";
        header('refresh:0;url=./login');
        die();
    }elseif($_SESSION['islogin']==1){
        $_SESSION['islogin']=3;
    }
    return view('index', [
            'username' => $_SESSION['username'], 
            'office' => OFFICE
        ]);
    }
}
