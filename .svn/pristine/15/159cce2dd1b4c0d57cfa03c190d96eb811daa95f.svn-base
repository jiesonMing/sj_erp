<?php

namespace app\index\controller;


/**
* 
*/
class DealerController extends BaseController
{
	
	//作者：ww
    //功能：客户列表
    public function DealerList(){
    	$where='d.companyId='.$this->companyId.' and d.status>0';
    	$value=request()->param('value/s')?request()->param('value/s'):'';
        if($value){
            $where.=" and (d.dealerName like '%{$value}%' or d.dealerShortName like '%{$value}%' or d.dealerNameEn like '%{$value}%') ";
        }
        $list = db('dealer')->alias('d')->join('company_type ct','ct.id=d.commpanyType','left')->where($where)->paginate();

        // $this->assign('button',Config::get('func'.$this->authId.'.dealerList'));
        $this->assign('list',$list);
    	$this->assign('nav','dealer');
    	$this->assign('subNav','dealerList');
    	return $this->fetch();
    }

}