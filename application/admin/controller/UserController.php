<?php

namespace app\admin\controller;

use think\Controller;
use think\Session;
use app\admin\model\Admin;

class UserController extends Controller
{
    //作者：于明明
    //功能：登陆
    public function login(){
        if(request()->isPost()){
            $mobile=request()->param('mobile/s')?request()->param('mobile/s'):'';
            if(!$mobile) errorMsg(400,'登陆手机不能为空！');
            $password=request()->param('password/s')?request()->param('password/s'):'';
            if(!$mobile) errorMsg(400,'登陆密码不能为空！');
            $password=md5($password.'erp');
            try{
                $admin=Admin::get(['mobile'=>$mobile,'status'=>1,'companyId'=>0]);
                if(!$admin) errorMsg(400,'当前管理员不存在！');
                if($admin->password!=$password) errorMsg(400,'密码错误，请重新输入！');

                Session::set('adminId',$admin->adminId);
                Session::set('adminName',$admin->adminName);
                Session::set('mobile',$admin->mobile);
                session::set('session_start_time', time());
                errorMsg(200,'登陆成功！');
            }catch(Exception $e){
                errorMsg(400,$e->getMessage());
            }
        }else{
            return $this->fetch();
        }
    }

    //作者：于明明
    //功能：退出
    public function loginOut(){
        Session::clear();
        errorMsg(200,'done');
    }
}
