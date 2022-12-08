<?php

namespace app\controller;

use app\BaseController;
use app\model\Web;
use app\model\User;

class PostApi extends BaseController
{
  function __construct() 
    {
    //   if (strpos($_SERVER['SERVER_NAME'], "cat") == false) {
    //       die();
    //     }
  }
    function line()
    {
        header("Access-Control-Allow-Origin: *"); //解决远程无法访问控制器问题
          if ($_POST && isset($_POST['mytoken']) && $_POST['mytoken'] === "sHPLIuO6O9cxQ8Z9") { 
            $web = new Web();
            $user = new User();
            $amy = "amyamy.net";
            $web_url = $_POST['web'];
            $web_source = $_POST['web_source'];

           // if (strpos($web_url, $amy) !== false) { //判断当前页是否为Amy官网
                $web_source_paas = $web->get_Url_paas($web_source);
                if (is_numeric($web_source_paas)) { //判断来源链接是否存在数据库
                    $web_url = $web_source;
                }
            //}
     
            if (strpos($web_url, 'www') !== false) {
                $web_urls = str_replace("www.", "", $web_url);
            } else {
                $web_urls = 'www.' . $web_url;
            }

            $user_arr = [];
            $web_paas = $web->get_Url_paas($web_url); //获取url所在的平台ID
            if (!is_numeric($web_paas)) {
                $web_paas = $web->get_Url_paas($web_urls);
                if (!is_numeric($web_paas)) {
                  //logURL($web_urls);
                  //当status = 5 时，将跳转至我们的落地页
                    $data = [
                      //'status'=>"5",
                        'line_id' => "系统未添加该网站",
                        'line_url_mb' => "https://www.skincare-info.com/news",
                        'line_url_pc' => "",
                        'code1' => "",
                        'code2' => "",
                        'code3' => ""
                    ];
                    return json($data);
                    die();
                }
                $code1 = get_code($web_paas, 'code1');
                $code2 = get_code($web_paas, 'code2');
                $code3 = get_code($web_paas, 'code3');
            } else {
                $code1 = get_code($web_paas, 'code1');
                $code2 = get_code($web_paas, 'code2');
                $code3 = get_code($web_paas, 'code3');
            }

            $user_arr = $user->paas_get_user($web_paas); //获取平台内的在线的业务号
            if (empty($user_arr) || count($user_arr) == 0) {
                $data = [
                    'line_id' => "该网站未放置业务号！",
                    'line_url_mb' => "",
                    'line_url_pc' => "",
                    'code1' => $code1,
                    'code2' => $code2,
                    'code3' => $code3,
                ];
                return json($data);
                die();
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
                'line_id' => $line_id,
                'line_url_mb' => $line_url_mb,
                'line_url_pc' => $line_url_pc,
                'code1' => $code1,
                'code2' => $code2,
                'code3' => $code3,
            ];
            return json($data);
            die();
        } else {
            return "<h1><center>嗨！别来无恙啊！</center></h1>" . "<script>console.log('嗨！别来无恙啊！')</script>";
        }
    }
}
