<?php

namespace app\controller;
use app\BaseController;
use think\facade\Db;

class PageCenter extends BaseController
{
    private $requestType;
    private $id;
    private $dataValidate = true;
    private $errormsg;

    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Request-Methods:GET, POST, PUT, DELETE, OPTIONS");
        $this->requestType = strtolower(request()->method());
        $this->errormsg = json([
            'status' => 0,
            'msg' => 'api与method不匹配，不符合RESTful接口规范',
        ]);
        //print_r($_SERVER['REQUEST_URI']); // = /page_center/landingpage/1/view
        login_Verify();  //功能开发完成后再打开
    }

    function index()
    {
        return view('index',['userpower'=>getPower()]);
    }

    function Project($id = null)
    {
        $this->id = $id;
        $param = [
            'id',
            'name',
            'teacher',
            'description',
            'keywords',
            'gravatar',
            'office',
        ];
        $table = Db::table('project');
        return $this->router($table, $param);
    }

    function LandingPage($id = null)
    {
        $this->id = $id;
        $param = [
            'id',
            'projectid',
            'releasedate',
            'author',
            'grade',
            'title',
            'screenshot',
            'description',
            'status',
            'html',
            'safehtml',
        ];
        $table = Db::table('landingpage');
        return $this->router($table, $param);
    }

    function AuditPage($id = null)
    {
        $this->id = $id;
        $param = [
            'id',
            'projectid',
            'releasedate',
            'title',
            'screenshot',
            'status',
            'html',
        ];
        $table = Db::table('auditpage');
        return $this->router($table, $param);
    }

    public function projectLandingpage($id)
    {
        $list = Db::table('landingpage')
            ->where('projectid', $id)
            ->select();
        return json([
            'status' => 1,
            'data' => $list,
        ]);
    }

    public function projectAuditpage($id)
    {
        $list = Db::table('auditpage')
            ->where('projectid', $id)
            ->select();
        return json([
            'status' => 1,
            'data' => $list,
        ]);
    }

    /* 预览窗口 */
    public function viewLandingpage($id)
    {
        echo Db::table('landingpage')
            ->where('id', $id)
            ->value('html');
    }

    public function viewAuditpage($id)
    {
        echo Db::table('auditpage')
            ->where('id', $id)
            ->value('html');
    }

    /* 图片上传接口 */
    public function uploadImg()
    { 
       banVisitor();
        $file = request()->file('photo');
        if ($file) {
            $savename = \think\facade\Filesystem::disk('public')->putFile( 'topic', $file);
            return json([
                'status' => 1,
                'src' => WEBHOST . '/storage/' . $savename,
            ]);
        } else {
            return json([
                'status' => 0,
                'src' => null,
                'msg' => '未提交图片',
            ]);
        }
    }

    /* 私有方法区 */
    private function router($table, $param)
    {
        switch ($this->requestType) {
            case 'get':
                return $this->read($table);
                break;
            case 'post': banVisitor();
                return $this->add($table, $param);
                break;
            case 'put': banVisitor();
                return $this->update($table, $param);
                break;
            case 'delete': banVisitor();
                return $this->delete($table);
                break;
        }
    }

    /* 参数验证 */
    private function datahandle($datakey, $param)
    {
        if (array_diff($datakey, $param)) {
            $this->dataValidate = false;
            return json([
                'status' => 0,
                'msg' =>
                    '你所提交的数据不规范，多余字段：' .
                    implode('、', array_diff($datakey, $param)),
            ]);
        }
        if (array_diff($param, $datakey)) {
            $this->dataValidate = false;
            return json([
                'status' => 0,
                'msg' =>
                    '你所提交的数据不规范，缺少字段：' .
                    implode('、', array_diff($param, $datakey)),
            ]);
        }
    }

    private function read($table)
    {
        if (!$this->id) {
            $list = $table->withoutField('html')->select();
            return json(['status' => 1, 'data' => $list]);
        }

        $data = $table->where('id', $this->id)->find();

        if ($data && isset($data['projectid'])) {
            $project = Db::table('project')
                ->where('id', $data['projectid'])
                ->find();
            $data['projectdata'] = $project;
        }
        return json(['status' => 1, 'data' => $data]);
    }

    private function add($table, $param)
    {
        if ($this->id) {
            return $this->errormsg;
        }

        $data = request()->param();
        if (
            array_key_exists('releasedate', $data) &&
            empty($data['releasedate'])
        ) {
            $data['releasedate'] = date('Y-m-d');
        }

        $datakey = array_keys($data);
        unset($param[0]);
        $res = $this->datahandle($datakey, $param);
        if (!$this->dataValidate) {
            return $res;
        }
        $keyarr = [];
        foreach ($data as $key => $val) {
            if ($val === '') {
                array_push($keyarr, $key);
            }
        }

        if (!empty($keyarr)) {
            return json([
                'status' => 0,
                'msg' => '空的字段：' . implode('、', $keyarr),
            ]);
        }

        if ($table->save($data)) {
            $id = $table->getLastInsID();
            return json(['status' => 1, 'msg' => '添加成功','id'=>$id]);
        } else {
            return json(['status' => 0, 'msg' => '添加失败','id'=>null]);
        }
    }

    private function update($table, $param)
    {
        if (!$this->id) {
            return $this->errormsg;
        }
        $data = request()->param();

        $datakey = array_keys($data);
        $res = $this->datahandle($datakey, $param);
        if (!$this->dataValidate) {
            return $res;
        }
        if (
            array_key_exists('releasedate', $data) &&
            empty($data['releasedate'])
        ) {
            unset($data['releasedate']);
        }

        $keyarr = [];
        foreach ($data as $key => $val) {
            if ($val === '') {
                array_push($keyarr, $key);
            }
        }

        if (!empty($keyarr)) {
            return json([
                'status' => 0,
                'msg' => '修改失败，空的字段：' . implode('、', $keyarr),
            ]);
        }

        if ($table->where('id', $this->id)->update($data)) {
            return json(['status' => 1, 'msg' => '修改成功']);
        } else {
            return json(['status' => 0, 'msg' => '修改失败']);
        }
    }

    private function delete($table)
    {
        if (!$this->id) {
            return json();
        }
        if ($table->delete($this->id)) {
            return json(['status' => 1, 'msg' => '删除成功']);
        } else {
            return json(['status' => 0, 'msg' => '删除失败']);
        }
    }
}
