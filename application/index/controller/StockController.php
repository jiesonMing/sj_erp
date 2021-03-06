<?php

namespace app\index\controller;

use app\index\model\Goods;
use app\index\model\PurchaseGoods;
use app\index\model\StockGoods;
use think\Db;
use think\Config;
use Think\Exception;

class StockController extends BaseController
{
    //作者：于明明
    //功能：库存（商品）
   public function stockGoods(){
        $value=request()->param('value/s')?request()->param('value/s'):'';
        $where='sg.companyId='.$this->companyId.' and sg.status=1';

       $list=db('stock_goods')->alias('sg')->join('erp_goods g','g.id=sg.goodsId','left')->field('sg.stockGoodsId,sg.goodsId,sg.amount,sg.onWayStock,sg.inQCStock,sg.surStock,sg.usingStock,sg.badStock,sg.adventStock,sg.overdueStock,sg.updateTime,g.goodsCode,g.goodsName')->where($where)->paginate();

       $this->assign('button',Config::get('func'.$this->authId.'.stockGoods'));
       $this->assign('nav','stock');
       $this->assign('subNav','stockGoods');
       $this->assign('list',$list);
       $this->assign('value',$value);
       return $this->fetch();
   }

   //作者：于明明
    //功能：库存商品详情
    public function stockGoodsDetails(){
        $goodsId=request()->param('id/d')?request()->param('id/d'):0;
        $goods=Goods::get($goodsId);
        $stockGoods=array('amont'=>0,'badStock'=>0,'onWayStock'=>0,'inQCStock'=>0);
        $stockDetails=array();
        $count=1;
        if($goods){
            //库存商品统计
            $stockGoods=StockGoods::get($goodsId);
            //采购库存
            $purchaseGoods=db('purchase_goods')->alias('pg')->join('erp_purchase p','p.id=pg.purchaseId')->field("pg.purchaseGoodsId as id,pg.inBatch AS surStock,0 AS usingStock,p.contractNum as cardNum,p.createTime,'采购在途' as source,'未知' AS warehouseName,'未知' AS expireDate,p.status,0 as 'check'")->where('pg.goodsId='.$goodsId.' AND pg.status=1 AND p.`companyId`=1')->select();
            if($purchaseGoods){
                foreach($purchaseGoods as $value){
                    $stockDetails[$count]=$value;
                    $stockDetails[$count]['count']=$count;
                    $count++;
                }
            }
            //在途库存
            $onWayGoods=
        }
        $this->assign('nav','stock');
        $this->assign('subNav','stockGoods');
        $this->assign('goods',$goods);
        $this->assign('stockGoods',$stockGoods);
        $this->assign('stockDetails',$stockDetails);
        return $this->fetch();
    }
}
