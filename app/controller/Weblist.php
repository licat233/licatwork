<?php
namespace app\controller;

use app\BaseController;
use app\model\Paas;
use app\model\Web;
use think\facade\View;
class Weblist extends BaseController
{
    function __construct()
    {
        login_Verify();
        banVisitor();
    }
    public function index(Paas $paas)
    {
        $paas_arr = format_json($paas);
        foreach($paas_arr['data'] as $row){
            $namearr[$row['id']] = $row['name'];
        }

        View::assign(
            ['paas_name' => $namearr]
        );

        return view();
    }

    public function webdata(Web $web)
    {
        $webdata = format_json($web);
        foreach ($webdata['data'] as $k => $row) {
            $webdata['data'][$k]['paas_name'] = idTopaasname($row['paas_id']);
        }
        return json($webdata);
    }
}