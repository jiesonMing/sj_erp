<?php
namespace app\admin\model;

use think\Model;

class Company extends Model
{
    // 定义自动完成的属性
    protected $insert=['status'=>1];

    // 定义关联方法
    public function users()
    {
        // 用户HAS ONE档案关联
        return $this->hasMany('Users','companyId','companyId');
    }
}