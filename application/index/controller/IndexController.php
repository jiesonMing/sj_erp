<?php
namespace app\index\controller;

use think\Session;
use think\Config;

class IndexController extends BaseController
{
    public function index()
    {
        $this->assign('nav','');
        return $this->fetch();
    }
}
