<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;

class Skinpaper extends BaseController
{
    public function index()
    {
        return view('index');
    }
}