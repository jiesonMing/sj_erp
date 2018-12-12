<?php

namespace app\index\controller;

use think\Controller;
use think\Session;
use think\Config;
use think\Db;

class WarehouseController extends BaseController
{
    //作者：ww
    //功能：自营仓
    public function selfWarehouse(){
    	$value = request()->param('value/s')?request()->param('value/s'):'';
    	$where = 'w.companyId='.$this->companyId.' and w.status=1 and w.warehouseType=1';
    	if($value){
    		$where .= " and ((w.warehouseCode like '%{$value}%') or (w.warehouseName like '%{$value}%') or (w.companyName like '%{$value}%'))";
    	}
    	$result=db('warehouse')->alias('w')->join('contacts c','w.contactsId=c.id','left')->field('w.*,c.contName,c.conTel')->where($where)->paginate();
    	$this->assign('button',Config::get('func'.Session::get('authId').'.selfWarehouse'));
    	Session::set('subNav','warehouse');
        $this->assign('nav','warehouse');
        $this->assign('subNav','selfWarehouse');
        $this->assign('result',$result);
        $this->assign('value',$value);
        return $this->fetch();
    }

    //作者：ww
    //功能：第三方仓库
    public function thirdWarehouse(){
    	$value = request()->param('value/s')?request()->param('value/s'):'';
    	 $where = 'w.companyId='.$this->companyId.' and w.status=1 and w.warehouseType=2';
    	if($value){
    		$where .= " and ((w.warehouseCode like '%{$value}%') or (w.warehouseName like '%{$value}%') or (w.companyName like '%{$value}%'))";
    	}
    	$result=db('warehouse')->alias('w')->join('contacts c','w.contactsId=c.id','left')->field('w.*,c.contName,c.conTel')->where($where)->paginate();
    	$this->assign('button',Config::get('func'.Session::get('authId').'.thirdWarehouse'));
    	Session::set('subNav','warehouse');
        $this->assign('nav','warehouse');
        $this->assign('subNav','thirdWarehouse');
        $this->assign('result',$result);
        $this->assign('value',$value);
        return $this->fetch();
    }

    //作者：ww
    //功能：自营仓仓库回收站
    public function selfDelList(){
        $value=request()->param('value/s')?request()->param('value/s'):'';
        $where='(w.status=0 and w.warehouseType=1)';
        $where .= ' and w.companyId='.$this->companyId;
        if($value){
            $where.=" and ( (w.warehouseCode='{$value}') or (w.warehouseName='{$value}') )";
        }
        $lists=db('warehouse')->alias('w')->join('contacts c','w.contactsId=c.id','left')->field('w.*,c.contName,c.conTel')->where($where)->paginate();
        $this->assign('nav','warehouse');
        $this->assign('subNav','selfWarehouse');
        $this->assign('value',$value);
        $this->assign('list',$lists);
        return $this->fetch();
    }

    //作者：ww
    //功能：第三方仓库回收站
    public function thirdDelList(){
        $value=request()->param('value/s')?request()->param('value/s'):'';
        $where='(w.status=0 and w.warehouseType=2)';
        $where .= ' and w.companyId='.$this->companyId;
        if($value){
            $where.=" and ( (w.warehouseCode='{$value}') or (w.warehouseName='{$value}') )";
        }
        $lists=db('warehouse')->alias('w')->join('contacts c','w.contactsId=c.id','left')->field('w.*,c.contName,c.conTel')->where($where)->paginate();
        $this->assign('nav','warehouse');
        $this->assign('subNav','thirdWarehouse');
        $this->assign('value',$value);
        $this->assign('list',$lists);
        return $this->fetch();
    }

    //作者：ww
    //功能：新增自营仓库
    public function addSelfWarehouse(){
        if(request()->isPost()){
        	$warehouseArr = array();

        	$warehouseArr['warehouseCode'] = request()->param('warehouseCode/s')?request()->param('warehouseCode/s'):'';
        	if(!$warehouseArr['warehouseCode']) errorMsg(400,'仓库自编码不能为空');

        	$warehouseArr['warehouseName'] = request()->param('warehouseName/s')?request()->param('warehouseName/s'):'';
        	if(!$warehouseArr['warehouseName']) errorMsg(400,'仓库名称不能为空');

        	$warehouseArr['wareType'] = request()->param('wareType/s')?request()->param('wareType/s'):'';
        	if(!$warehouseArr['wareType']) errorMsg(400,'仓库属性需要选择');

        	$warehouseArr['warehouseType'] = request()->param('warehouseType/s')?request()->param('warehouseType/s'):'';
        	if(!$warehouseArr['warehouseType']) errorMsg(400,'仓库类型需要选择');

        	$warehouseArr['companyId'] = $this->companyId?$this->companyId:0;

        	$warehouseArr['province'] = request()->param('province/s')?request()->param('province/s'):'';
        	$warehouseArr['city'] = request()->param('city/s')?request()->param('city/s'):'';
        	$warehouseArr['district'] = request()->param('district/s')?request()->param('district/s'):'';
        	$warehouseArr['warehouseAddr'] = request()->param('warehouseAddr/s')?request()->param('warehouseAddr/s'):'';

        	$warehouseArr['sender'] = request()->param('sender/s')?request()->param('sender/s'):'';
        	if(!$warehouseArr['sender']) errorMsg(400,'寄件人姓名不能为空');

        	$warehouseArr['senderTel'] = request()->param('senderTel/s')?request()->param('senderTel/s'):'';
        	if(!$warehouseArr['senderTel']) errorMsg(400,'寄件人电话不能为空');

        	$warehouseArr['weightError'] = request()->param('weightError/s')?request()->param('weightError/s'):'';
        	if(!$warehouseArr['weightError']) errorMsg(400,'称重误差不能为空');

        	$warehouseArr['hasPackingArea'] = request()->param('hasPackingArea/s')?request()->param('hasPackingArea/s'):'';

        	$warehouseArr['createUserId'] = $this->userId;

        	$warehouseArr['createTime'] = date('Y-m-d H:i:s',time());

        	$warehouseArr['updateTime'] = date('Y-m-d H:i:s',time());

        	$warehouseArr['maxLayer'] = request()->param('maxLayer/s')?request()->param('maxLayer/s'):'';
        	if(!$warehouseArr['maxLayer']) errorMsg(400,'货架层数不能为空');

        	$warehouseArr['remark'] = request()->param('remark/s')?request()->param('remark/s'):'';

        	errorMsg(400,$warehouseArr);
            //数据库操作
            //开始事务s
            Db::startTrans();
            try{
                //添加仓库
                Db::name('warehouse')->insert($warehouseArr);
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
        	$companyName = db('company')->field('companyName')->where('companyId='.$this->companyId)->find();
        	$wareType = db('ware_type')->field('name')->select();
            $provinceName = db('area')->field('provinceName')->group('province')->order('id asc')->select();
            $this->assign('button',Config::get('func'.Session::get('authId').'.selfWarehouse'));
            $this->assign('nav','warehouse');
            $this->assign('subNav','selfWarehouse');
            $this->assign('wareType',$wareType);
            $this->assign('companyName',$companyName['companyName']);
            $this->assign('provinceName',$provinceName);
            return $this->fetch();
        }
    }


    //作者：ww
    //功能：编辑自营仓库
    public function editSelfWarehouse(){
    	$id=request()->param('id/s')?request()->param('id/s'):0;
        if(request()->isPost()){
        	try {

        		if(!$id) errorMsg(400,'仓库信息有误，请刷新重试!');

	            $warehouseArr = array();

	        	$warehouseArr['warehouseCode'] = request()->param('warehouseCode/s')?request()->param('warehouseCode/s'):'';
	        	if(!$warehouseArr['warehouseCode']) errorMsg(400,'仓库自编码不能为空');

	        	$warehouseArr['warehouseName'] = request()->param('warehouseName/s')?request()->param('warehouseName/s'):'';
	        	if(!$warehouseArr['warehouseName']) errorMsg(400,'仓库名称不能为空');

	        	$warehouseArr['wareType'] = request()->param('wareType/s')?request()->param('wareType/s'):'';
	        	if(!$warehouseArr['wareType']) errorMsg(400,'仓库属性需要选择');

	        	$warehouseArr['warehouseType'] = request()->param('warehouseType/s')?request()->param('warehouseType/s'):'';
	        	if(!$warehouseArr['warehouseType']) errorMsg(400,'仓库类型需要选择');

	        	$warehouseArr['companyId'] = $this->companyId?$this->companyId:0;

	        	$warehouseArr['province'] = request()->param('province/s')?request()->param('province/s'):'';
	        	$warehouseArr['city'] = request()->param('city/s')?request()->param('city/s'):'';
	        	$warehouseArr['district'] = request()->param('district/s')?request()->param('district/s'):'';
	        	$warehouseArr['warehouseAddr'] = request()->param('warehouseAddr/s')?request()->param('warehouseAddr/s'):'';

	        	$warehouseArr['sender'] = request()->param('sender/s')?request()->param('sender/s'):'';
	        	if(!$warehouseArr['sender']) errorMsg(400,'寄件人姓名不能为空');

	        	$warehouseArr['senderTel'] = request()->param('senderTel/s')?request()->param('senderTel/s'):'';
	        	if(!$warehouseArr['senderTel']) errorMsg(400,'寄件人电话不能为空');

	        	$warehouseArr['weightError'] = request()->param('weightError/s')?request()->param('weightError/s'):'';
	        	if(!$warehouseArr['weightError']) errorMsg(400,'称重误差不能为空');

	        	$warehouseArr['hasPackingArea'] = request()->param('hasPackingArea/s')?request()->param('hasPackingArea/s'):'';

	        	$warehouseArr['createUserId'] = $this->userId;

	        	$warehouseArr['updateTime'] = date('Y-m-d H:i:s',time());

	        	$warehouseArr['maxLayer'] = request()->param('maxLayer/s')?request()->param('maxLayer/s'):'';
	        	if(!$warehouseArr['maxLayer']) errorMsg(400,'货架层数不能为空');

	        	$warehouseArr['remark'] = request()->param('remark/s')?request()->param('remark/s'):'';

        	}catch(\Exception $e){
                errorMsg(400,$e->getMessage());
            }
            //数据库操作
            //开始事务s
            Db::startTrans();
            try{
                //添加仓库
                Db::name('warehouse')->where('id',$id)->update($warehouseArr);
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            if(!$id){
                $this->error('仓库信息错误',$this->referer);
            }
            $where['w.id'] = $id;
            $where['w.status'] = 1;
            $data = db('warehouse')->alias('w')->where($where)->find();
            if(!$data){
                $this->error('未查询到仓库信息',$this->referer);
            }
            $companyName = db('company')->field('companyName')->where('companyId='.$this->companyId)->find();
        	$wareType = db('ware_type')->field('name')->select();
            $provinceName = db('area')->field('provinceName')->group('province')->order('id asc')->select();
            $result = db('warehouse')->alias('w')->join('contacts c','w.contactsId=c.id','left')->field('w.*,c.contName,c.conTel')->where($where)->find();
         //    $this->assign('button',Config::get('func'.Session::get('authId').'.selfWarehouse'));
            $this->assign('id',$id);
            $this->assign('nav','warehouse');
            $this->assign('subNav','selfWarehouse');
            $this->assign('wareType',$wareType);
            $this->assign('companyName',$companyName['companyName']);
            $this->assign('provinceName',$provinceName);
            $this->assign('result',$result);
            return $this->fetch();
        }
    }


    //作者：ww
    //功能：新增第三方仓库
    public function addThirdWarehouse(){
        if(request()->isPost()){
        	$warehouseArr = array();

        	$warehouseArr['warehouseCode'] = request()->param('warehouseCode/s')?request()->param('warehouseCode/s'):'';
        	if(!$warehouseArr['warehouseCode']) errorMsg(400,'仓库自编码不能为空');

        	$warehouseArr['warehouseName'] = request()->param('warehouseName/s')?request()->param('warehouseName/s'):'';
        	if(!$warehouseArr['warehouseName']) errorMsg(400,'仓库名称不能为空');

        	$warehouseArr['wareType'] = request()->param('wareType/s')?request()->param('wareType/s'):'';
        	if(!$warehouseArr['wareType']) errorMsg(400,'仓库属性需要选择');

        	$warehouseArr['warehouseType'] = request()->param('warehouseType/s')?request()->param('warehouseType/s'):'';
        	if(!$warehouseArr['warehouseType']) errorMsg(400,'仓库类型需要选择');

        	$warehouseArr['companyId'] = $this->companyId?$this->companyId:0;

        	$warehouseArr['companyName'] = request()->param('companyName/s')?request()->param('companyName/s'):'';
        	$warehouseArr['province'] = request()->param('province/s')?request()->param('province/s'):'';
        	$warehouseArr['city'] = request()->param('city/s')?request()->param('city/s'):'';
        	$warehouseArr['district'] = request()->param('district/s')?request()->param('district/s'):'';
        	$warehouseArr['warehouseAddr'] = request()->param('warehouseAddr/s')?request()->param('warehouseAddr/s'):'';

        	$warehouseArr['createUserId'] = $this->userId;

        	$warehouseArr['createTime'] = date('Y-m-d H:i:s',time());
        	$warehouseArr['updateTime'] = date('Y-m-d H:i:s',time());

        	$warehouseArr['remark'] = request()->param('remark/s')?request()->param('remark/s'):'';
            //数据库操作
            //开始事务s
            Db::startTrans();
            try{
                //添加仓库
                Db::name('warehouse')->insert($warehouseArr);
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
        	$companyName = db('company')->field('companyName')->where('companyId='.$this->companyId)->find();
        	$wareType = db('ware_type')->field('name')->select();
            $provinceName = db('area')->field('provinceName')->group('province')->order('id asc')->select();
            $this->assign('button',Config::get('func'.Session::get('authId').'.selfWarehouse'));
            $this->assign('nav','warehouse');
            $this->assign('subNav','thirdWarehouse');
            $this->assign('wareType',$wareType);
            $this->assign('companyName',$companyName['companyName']);
            $this->assign('provinceName',$provinceName);
            return $this->fetch();
        }
    }


    //作者：ww
    //功能：编辑第三方仓库
    public function editThirdWarehouse(){
    	$id=request()->param('id/s')?request()->param('id/s'):0;
        if(request()->isPost()){
        	try {

        		if(!$id) errorMsg(400,'仓库信息有误，请刷新重试!');

	            $warehouseArr = array();

	        	$warehouseArr['warehouseCode'] = request()->param('warehouseCode/s')?request()->param('warehouseCode/s'):'';
	        	if(!$warehouseArr['warehouseCode']) errorMsg(400,'仓库自编码不能为空');

	        	$warehouseArr['warehouseName'] = request()->param('warehouseName/s')?request()->param('warehouseName/s'):'';
	        	if(!$warehouseArr['warehouseName']) errorMsg(400,'仓库名称不能为空');

	        	$warehouseArr['wareType'] = request()->param('wareType/s')?request()->param('wareType/s'):'';
	        	if(!$warehouseArr['wareType']) errorMsg(400,'仓库属性需要选择');

	        	$warehouseArr['warehouseType'] = request()->param('warehouseType/s')?request()->param('warehouseType/s'):'';
	        	if(!$warehouseArr['warehouseType']) errorMsg(400,'仓库类型需要选择');

	        	$warehouseArr['companyId'] = $this->companyId?$this->companyId:0;

	        	$warehouseArr['companyName'] = request()->param('companyName/s')?request()->param('companyName/s'):'';
	        	$warehouseArr['province'] = request()->param('province/s')?request()->param('province/s'):'';
	        	$warehouseArr['city'] = request()->param('city/s')?request()->param('city/s'):'';
	        	$warehouseArr['district'] = request()->param('district/s')?request()->param('district/s'):'';
	        	$warehouseArr['warehouseAddr'] = request()->param('warehouseAddr/s')?request()->param('warehouseAddr/s'):'';

	        	$warehouseArr['createUserId'] = $this->userId;
	        	$warehouseArr['updateTime'] = date('Y-m-d H:i:s',time());

	        	$warehouseArr['remark'] = request()->param('remark/s')?request()->param('remark/s'):'';

        	}catch(\Exception $e){
                errorMsg(400,$e->getMessage());
            }
            //数据库操作
            //开始事务s
            Db::startTrans();
            try{
                //添加仓库
                Db::name('warehouse')->where('id',$id)->update($warehouseArr);
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            if(!$id){
                $this->error('仓库信息错误',$this->referer);
            }
            $where['w.id'] = $id;
            $where['w.status'] = 1;
            $data = db('warehouse')->alias('w')->where($where)->find();
            if(!$data){
                $this->error('未查询到仓库信息',$this->referer);
            }
            $companyName = db('company')->field('companyName')->where('companyId='.$this->companyId)->find();
        	$wareType = db('ware_type')->field('name')->select();
            $provinceName = db('area')->field('provinceName')->group('province')->order('id asc')->select();
            $result = db('warehouse')->alias('w')->join('contacts c','w.contactsId=c.id','left')->field('w.*,c.contName,c.conTel')->where($where)->find();
            $this->assign('id',$id);
            $this->assign('nav','warehouse');
            $this->assign('subNav','thirdWarehouse');
            $this->assign('wareType',$wareType);
            $this->assign('companyName',$companyName['companyName']);
            $this->assign('provinceName',$provinceName);
            $this->assign('result',$result);
            return $this->fetch();
        }
    }

   	//作者：ww
    //功能：对应仓库货架
    public function storageList(){
    	$id = request()->param('id/s')?request()->param('id/s'):0;
    	$value = request()->param('value/s')?request()->param('value/s'):'';
    	$stype = request()->param('stype/s')?request()->param('stype/s'):'';
    	
    	$where = " s.status=1 and s.warehouseId={$id} ";
    	if($value){
    		$where .= " and (w.warehouseName like '%".$value."%' or s.shelfNumber like '".$value."' )";
    	}
    	if($stype){
    		$where .= " and s.stype={$stype} ";
    	}
    	$list = db('storage')->alias('s')->join('warehouse w','w.id=s.warehouseId','left')->field('s.*,w.warehouseName')->where($where)->paginate();

    	$this->assign('button',Config::get('func'.Session::get('authId').'.selfWarehouse'));
    	$this->assign('id',$id);
    	$this->assign('list',$list);
        $this->assign('nav','warehouse');
        $this->assign('subNav','selfWarehouse');
    	return $this->fetch();
    }

    //作者：ww
    //功能：禁用货架
    public function delStorage(){
    	$ids = request()->param('ids/s')?request()->param('ids/s'):'';
    	$storageArr['status'] = 0;
    	Db::startTrans();
    	try{
    		Db::name('storage')->where('id','in',[$ids])->update($storageArr);
            Db::commit();
            errorMsg(200,'done');
    	}catch(\Exception $e){
    		Db::rollback();
            errorMsg(400,$e->getMessage());
    	}
    }


    //作者：ww
    //功能：货架回收站
    public function storageDelList(){

    	$value = request()->param('value/s')?request()->param('value/s'):'';
    	$stype = request()->param('stype/s')?request()->param('stype/s'):'';
    	
    	$where = " s.status=0 ";
    	if($value){
    		$where .= " and (w.warehouseName like '%".$value."%' or s.shelfNumber like '".$value."' )";
    	}
    	if($stype){
    		$where .= " and s.stype={$stype} ";
    	}
    	$list = db('storage')->alias('s')->join('warehouse w','w.id=s.warehouseId','left')->field('s.*,w.warehouseName')->where($where)->paginate();
    	$this->assign('list',$list);
        $this->assign('nav','warehouse');
        $this->assign('subNav','selfWarehouse');
    	return $this->fetch();
    }

    //作者：ww
    //功能：省市区三级联动
    public function changeProvince(){
    	$provinceName = request()->param('provinceName/s')?request()->param('provinceName/s'):'';
        if ($provinceName) {
            $cityList = db('area')->field('cityName')->where('provinceName="'.$provinceName.'"')->group('city')->order('id asc')->select();
            errorMsg(200,'',$cityList);
        }
    }

    //作者：ww
    //功能：省市区三级联动
    public function changeCity(){
        $cityName = request()->param('cityName/s')?request()->param('cityName/s'):'';
        if ($cityName) {
            $areaList = db('area')->field('areaName')->where('cityName="'.$cityName.'"')->order('id asc')->select();
            errorMsg(200,'',$areaList);
        }
    }
}
