<?php

namespace app\index\model;

use think\Model;

class Auth extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $name = 'auths';

    // 定义自动完成的属性
    protected $insert=['status'=>1];

    //定义一对多关联
    public function authModular(){
        return $this->hasMany('Auth_modular','authId','authId');
    }

    public function authUser(){
        return $this->hasMany('User_auth','authId','authId');
    }
}
