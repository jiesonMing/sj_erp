<?php
namespace app\admin\model;

use think\Model;

class Users extends Model
{
    // 定义自动完成的属性
    protected $insert=['status'=>1,'isAdmin'=>1];

}