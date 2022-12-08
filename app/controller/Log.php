<?php
namespace app\controller;

use app\BaseController;
use think\facade\Db;

class Log extends BaseController
{
    function __construct()
    {
        login_Verify();
     banVisitor();
    }
    public function index()
    {
        return 0;
    }
    function login_error_log()
    {
        $res = Db::table('login_error_log')
            ->select()
            ->toArray();
        return view('loginfailed', ['data' => $res]);
    }
    function user_comments()
    {
        $res = Db::table('user_comments')
            ->select()
            ->toArray();
        return view('comments', ['data' => $res]);
    }
  
    function user_comments_data()
    {
      $res = Db::table('user_comments')
            ->select()
            ->toArray();
      return json($res);
    }

    function user_switch_log()
    {
        //已闲置
        $res = Db::table('user_switch_log')
            ->select()
            ->toArray();
        foreach ($res as $k => $row) {
            echo '<' . $k . '>' . join('&emsp;', $row) . '<br/>';
        }
    }
    function userswitchdata()
    {
        if (
            !isset($_GET['limit']) &&
            !isset($_GET['page']) &&
            !isset($_SESSION['power']) &&
            !isset($_SESSION['office'])
        ) {
            return;
        }

        if ($_SESSION['power'] === 7 && isset($_GET['userid'])) {
            if ($_GET['userid'] === 'all') {
                $list = Db::table('user_switch_log')
                    ->order('id', 'desc')
                    ->paginate([
                        'list_rows' => $_GET['limit'],
                        'var_page' => 'page',
                        'page' => $_GET['page'],
                    ])
                    ->toArray();
            } else {
                $list = Db::table('user_switch_log')
                    ->where('userid', $_GET['userid'])
                    ->order('id', 'desc')
                    ->paginate([
                        'list_rows' => $_GET['limit'],
                        'var_page' => 'page',
                        'page' => $_GET['page'],
                    ])
                    ->toArray();
            }
        } elseif ($_SESSION['power'] === 7) {
            $list = Db::table('user_switch_log')
                ->order('id', 'desc')
                ->paginate([
                    'list_rows' => $_GET['limit'],
                    'var_page' => 'page',
                    'page' => $_GET['page'],
                ])
                ->toArray();
        } elseif ($_SESSION['power'] === 5 && isset($_GET['userid'])) {
            if ($_GET['userid'] === 'all') {
                $list = Db::table('user_switch_log')
                    ->where('office', $_SESSION['office'])
                    ->order('id', 'desc')
                    ->paginate([
                        'list_rows' => $_GET['limit'],
                        'var_page' => 'page',
                        'page' => $_GET['page'],
                    ])
                    ->toArray();
            } else {
                $list = Db::table('user_switch_log')
                    ->where([
                        ['office', '=', $_SESSION['office']],
                        ['userid', '=', $_GET['userid']],
                    ])
                    ->order('id', 'desc')
                    ->paginate([
                        'list_rows' => $_GET['limit'],
                        'var_page' => 'page',
                        'page' => $_GET['page'],
                    ])
                    ->toArray();
            }
        } elseif ($_SESSION['power'] === 5) {
            $list = Db::table('user_switch_log')
                ->where('office', $_SESSION['office'])
                ->order('id', 'desc')
                ->paginate([
                    'list_rows' => $_GET['limit'],
                    'var_page' => 'page',
                    'page' => $_GET['page'],
                ])
                ->toArray();
        } else {
            $list = ['error' => "You don't have permission to view it"];
        }

        $list['count'] = $list['total'];
        $list['msg'] = 'User switching log data';
        $list['code'] = 0;
        $res = json($list);

        return $res;
    }
    function userswitch()
    {
        return view('userswitch');
    }

    function webcodesafe()
    {
        $res = Db::table('webcodesafe_log')
            ->select()
            ->toArray();
        foreach ($res as $k => $row) {
            unset($row['id']);
            echo '<' . $k . '>' . join('=>', $row) . '<br/>';
        }
    }
    function webcodesafedata()
    {
        if (!(array_key_exists('limit',$_GET) &&
            array_key_exists('page',$_GET))) 
        {
            return json(["msg"=>"request error"]);
        }
        $list = Db::table('safe_log')
                ->order('id', 'desc')
                ->paginate([
                    'list_rows' => $_GET['limit'],
                    'var_page' => 'page',
                    'page' => $_GET['page'],
                ])
                ->toArray();
        $list['count'] = $list['total'];
        $list['msg'] = 'webcodesafe log data';
        $list['code'] = 0;
        $res = json($list);

        return $res;
    }
    function websafe()
    {
        /*
        $res = Db::table('safe_log')
            ->order('date', 'desc')
            ->select()
            ->toArray();
        */
        return view('websafe');
    }
}
