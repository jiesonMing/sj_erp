<?php

namespace app\index\model;

use think\Model;

class Purchase extends Model
{
    // 定义自动完成的属性
    protected $insert=['status'=>1];
}
