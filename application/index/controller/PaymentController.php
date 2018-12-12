<?php

namespace app\index\controller;

use think\Controller;

class PaymentController extends BaseController
{
    //作者：于明明
    //功能：应付账款
    public function index(){
        $this->assign('nav','payment');
        return $this->fetch();
    }
}
