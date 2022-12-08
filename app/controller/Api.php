<?php

namespace app\controller;

use app\BaseController;
use app\model\Web;
use app\model\User;
use think\facade\Log;
use think\facade\Db;
use think\facade\Request;
class Api extends BaseController
{
    function __construct()
    {
        header('Access-Control-Allow-Origin: *'); //解决远程无法访问控制器问题
        // if (strpos($_SERVER['SERVER_NAME'], 'cat') == false) {
        //     die();
        // }
        Log::close();
    }

    //line业务号数据接口
    function linedata()
    {
        if (!$_GET || !isset($_GET['weburl'])) {
            return '<h1><center>嗨！別來無恙啊！</center></h1>' .
                "<script>console.info('嗨！別來無恙啊！')</script>";
        }
        $web = new Web();
        $user = new User();
        $web_url = $_GET['weburl'];

        if (strpos($web_url, 'www') !== false) {
            $web_urls = str_replace('www.', '', $web_url);
        } else {
            $web_urls = 'www.' . $web_url;
        }

        $user_arr = [];
        $jsPlugin = "";
        $web_paas = $web->get_Url_paas($web_url); //获取url所在的平台ID
        if (!is_numeric($web_paas)) { //没找到
            $web_paas = $web->get_Url_paas($web_urls);
            if (!is_numeric($web_paas)) {
                //logURL($web_urls);
                //当status = 5 时，将跳转至我们的落地页
                $data = [
                    //'status'=>"5",
                    'state' => 404,
                    'line_id' => '系統未添加該網站',
                    'line_url_mb' => "javascript:alert('系統未添加該網站')",
                    'line_url_pc' => "javascript:alert('系統未添加該網站')",
                    'code1' => '',
                    'code2' => '',
                    'code3' => '',
                    'js_plugin'=>'',
                ];
                return json($data);
            }
            $code1 = get_code($web_paas, 'code1');
            $code2 = get_code($web_paas, 'code2');
            $code3 = get_code($web_paas, 'code3');
            $jsPlugin = get_url_data($web_urls)['js_plugin'];
        } else { //找到了
            $code1 = get_code($web_paas, 'code1');
            $code2 = get_code($web_paas, 'code2');
            $code3 = get_code($web_paas, 'code3');
            $jsPlugin = get_url_data($web_url)['js_plugin'];
        }

        $user_arr = $user->paas_get_user($web_paas); //获取平台内的在线的业务号
        if (empty($user_arr) || count($user_arr) == 0) {
            $data = [
                'state' => 404,
                'line_id' => '該網站未放置業務號！',
                'line_url_mb' => "javascript:alert('該網站未放置業務號')",
                'line_url_pc' => "javascript:alert('該網站未放置業務號')",
                'code1' => $code1,
                'code2' => $code2,
                'code3' => $code3,
            ];
            return json($data);
        }
        $data_arr = [];
        foreach ($user_arr as $row) {
            $data_arr[$row['id']] = $row; //获取所有的业务号ID
        }

        ///开始概率抽取///
        $result = [];
        $prob_arr = [];
        foreach ($data_arr as $key => $val) {
            $prob_arr[$key] = $val['prob'];
        }
        $prob_Sum = array_sum($prob_arr); ///计算总幸运值
        asort($prob_arr); //对数组升序排序
        foreach ($prob_arr as $userid => $aProb) {
            $randNum = mt_rand(0, $prob_Sum); ////取出一个随机数
            if ($randNum <= $aProb) {
                $result = $data_arr[$userid];
                break;
            } else {
                $prob_Sum -= $aProb;
            }
        }
        $line_id = trim($result['account']);
        $line_url_mb = trim($result['link_mb']);
        $line_url_pc = trim($result['link_pc']);
        $data = [
            'state' => 200,
            'line_id' => $line_id,
            'line_url_mb' => $line_url_mb,
            'line_url_pc' => $line_url_pc,
            'code1' => $code1,
            'code2' => $code2,
            'code3' => $code3,
            'js_plugin'=> $jsPlugin
        ];
        return json($data);
    }

    //评论接口
    function usercomment()
    {
        if (!$_POST) {
            return 'only post';
        }
        //$data = request()->param();
      
        $url = $_POST['url'];
        $comment = $_POST['content'];
        $type = $_POST['type'];
        $ip = getHostIp();
        $topic = isset($_POST['topic'])?$_POST['topic']:null;
        //$commentdata = '【'.$url.'】【'.$type.'】【'.$comment.'】';
        //writeComment($commentdata);
        $data = [
            'type' => $type,
            'content' => $comment,
            'weburl' => $url,
            'ip' => $ip,
            'topic' => $topic,
        ];
        //print_r($data);
        Db::name('user_comments')->save($data);
        return 'comment success';
    }
  
    function lineverify()
    {
      if (!Request::instance()->isPost()||!isset($_POST['lineid'])) return json(['code'=>0,'data'=>['msg'=>'Request Errors !']]);
      $lineid = $_POST['lineid'];
      $res = Db::name('user')->getByAccount($lineid);
      $msg = $res?"Found":"Not Found";
      $code = $res?1:0;
      return json(['code'=>$code,'data'=>['msg'=>$msg]]);
    }
    public function skintest()
    {
        if (!$_POST) {
            return 'only post';
        }
        $origin = $_POST['origin'];
        $paper = $_POST['paper'];
        $code = substr(md5(time().mt_rand(1,1000000)),8,8);
        $ip = getHostIp();
        $data = [
            'code'=>$code,
            'paper'=>$paper,
            'origin'=>$origin,
            'ip'=>$ip,
        ];
        Db::name('skintestpaper')->save($data);
        $res = [
            'code'=>$code,
            'msg'=> "successful test"
            ];
        return json($res);
    }
    public function searchskintest()
    {
        if (!$_GET) {
            return 'error';
        }
        $code = $_GET['code'];
        $res = Db::table('skintestpaper')->where('code', $code)->find();
        if($res==null) {
            return json(['result'=>null,'msg'=>'沒有找到！']);
        }
        return json(['result'=>$res,'msg'=>'找到了！']);
    }
}
