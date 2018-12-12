<?php

namespace app\index\controller;

use think\Controller;

class ReceivablesController extends BaseController
{
    //作者：于明明
    //功能：应收账款
    public function index(){
        $this->assign('nav','receivables');
        return $this->fetch();
    }
}
