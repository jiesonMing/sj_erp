<?php
namespace app\admin\controller;

use think\Controller;
use think\Session;
use think\Config;
class BaseController extends Controller
{
    public function _initialize()
    {
        $adminId=Session::get('adminId');
        //如果该用户id或者权限id异常，则重新登陆
        if(!($adminId)){
            Session::clear();
            if(request()->isPost()){
                errorMsg(300,'请先登陆！');
            }else{
                $this->error('请先登陆','/Admin/User/login');
            }
        }
        //判断会话是否过期
        if (time() - session::get('session_start_time') > Config::get('session.expire')) {
            Session::clear();
            if(request()->isPost()){
                errorMsg(300,'登陆已经过期，请重新登陆！');
            }else {
                $this->error('登陆已经过期', '/Admin/User/login');
            }
        }
    }
}