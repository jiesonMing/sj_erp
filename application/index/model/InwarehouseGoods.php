<?php

namespace app\index\model;

use think\Model;

class InwarehouseGoods extends Model
{
    // 设置数据表（不含前缀）
    protected $name = 'inwarehouse_goods';

    // 定义自动完成的属性
    protected $insert=['status'=>1];

}
