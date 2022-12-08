<?php
namespace app\controller;

use app\BaseController;
use app\model\Paas;
use app\model\Web;
use think\facade\View;

class Adminer extends BaseController
{
    function __construct() 
    {
    //   if (strpos($_SERVER['SERVER_NAME'], "cat") == false) {
    //       die();
    //     }
      login_Verify();
    }

    function index()
    {
        return View('index');
    }
  
}
