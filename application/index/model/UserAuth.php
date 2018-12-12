<?php

namespace app\index\model;

use think\Model;

class UserAuth extends Model
{
    // 设置数据表（不含前缀）
    protected $name = 'user_auth';

    public function users(){
        return $this->belongsTo('Users');
    }


}
