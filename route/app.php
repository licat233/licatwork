<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::post('verify/:name', 'websafe/i');

//restFul Api
Route::get('page_center/landingpage/:id/view', 'page_center_api/viewLandingpage');
Route::get('page_center/auditpage/:id/view', 'page_center_api/viewAuditpage');

Route::get('page_center/project/:id/landingpage', 'page_center/projectLandingpage');
Route::get('page_center/project/:id/auditpage', 'page_center/projectAuditpage');

Route::any('page_center/project/[:id]', 'page_center/project');
Route::any('page_center/landingpage/[:id]', 'page_center/landingpage');
Route::any('page_center/auditpage/[:id]', 'page_center/auditpage');