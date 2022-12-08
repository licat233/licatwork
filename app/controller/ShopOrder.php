<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use app\model\Order;
//use think\facade\Session;

class ShopOrder extends BaseController
{
    function __construct()
    {
        $origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';
        $allowOrigin = array(
            'https://licat.work','http://licat.work',
            'https://newworkbench.com','http://newworkbench.com',
            'https://www.planttitle.com','http://www.planttitle.com',
            );
        if (in_array($origin, $allowOrigin)) {
            header("Access-Control-Allow-Origin:".$origin);
        }

        // header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Request-Methods:GET, POST");
    }
    private function simplelogin()
    {
        if(isLogin()) return true;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if(isset($_SESSION['simplelogin']) && $_SESSION['simplelogin'] == true){
            return true;
        }
        return false;
    }
    public function index()
    {
        $adminStatus = isLogin();
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $status = $adminStatus || (isset($_SESSION['simplelogin']) && ($_SESSION['simplelogin']==true));
        //登录请求
        if (!$status && isset($_POST['simplesubmit'])) {
            $password = $_POST['password']; 
            if ($password === "xianggou888888") {
                $_SESSION['simplelogin'] = true;
                return view();
                // return view('/login/simple-login');
            }
            $status = false;
        }
        if($status){
            return view();
        }
		return view('login/simple-login');
    }
    public function postorder()
    {
        $req_type = strtolower(request()->method());
        if ($req_type !== "post") return "Method error";
        $param = [
            "name",
            "phone_number",
            "address",
            "pay_method",
            "order_number",
            "product_list",
            "product_num",
            "total_price",
            "remark",
            // "submit_at",
            "submit_ip",
            "brand"
        ];

        $data = request()->param();
        // $data["submit_at"] = time();
        $order_id = substr(md5(time() . mt_rand(1, 1000000)), 8, 16);
        $data["order_number"] = $order_id;
        $data["submit_ip"] = getHostIp();
        $datakey = array_keys($data);
        $res = $this->datahandle($datakey, $param);
        if ($res !== null) return $res;
        if(!preg_match('/[^,:{}\\[\\]0-9.\-+Eaeflnr-u \n\r\t]/',$data["product_list"])){
            return json([
                'status' => 0,
                'msg' => '你所提交的product_list数据不规范'
            ]);
        }
        $parr = json_decode($data["product_list"]);
        $totalnum = 0;
        foreach($parr as $value)
        {
            $totalnum += $value->num;
        }
        $data["product_num"] = $totalnum;
        Db::name('shop_order')->save($data);
        $info = [
            'status' => 200,
            'msg' => "successful",
            'order_number'=>$order_id,
            'productList'=>$data['product_list'],
            'totalPrice'=>$data['total_price'],
            'order_date' => date('Y-m-d H:i', time())
        ];
        return json($info);
    }
    
    public function orderdata()
    {
        if (session_status() !==PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!(array_key_exists('limit',$_GET) &&
            array_key_exists('page',$_GET)))
        {
            return json(["msg"=>"No permission"]);
        }
        
        $list = Db::table('shop_order')
                ->where('hide', 0)
                ->order(['status','id'=>'desc'])
                ->paginate([
                    'list_rows' => $_GET['limit'],
                    'var_page' => 'page',
                    'page' => $_GET['page'],
                ])
                ->toArray();
        $list['count'] = $list['total'];
        $list['msg'] = 'shop order data';
        $list['code'] = 0;
        $res = json($list);

        return $res;
    }
    public function switchStatus(){
        if ($_POST) {
            if(!isset($_POST['id'])){
                return json(["code"=>400,"msg"=>"缺少參數:order_number"]);
            }
            if(!isset($_POST['status'])){
                return json(["code"=>400,"msg"=>"缺少參數:status"]);
            }
            $id = $_POST['id'];
            $status = $_POST['status'];
            // $order->mdfdata($id, ["status"=>$status]);
            $res = Db::name('shop_order')->save(['id' => $id, 'status' => $status]);
            // $res = Db::table('shop_order')->where('id', $id)->update(['status' => $status]);
            if($res===1){
                return json(["code"=>200,"msg"=>"已修改","count"=>$res]);
            }
            return json(["code"=>400,"msg"=>"未修改","count"=>$res]);
        }
        return json(["code"=>400,"msg"=>"未修改"]);
    }
    /* 参数验证 */
    private function datahandle($datakey, $param)
    {
        if (array_diff($datakey, $param)) {
            return json([
                'status' => 0,
                'msg' =>
                '你所提交的数据不规范，多余字段：' .
                    implode('、', array_diff($datakey, $param)),
            ]);
        }

        if (array_diff($param, $datakey)) {
            return json([
                'status' => 0,
                'msg' =>
                '你所提交的数据不规范，缺少字段：' .
                    implode('、', array_diff($param, $datakey)),
            ]);
        }
        return null;
    }
    
    public function deleteOrder()
    {
        if ($_POST) {
            if(!isset($_POST['id'])){
                return json(["code"=>400,"msg"=>"未删除,缺少删除id"]);
            }
            $id = $_POST['id'];
            $res = Db::name('shop_order')->save(['id' => $id, 'hide' => 1, 'delete_time' => date('Y-m-d h:i:s', time()) ]);
            if($res===1){
                return json(["code"=>200,"msg"=>"已删除","count"=>$res]);
            }
            return json(["code"=>400,"msg"=>"未删除","count"=>$res]);
        }
        return json(["code"=>400,"msg"=>"未删除"]);
    }
    public function searchOrder()
    {
        if ($_POST) {
            if(!isset($_POST['phone_number'])){
                return json(["code"=>400,"msg"=>"缺少參數:phone_number"]);
            }
            $phone = $_POST['phone_number'];
            preg_match('/^\d{1,5}[\-]?\d{5,13}$/',$phone,$matches);
            if (count($matches) === 0) {
                return json(["code"=>400,"msg"=>"請輸入正確的手機號"]);
            }
            $list = Db::name('shop_order')->where('phone_number', $phone)->select()->toArray();
            $data['data'] = $list;
            $data['msg'] = 'shop order data';
            $data['total'] = count($list);
            $data['code'] = 200;
            return json($data);
        }
        return json(["code"=>400,"msg"=>"錯誤的請求"]);
    }
    public function cancelOrder()
    {
        if ($_POST) {
            if(!isset($_POST['order_number'])){
                return json(["code"=>400,"msg"=>"缺少參數:order_number"]);
            }
            $order_number = $_POST['order_number'];
            $list = Db::name('shop_order')->where('order_number',$order_number)->find();
            if ($list === null) {
                return json(["code"=>400,"msg"=>$order_number."訂單不存在在"]);
            }
            if ($list['status']===1){
                return json(["code"=>400,"msg"=>"該訂單已經發貨，訂單取消失敗，請聯繫客服取消<br />祝您生活愉快","num"=>$order_number]);
            }
            $res = Db::name('shop_order')->where('order_number',$order_number)->update(['status' => 2,'delete_time' => date('Y-m-d h:i:s', time()) ]);
            if($res !== 0){
                return json(["code"=>200,"msg"=>"訂單取消成功","count"=>$res,"num"=>$order_number]);
            }
            return json(["code"=>400,"msg"=>"該訂單取消失敗，請聯繫客服處理","count"=>$res,"num"=>$order_number]);
        }
        return json(["code"=>400,"msg"=>"錯誤的請求"]);
    }
}
