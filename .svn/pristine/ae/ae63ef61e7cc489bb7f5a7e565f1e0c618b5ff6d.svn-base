<?php

namespace app\index\model;

use think\Model;

class Inwarehouse extends Model
{
    // 定义自动完成的属性
    protected $insert=['status'=>1];

    // 定义关联
    public function inwareGoods()
    {
        return $this->hasMany('inwarehouse_goods','inwareId','inwareId');
    }
}
