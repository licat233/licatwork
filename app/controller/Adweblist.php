<?php

namespace app\controller;

use app\BaseController;
use think\facade\View;

class Adweblist extends BaseController
{
  public static $viewislogin = 0;
  
  function __construct()
  {
    session_start();
    if((isset($_SESSION['viewislogin']) && $_SESSION['viewislogin'] == 1)||(isset($_SESSION['islogin']) && $_SESSION['islogin'] == 1)) self::$viewislogin = 1;
    
  }
  
  function index()
  {
    if(self::$viewislogin == 1){
      return view("index/adweblist");
    }
    return view("lock/view-lock");
  }
}