<?php

namespace app\index\controller;

use think\Controller;

class ChartController extends BaseController
{
    //作者：于明明
    //功能：报表
    public function index(){
        $this->assign('nav','chart');
        return $this->fetch();
    }
}
