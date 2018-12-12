<?php

namespace app\index\model;

use think\Model;

class Modular extends Model
{
    // 定义自动完成的属性
    protected $insert=['status'=>1];

    //定义一对多关联
    public function modularAuth(){
        return $this->hasMany('Auth_modular','modularId','modularId');
    }
}
