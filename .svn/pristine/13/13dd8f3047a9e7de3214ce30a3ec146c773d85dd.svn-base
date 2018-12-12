<?php

namespace app\index\model;

use think\Model;

class Users extends Model
{
    protected static function init(){
        Users::afterUpdate(function($user){
            $user->setInc('loginTimes',1);
        });
    }

    // 定义自动完成的属性
    protected $insert=['status'=>1,'isAdmin'=>0];
    protected $update = ['loginIp'];

    protected function setLoginIpAttr()
    {
        return request()->ip();
    }

    //定义员工角色关联
    public function userRole(){
        return $this->hasOne('User_role','userId','userId');
    }

    //定义员工权限关联
    public function userAuth(){
        return $this->hasOne('User_auth','userId','userId');
    }

    //定义员工公司关联
    public function userCompany(){
        return $this->hasOne('Company','companyId','companyId');
    }

}
