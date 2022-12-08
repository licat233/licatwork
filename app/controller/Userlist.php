<?php

namespace app\controller;

use app\BaseController;
use app\model\User;
use app\model\Paas;
use think\facade\View;
use think\facade\Db;

class Userlist extends BaseController
{

    function __construct() 
    {
    //   if (strpos($_SERVER['SERVER_NAME'], "cat") == false) {die();}
       login_Verify();
        banVisitor();
    }
  
    function __destruct() {
        Db::name('user')->where('status', 0)->update(['prob'=>'100']); //$data必须为一维数组
    }

    public function index(User $user, Paas $paas)
    {
        //json_file_status($user, $paas, null);
        // $res = json_decode(getdata_json(PAAS_JSON), true); //true时为数组,此时的数组来着json文件

        $res = format_json($paas, PAAS_JSON);//true时为数组,此时的数组来自数据库

        foreach ($res['data'] as $row) {
            $namearr[$row['id']] = $row['name'];
        }
        View::assign('paas_name', $namearr);
        View::assign('power', $_SESSION['power']);
        return view('index');
    }

    public function userdata(User $user)
    {
        $data = [];
        $user_json_data = [];
        if (array_key_exists('office',$_GET)) {
            if($_SESSION['power'] !==7 && $_GET['office']!==$_SESSION['office']){
                return json([
                    "total"=>0,"per_page"=>100,"current_page"=>1,"last_page"=>1,"data"=>[],"count"=>0,"msg"=>"你没有权限访问！","code"=>403
                    ]);
            }
            //安全处理，SQL注入
            $office = "";
            if($_GET['office']==412){
                $office = '412';
            }else if($_GET['office']==707){
                $office = '707';
            }else if($_GET['office']==808){
                $office = '808';
            }else{
                return 'office failed';
            }
            //这里不要使用model  ->where([['office','=', $office],['hide','=',0]])
            $list = Db::table('user')->where([['office','=', $office],['hide','=',0]])->order(['status' => 'desc', 'paas_id' => 'asc'])
                ->paginate([
                    'list_rows' => $_GET['limit'],
                    'var_page' => 'page',
                    'page' => $_GET['page'],
                ])->toArray();
        }else{
            $office = $_SESSION['office'];
            if($office == "808") {
                $office = "412";
            }
            $list = Db::table('user')->where([['office','=', $office],['hide','=',0]])->order(['status' => 'desc', 'paas_id' => 'asc'])
                ->paginate([
                    'list_rows' => $_GET['limit'],
                    'var_page' => 'page',
                    'page' => $_GET['page'],
                ])->toArray();
        }
        
        
            $listdata = $list['data'];
            foreach ($listdata as $k => $row) {
              $listdata[$k]['paas_name'] = idTopaasname($row['paas_id'])."<=".idTopaasname($row['last_paas_id']);
              $listdata[$k]['prob_PCT'] = prob_PCT($listdata[$k]['id']);
            }
            $list['data'] = $listdata;
          $user_json_data=$list;
          $user_json_data['count'] = $list['total'];
          $user_json_data['msg'] = 'user data';
          $user_json_data['code'] = 0;
        
        //   $user_json_data = format_json($user, USER_JSON);
        
       
        //对数据进行过滤处理
        foreach ($user_json_data['data'] as $a) {  //核心部位，别瞎优化，别以为自己能看得懂，我自己写的都看不懂了
          if($a['hide']==0){
            if($_SESSION['power']==7){
              $a['color'] = paasColor($a["paas_id"]);
              if($a['status'] === 0) $a['color']=$a['color']."85";
              array_push($data,$a);
            }else{
               if($a['office']==$_SESSION['office']){
                 $a['color'] = paasColor($a["paas_id"]);
                 if($a['status'] === 0) $a['color']=$a['color']."85";
                 array_push($data,$a);
               }
            }
          }
         }
       
       $user_json_data['data'] = $data;
       $user_json_data['office'] = $office;
        return json($user_json_data);
    }

   public function editUser(User $user)
    {
     if($_SESSION['power']==1) return json(["msg"=>"你没有权限访问","code"=>0]);
        if ($_POST) {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $status = $_POST['status'];
            $account = $_POST['account'];
            $paas_id = $_POST['paas_id'];
            $link_pc = $_POST['link_pc'];
            $link_mb = $_POST['link_mb'];
            $office = trim($_POST['office']);
            $prob_PCT = $_POST['prob_PCT'];
            $prob = toProb($prob_PCT,$paas_id,$id);//根据传进来的概率计算幸运值！！！
            $data = [
                    "name" => $name,
                    "account" => $account,
                    "prob" => $prob,
                    "link_pc" => $link_pc,
                    "link_mb" => $link_mb,
                    "office"  => $office
                ];
            $datetime = date('Y-m-d H:i:s', time());
            $now_paas_id = userid_get_paasid($id);
            $now_status = userid_get_status($id);
            
            if($paas_id != $now_paas_id) 
            {
                lineSwitchPaas($id,$now_paas_id,$paas_id);
                $last_paas_id = $now_paas_id;
                $data["last_paas_id"] = $last_paas_id;
                $data["datetime"] = $datetime;
                $data["paas_id"] = $paas_id;
            }
            
            if($status != 'no' && $now_status != $status)
            {
                lineSwitchStatus($id,$paas_id,$status);
                $data["status"] = $status;
                $data["datetime"] = $datetime;
            }
            
            $now_prob_PCT = prob_PCT($id);
            if($prob_PCT != $now_prob_PCT&&$now_prob_PCT!=0)
            {
              lineSwitchProb($id,$now_prob_PCT,$prob_PCT);
            }
            $user->mdfdata($id, $data);
            $post = "Edit success";
        } else {
            $post = $_POST;
        }
        return json($post);
    }

    public function adduser(User $user)
    {
        if ($_POST) {
            $name = $_POST['name'];
            $account = $_POST['account'];
            $paas_id = $_POST['paas_id'];
            $status = $_POST['status'];
            // $prob_PCT = $_POST['prob_PCT'];
            // /////根据传进来的概率计算幸运值！！！
            // $prob = toProb($prob_PCT,$paas_id);
            $prob = $_POST['prob'];
            //$datetime = $_POST['datetime'];
            $datetime = date('Y-m-d H:i:s', time());
            $link_pc = $_POST['link_pc'];
            $link_mb = $_POST['link_mb'];
            $office = trim($_POST['office'])?trim($_POST['office']):$_SESSION['office'];
            $data = [
                [
                    "name" => $name,
                    "account" => $account,
                    "paas_id" => $paas_id,
                   "status" => $status,
                    "prob" => $prob,
                    "datetime" => $datetime,
                    "link_pc" => $link_pc,
                    "link_mb" => $link_mb,
                    "office"  => $office
                ]
            ];
            $user->adddata($data);
            //update_json($user, USER_JSON);
            $post = "Adduser success";
        } else {
            $post = "failed";
        }
        return json($post);
    }

    public function delUser(User $user)
    {
        if ($_POST) {
            
            if(!isset($_POST['id'])){
                return json("not find parame");
            }
            $ids = $_POST['id'];
            if(count($ids)===0){
                return json("not find id");
            }
            // $user->deldata($_POST['id']);
            $list = [];
            foreach ($ids as $id) {
                array_push($list,['id'=>$id, 'hide' => 1]);
            }
            $user->saveAll($list);
            //update_json($user, USER_JSON);
            $post = "Delete success";
        } else {
            $post = "defailed";
        }
        return json($post);
    }

    public function mdfstatus(User $user)
    {
        if ($_POST) {
            $id = $_POST['id'];
            $status = $_POST['status'] === 'true' ? 1 : 0;
            //$datetime = $_POST['datetime'];
           $datetime = date('Y-m-d H:i:s', time());
            $data = [
                "status" => $status,
                "datetime" => $datetime,
                "prob" => 100
            ];
            $user->mdfdata($id, $data);
            $now_paas_id = userid_get_paasid($id);
            lineSwitchStatus($id,$now_paas_id,$status);
            //update_json($user, USER_JSON);
            $post = "Modify success";
        } else {
            $post = $_POST;
        }
        return json($post);
    }
  
    public function probreset(User $user)
    {
      $id = $_POST['id'];
      $data = [];
      foreach ($id as $key => $row) {
        Array_push($data,['id'=>$row,'prob'=>100]);
      }
      $user->saveAll($data);
      return json($data);
    }

    public function batchedituser(User $user)
    {
        if ($_POST) {
            $id = $_POST['id'];
            $status = $_POST['status'];
            $paas_id = $_POST['paas_id'];
            $prob = $_POST['prob'];
            //$datetime = $_POST['datetime'] === 'no' ? $_POST['timenow'] : $_POST['datetime'];
            $data = [];
            $key_id = 'id';
            $key_status = $status === 'no' ? 'status_no' : 'status';
            $key_paas_id = $paas_id === 'no' ? 'paas_id_no' : 'paas_id';
            $key_prob = $prob === 'no' ? 'prob_no' : 'prob';
            $key_datetime = $status === 'no' && $paas_id === 'no' ? 'datetime_no' : 'datetime';
            $datetime = date('Y-m-d H:i:s', time());
            foreach ($id as $key => $row) {
                $now_paas_id = userid_get_paasid($row);
                $now_status = userid_get_status($row);
                if($paas_id == 'no' || $paas_id == $now_paas_id)
                {
                    $last_paas_id = get_lastpaasid($row);
                    $key_datetime =  'datetime_no';
                }else{
                    $last_paas_id = $now_paas_id;
                    lineSwitchPaas($row,$now_paas_id,$paas_id);
                }
                if($status != 'no' && $now_status != $status)
                {
                  lineSwitchStatus($row,$paas_id,$status);
                  $key_datetime =  'datetime';
                }
                if( $prob != 'no')
                {
                  lineProbRecover($row);
                }
                Array_push($data, [
                    $key_id => $row,
                    $key_status => $status,
                    $key_paas_id => $paas_id,
                    "last_paas_id" => $last_paas_id,
                    $key_prob => $prob,
                    $key_datetime => $datetime,
                    //"datetime" => $datetime
                ]);
            }

            $user->saveAll($data);
            //update_json($user, USER_JSON);
            $post = "BatchModify success";
        } else {
            $post = $_POST;
        }
        return json($post);
    }
}
