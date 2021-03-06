<?php

namespace app\index\controller;

use think\Controller;
use think\Session;
use think\Config;
use app\index\model\Enclosure;
use think\Db;

class OpenController extends Controller
{
    protected $referer='';
    protected $userId=0;
    protected $authId=0;
    protected $isAdmin=0;
    protected $companyId=0;
    protected $batchTypeArr=array(1=>'正常',2=>'损坏（可用）',3=>'损坏（不可用）',5=>'其他');//批次类型

    public function _initialize()
    {
        $this->userId=Session::get('userId');
        $this->authId=Session::get('authId');
        $this->isAdmin=Session::get('isAdmin');
        $this->companyId=Session::get('companyId');

        /*状态*/
        $statusArr=Config::get('status');
        $this->assign('statusArr',$statusArr);

        //上一页url
        $this->referer=request()->server('HTTP_REFERER');
        if(!$this->referer){
            //如果上一页url不存在，则设为主页
            $this->referer=request()->server('SERVER_NAME');
        }
        $this->assign('referer',$this->referer);
    }


    //作者：于明明
    //功能：选择供应商
    public function getSupplier(){
        return $this->fetch();
    }

    //作者：于明明
    //功能：关联采购单
    public function getPurchase(){
        return $this->fetch();
    }

    //作者：于明明
    //功能：新增商品
    public function getGoods(){
        return $this->fetch();
    }

    //作者：于明明
    //功能：查看破损批次详情
    public function typeOpen(){
        $batchId=request()->param('batchId/d')?request()->param('batchId/d'):0;
        $data=array();
        $encls=array();
        if($batchId>0){
            $where['b.batchId']=$batchId;
            $where['b.companyId']=$this->companyId;
            $data=db('batch')->alias('b')->join('erp_goods g','g.id=b.goodsId','left')->field('b.batchId,b.batch,b.amount,b.shelves,b.bSurStock,b.bUsingStock,b.type,b.expireDate,b.enclIds,g.goodsCode,g.goodsName')->where($where)->find();
            if($data){
                $encls=Enclosure::all($data['enclIds']);
            }
        }
        $this->assign('data',$data);
        $this->assign('encls',$encls);
        $this->assign('batchTypeArr',$this->batchTypeArr);
        return $this->fetch();
    }

    //作者：于明明
    //功能：编辑批次
    public function editBatch(){
        $batchId=request()->param('batchId/d')?request()->param('batchId/d'):0;
        $data=array();
        if($batchId){
            $where['b.batchId']=$batchId;
            $where['b.companyId']=$this->companyId;
            $data=db('batch')->alias('b')->join('erp_goods g','g.id=b.goodsId','left')->field('b.batchId,b.batch,b.amount,b.shelves,b.bSurStock,b.bUsingStock,b.type,b.expireDate,b.enclIds,g.goodsCode,g.goodsName')->where($where)->find();
        }
        $this->assign('data',$data);
        $this->assign('batchTypeArr',$this->batchTypeArr);
        $this->assign('id',$batchId);
        return $this->fetch();
    }

    //作者: ww
    //功能：货架信息添加
    public function addStorage(){
        $id = request()->param('id/s')?request()->param('id/s'):0;
        if(request()->isPost()){
            try {
                $storageArr = array();
                $storageArr['stype'] = request()->param('stype/s')?request()->param('stype/s'):0;
                if(!$storageArr['stype']) errorMsg(400,'货架层数不能为空');
                $storageArr['shelfNumber'] = request()->param('shelfNumber/s')?request()->param('shelfNumber/s'):0;
                if(!$storageArr['shelfNumber']) errorMsg(400,'货架编码不能为空');
                $storageArr['warehouseId'] = request()->param('wareId/s')?request()->param('wareId/s'):0;
                if(!$storageArr['warehouseId']) errorMsg(400,'仓库信息获取有误！请刷新重试！');
                $storageArr['createTime'] = date('Y-m-d H:i:s',time());
                $storageArr['updateTime'] = date('Y-m-d H:i:s',time());
            }catch(\Exception $e){
                errorMsg(400,$e->getMessage());
            }
            //数据库操作
            //开始事务s
            Db::startTrans();
            try{
                //添加仓库
                Db::name('storage')->insert($storageArr);
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            $warehouse = db('warehouse')->field('warehouseName')->where('id='.$id)->find();
            $this->assign('id',$id);
            $this->assign('warehouse',$warehouse);
            return $this->fetch();
        }
    }


    //作者: ww
    //功能：货架信息修改
    public function editStorage(){
        $storageId = request()->param('storageId/s')?request()->param('storageId/s'):0;
        if(request()->isPost()){
            try {
                $storageArr = array();
                $storageArr['stype'] = request()->param('stype/s')?request()->param('stype/s'):0;
                if(!$storageArr['stype']) errorMsg(400,'货架层数不能为空');
                $storageArr['shelfNumber'] = request()->param('shelfNumber/s')?request()->param('shelfNumber/s'):0;
                if(!$storageArr['shelfNumber']) errorMsg(400,'货架编码不能为空');
                $storageArr['updateTime'] = date('Y-m-d H:i:s',time());
            }catch(\Exception $e){
                errorMsg(400,$e->getMessage());
            }
            //数据库操作
            //开始事务s
            Db::startTrans();
            try{
                //修改货架
                Db::name('storage')->where('id='.$storageId)->update($storageArr);
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            $list = db('storage')->alias('s')->join('warehouse w','w.id=s.warehouseId','left')->field('s.*,w.warehouseName')->where('s.id='.$storageId)->find();
            $this->assign('storageId',$storageId);
            $this->assign('list',$list);
            return $this->fetch();
        }
    }
}
