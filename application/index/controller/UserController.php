<?php

namespace app\index\controller;

use think\Controller;
use think\Session;
use app\index\model\Users;
use app\index\model\Company;

class UserController extends Controller
{
    //作者：于明明
    //功能：登陆
    public function login(){
        if(request()->isPost()){
            $domain=request()->domain();
            $company=Company::get(['domain'=>$domain,'status'=>1]);
            if(!$company) errorMsg(400,'当前公司尚未开通权限，请联系客服！');
            $mobile=request()->param('mobile/s')?request()->param('mobile/s'):'';
            if(!$mobile) errorMsg(400,'登陆手机不能为空！');
            $password=request()->param('password/s')?request()->param('password/s'):'';
            if(!$password) errorMsg(400,'登陆密码不能为空！');
            $password=md5($password.'erp');
            try{
                $users=Users::get(['mobile'=>$mobile,'companyId'=>$company->companyId,'status'=>1]);
                if(!$users) errorMsg(400,'当前员工不存在！');
                if($users->password!=$password) errorMsg(400,'密码错误，请重新输入！');
                //if($users->isLogin) errorMsg(400,'当前用户正在登陆中，不能重复登陆！');
                //if($users->isLogin) errorMsg(400,'当前员工正在登陆中，不能重复登陆！');

                $users->loginTime=date('Y-m-d H:i:s');
                $users->isLogin=1;
                $users->save();
                if($users->isAdmin){
                    $authId='';
                }else{
                    $userAuth=$users->userAuth;
                    if(!$userAuth) errorMsg(400,'当前员工没有登陆权限！');
                    $authId=$userAuth->authId;
                }

                Session::set('companyId',$company->companyId);
                Session::set('companyName',$company->companyName);
                Session::set('companyShortName',$company->companyShortName);
                Session::set('companyEnName',$company->companyEnName);
                Session::set('userId',$users->userId);
                Session::set('isAdmin',$users->isAdmin);
                Session::set('nickName',$users->nickName);
                Session::set('mobile',$users->mobile);
                Session::set('session_start_time', time());
                Session::set('authId',$authId);
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
        $userId=request()->param('userId/d')?request()->param('userId/d'):0;
        if($userId){
            $users=Users::get($userId);
            $users->isLogin=0;
            $users->save();
        }
        Session::clear();
        errorMsg(200,'done');
    }
}
