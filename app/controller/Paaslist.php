<?php
namespace app\controller;

use app\BaseController;
use app\model\Paas;
use app\model\Web;
use think\facade\View;
use think\Request;
class Paaslist extends BaseController
{
    function __construct()
    {
        login_Verify();
        banVisitor();
    }
    
    public function index(Paas $paas,Web $web)
    {
        json_file_status(null,$paas,$web);
        // $paas_arr = json_decode(getdata_json(PAAS_JSON),true);
        // $web_arr = json_decode(getdata_json(WEB_JSON),true);
        $paas_arr = format_json($paas);
        $web_arr = format_json($web);

        foreach($paas_arr['data'] as $row){
            $namearr[$row['id']] = $row['name'];
        }

        foreach($paas_arr['data'] as $key=>$row){
            $paas_arr['data'][$key]['color'] = paasColor($row['id']);
            $paas_arr['data'][$key]['web']=[];
            foreach($web_arr['data'] as $v){
                if($v['paas_id']==$row['id']){
                    $paas_arr['data'][$key]['web'][]=$v;
                }
            }
        }
        View::assign(
            ['paass' => $paas_arr['data'],
            'paas_name' => $namearr
            ]
        );

        // print_r($paas_arr['data']);
        // die();
      return view('index');
    }

    public function webdata(Web $web)
    {
        $web_arr = format_json($web);
        return json($web_arr);
    }

    public function paasdata(Paas $paas)
    {
        $web_arr = format_json($paas);
        return json($web_arr);
    }

    public function addurl(Web $web)
    {
        if($_POST){
          if(isset_web_url($_POST['url'])!==false)
            {
                return 3;
                die();
            }
           
          // $data[] = $request->param();
           $data = [
                [
                'paas_id'=>$_POST['paas_id'],
                'url'=>$_POST['url'],
                'js_plugin'=>$_POST['plugin']
                ]
            ];
           $web->adddata($data);
        //    update_json($web,WEB_JSON);
           $post="Addurl success";
        }else{
            $post = "Url Not Null";
        }
        return $post;
    }

    public function editurl(Web $web)
    {
        if($_POST){
            $id = $_POST['id'];
            if($_POST['paas_id'] === 'no'){
            $data = [
                'url'=>$_POST['url'],
                'js_plugin'=>$_POST['js_plugin']
            ];}else{
              $data = [
               'paas_id'=>$_POST['paas_id'],
                'url'=>$_POST['url'],
                'js_plugin'=>$_POST['js_plugin']
            ];}
            
           $web->mdfdata($id,$data);
        //    update_json($web,WEB_JSON);
           $post="Editurl success";
        }else{
            $post = "Failed";
        }
        return $post;
    }

    public function delurl(Web $web)
    {
        if($_POST){
            $web->deldata($_POST['id']);
            // update_json($web,WEB_JSON);
            $post="Delete success";
        }else{
            $post = "defailed";
        }
        return $post;
    }

    public function delpaas(Paas $paas)
    {
        if($_POST){
            $paas->deldata($_POST['id']);
            // update_json($web,WEB_JSON);
            $post="Delete success";
        }else{
            $post = "defailed";
        }
        return $post;
    }

    public function addpaas(Paas $paas)
    {
        if($_POST){
            $data = [
                [
                'name'=>$_POST['name'],
                'admine'=>$_POST['admine'],
                'code1'=>$_POST['code1'],
                'code2'=>$_POST['code2'],
                'code3'=>$_POST['code3']
                ]
            ];
           $paas->adddata($data);
           update_json($paas,PAAS_JSON);
           $post="AddPaas success";
        }else{
            $post = "Paas Not Null";
        }
        return $post;
    }
    
     public function editpaas(Paas $paas)
    {
        if($_POST){
            $id = $_POST['id'];
            $data = [
                'name'=>$_POST['name'],
                'admine'=>$_POST['admine'],
                'code1'=>$_POST['code1'],
                'code2'=>$_POST['code2'],
                'code3'=>$_POST['code3']
            ];
           $paas->mdfdata($id,$data);
           update_json($paas,PAAS_JSON);
           $post="EditPaas success";
        }else{
            $post = "Failed";
        }
        return $post;
    }

}
