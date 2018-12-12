<?php

namespace app\index\controller;

use think\Db;
use think\Controller;
use Think\Exception;
use think\Session;
use think\Config;
use app\index\model\Users;
use app\index\model\StockGoods;

class BaseController extends Controller
{
    protected $referer='';
    protected $userId=0;
    protected $authId=0;
    protected $isAdmin=0;
    protected $companyId=0;

    public function _initialize()
    {
        $this->userId=Session::get('userId');
        $this->authId=Session::get('authId');
        $this->isAdmin=Session::get('isAdmin');
        $this->companyId=Session::get('companyId');

        //如果该用户id或者权限id异常，则重新登陆
        if(!$this->userId){
            Session::clear();
            if(request()->isPost()){
                errorMsg(300,'请先登陆！');
            }else{
                $this->error('请先登陆','/Index/User/login');
            }
        }

        //如果该用户id或者权限id异常，则重新登陆
        if(!$this->authId && !$this->isAdmin){
            $this->logout();
            if(request()->isPost()){
                errorMsg(300,'请先登陆！');
            }else{
                $this->error('请先登陆','/Index/User/login');
            }
        }

        //判断会话是否过期
        if (time() - session::get('session_start_time') > 3600) {
            $this->logout();
            if(request()->isPost()){
                errorMsg(300,'登陆已经过期，请重新登陆！');
            }else {
                $this->error('登陆已经过期', '/Index/User/login');
            }
        }

        //非管理员验证权限
        if(!$this->isAdmin){
            $this->checkAuth(request()->action());
        }

        //设置二级导航的
        Session::set('subNav',strtolower(request()->controller()));

        //顶部按钮
        $topNav=Config::get('func'.$this->authId.'.top');
        $this->assign('topNav',$topNav);

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

    //验证当前用户是否有权限使用该模块
    protected function checkAuth($authName){
        $str='';
        if(!$this->authId){
            $str='当前员工没有权限使用系统，请重新登陆！';
        }else{
            $result=db('modular')->alias('m')->join('erp_auth_modular am',"am.modularId=m.modularId and am.authId='{$this->authId}'",'left')->field('m.modularId,am.modularId as amoudlarId')->where("m.modularEnName='{$authName}'")->find();
            if($result){
                if(!$result['amoudlarId']){
                    $str='当前员工没有使用该功能的权限，请验证！';
                }
            }
        }
        if($str) {
            if (request()->isPost()) {
                errorMsg(400, $str);
            } else {
                $this->error($str, '/Index/Index/index');
            }
        }
    }

    //退出登陆
    protected function logout(){
        $users=Users::get($this->userId);
        $users->isLogin=0;
        $users->save();
        Session::clear();
    }

    /*更新商品库存
     * goodsId:商品id
     * amount：需要更新的库存数量
     * type：更新类型，1-新增在途库存，2-在途库存转未质检库存，3-直接新增未质检库存，4-未质检库存转可用库存
     * 5-可用库存转占用库存，6-未质检库存转破损库存，7-可用库存转破损库存，8-可用高库存转占用库存，
     * 9-库存销毁、出库、转移等，10-新增临期库存，11-新增过期库存
    */
    protected function stockGoodsEdit($goodsId,$amount,$type){
        $where['companyId']=$this->companyId;
        $where['goodsId'] = $goodsId;
        $stockGoods=StockGoods::get($where);
        $saveType=1;//默认更新
        if(!$stockGoods){//如果不存在该商品，则添加
            $saveType=2;
            $stockGoods=new StockGoods;
        }
        $type=$saveType.$type;//拼接类型
        $isSave=1;//判断是否需要处理
        $where=array();//更新条件
        try{
            switch(intval($type)){
                case 11://新增在途库存
                    $stockGoods->amount=$stockGoods->amount+$amount;
                    $stockGoods->onWayStock=$stockGoods->onWayStock+$amount;
                    break;
                case 21://新增在途库存
                    $stockGoods->companyId=$this->companyId;
                    $stockGoods->goodsId=$goodsId;
                    $stockGoods->amount=$amount;
                    $stockGoods->onWayStock=$amount;
                    break;
                case 12://在途库存转未质检库存
                    $stockGoods->onWayStock=$stockGoods->onWayStock-$amount;
                    $stockGoods->inQCStock=$stockGoods->inQCStock+$amount;
                    $where['onWayStock']=['>=',$amount];
                    break;
                case 13://直接新增未质检库存
                    $stockGoods->amount=$stockGoods->amount+$amount;
                    $stockGoods->inQCStock=$stockGoods->inQCStock+$amount;
                    break;
                case 23://直接新增未质检库存
                    $stockGoods->companyId=$this->companyId;
                    $stockGoods->goodsId=$goodsId;
                    $stockGoods->amount=$amount;
                    $stockGoods->inQCStock=$amount;
                    break;
                case 14://未质检库存转可用库存，扣未质检库存
                    $stockGoods->inQCStock=$stockGoods->inQCStock-$amount;
                    $stockGoods->surStock=$stockGoods->surStock+$amount;
                    $where['inQCStock']=['>=',$amount];
                    break;
                case 15://可用库存转占用库存，扣可用库存
                    $stockGoods->surStock=$stockGoods->surStock-$amount;
                    $stockGoods->usingStock=$stockGoods->usingStock+$amount;
                    $where['surStock']=['>=',$amount];
                    break;
                case 16://QC质检时新增破损库存，扣未质检库存
                    $stockGoods->inQCStock=$stockGoods->inQCStock-$amount;
                    $stockGoods->badStock=$stockGoods->badStock+$amount;
                    $where['inQCStock']=['>=',$amount];
                    break;
                case 17://批次编辑时新增破损库存，扣可用库存
                    $stockGoods->surStock=$stockGoods->surStock-$amount;
                    $stockGoods->badStock=$stockGoods->badStock+$amount;
                    $where['surStock']=['>=',$amount];
                    break;
                case 18://新增订单已使用库存，扣总库存、占用库存，
                    $stockGoods->amount=$stockGoods->amount-$amount;
                    $stockGoods->usingStock=$stockGoods->usingStock-$amount;
                    $stockGoods->usedStock=$stockGoods->usedStock+$amount;
                    $where['amount']=['>=',$amount];
                    $where['usingStock']=['>=',$amount];
                    break;
                case 19://库存销毁、出库、转移等，扣总库存、可用库存
                    $stockGoods->amount=$stockGoods->amount-$amount;
                    $stockGoods->surStock=$stockGoods->surStock-$amount;
                    $where['amount']=['>=',$amount];
                    $where['surStock']=['>=',$amount];
                    break;
                case 110://新增临期库存
                    $stockGoods->adventStock=$stockGoods->adventStock+$amount;
                    break;
                case 111://新增过期库存，扣总库存、可用库存
                    $stockGoods->amount=$stockGoods->amount-$amount;
                    $stockGoods->surStock=$stockGoods->surStock+$amount;
                    $stockGoods->overdueStock=$stockGoods->overdueStock+$amount;
                    $where['amount']=['>=',$amount];
                    break;
                default:
                    $isSave=0;
                    break;

            }
        }catch(Exception $e){
            $isSave=0;
        }
        if($isSave){
            $stockGoods->updateTime=date('Y-m-d H:i:s');
            Db::startTrans();
            try{
                if(!$stockGoods->save(array(),$where)) exception('更新条件不满足！');
                Db::commit();
            }catch(Exception $e){
                $isSave=0;
                Db::rollback();
            }
        }
        return $isSave;
    }

}
