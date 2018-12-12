<?php

namespace app\index\controller;

use think\Db;
use think\ConnectMysqli;
use think\Session;
use think\Config;
use app\index\model\Auth;
use app\index\model\Modular;
use app\index\model\UserAuth;

class AuthController extends BaseController
{
    //作者：于明明
    //功能：模块列表
    public function modularList()
    {
        $list=$this->getModularTab();

        $this->assign('button',Config::get('func'.$this->authId.'.modularList'));
        $this->assign('nav','auth');
        $this->assign('subNav','modularList');
        $this->assign('list',$list);
        return $this->fetch();
    }

    //作者：于明明
    //功能：树状模块列表
    public function modularTree(){
        $list=$this->getModularTreeRec();

        $this->assign('button',Config::get('func'.$this->authId.'.modularList'));
        $this->assign('nav','auth');
        $this->assign('subNav','modularList');
        $this->assign('list',$list);
        return $this->fetch();
    }

    //作者：于明明
    //功能：模块回收站
    public function modularDelList(){
        $value=request()->param('value/s')?request()->param('value/s'):'';
        $where='m.status=0';
        if($value){
            $where.=" and ( (m.modularId='{$value}') or (m.modularName='{$value}') or (m.modularName='{$value}') )";
        }
        $lists=db('modular')->alias('m')->join('erp_modular pm','pm.modularId=m.parentModularId','left')->where($where)->field('m.modularId,m.modularName,m.modularEnName,m.modularFunName,IFNULL(pm.modularName,\'顶级模块\') AS pmodularName')->order('m.modularId asc')->paginate();

        $this->assign('button',Config::get('func'.$this->authId.'.modularList'));
        $this->assign('nav','auth');
        $this->assign('subNav','modularList');
        $this->assign('value',$value);
        $this->assign('list',$lists);
        return $this->fetch();
    }

    //作者：于明明
    //功能：新增模块
    public function addModular(){
        if(request()->isPost()){
            $modular=new Modular;
            $modular->modularName=request()->param('modularName/s')?request()->param('modularName/s'):'';
            if(!$modular->modularName) errorMsg(400,'模块名称不能为空');
            $modular->modularEnName=request()->param('modularEnName/s')?request()->param('modularEnName/s'):'';
            if(!$modular->modularEnName) errorMsg(400,'模块英文名称不能为空');
            $modular->modularFunName=request()->param('modularFunName/s')?request()->param('modularFunName/s'):'';
            if(!$modular->modularFunName) errorMsg(400,'模块方法名称不能为空');
            $where['modularName']=$modular->modularName;
            $where['modularFunName']=$modular->modularFunName;
            $where['modularEnName']=$modular->modularEnName;
            $result=db('modular')->where($where)->find();
            if($result){
                errorMsg(400,'当前模块方法名称已经存在');
            }
            $modular->modularIcon=request()->param('modularIcon/s')?request()->param('modularIcon/s'):'';
            $modular->modularClass=request()->param('modularClass/s')?request()->param('modularClass/s'):'';
            $modular->isNav=request()->param('isNav/d')?request()->param('isNav/d'):0;
            $modular->showName=request()->param('showName/d')?request()->param('showName/d'):0;
            $modular->parentModularId=request()->param('parentModularId/d')?request()->param('parentModularId/d'):'';
            if($modular->parentModularId==0){
                $modular->level=1;
            }else{
                $result=db('modular')->where('modularId',$modular->parentModularId)->find();
                if(!$result) errorMsg(400,'所选上级模块有误，请刷新重试！');
                if((!$result['isNav']) && ($result['level'])>1) errorMsg(400,'所选上级模块属于功能键，不允许添加下级模块');
                if($modular->isNav && $result['level']==3) errorMsg(400,'所选上级模块已经是最底层导航，只能添加功能键！');
                $modular->level=$result['level']+1;
            }
            $modular->remark=request()->param('remark/s')?request()->param('remark/s'):'';
            //数据库操作
            //开始事务
            Db::startTrans();
            try{
                //添加模块
                $modular->save();
                //更新管理员权限
                if($modular->isNav==0){
                    $this->setButtonNav('',0);
                }else{
                    $this->getNavStr();
                    $this->getSubNavStr();
                }
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            /*使用存储过程查询属性模块结构
            $start=microtime(true);
            $link=ConnectMysqli::getIntance();
            $modularList=$link->getAll("call pro_show_childLst(0,1,1,1)");
            $link->clear();
            echo microtime(true)-$start;*/
            $parentId=request()->param('parentId/d')?request()->param('parentId/d'):0;
            $modularList=$this->getModularOption(0,$parentId);

            $this->assign('nav','auth');
            $this->assign('subNav','modularList');
            $this->assign('modularList',$modularList);
            return $this->fetch();
        }
    }

    //作者：于明明
    //功能：添加/修改模块
    public function editModular(){
        $id=request()->param('id/d')?request()->param('id/d'):0;
        if(request()->isPost()){
            if(!$id) errorMsg(400,'模块信息有误，请刷新重试！');
            $modular=Modular::get($id);
            if(!$modular) errorMsg(400,'当前模块不存在，请刷新重试！');
            $modular->modularName=request()->param('modularName/s')?request()->param('modularName/s'):'';
            if(!$modular->modularName) errorMsg(400,'模块名称不能为空');
            $modular->modularEnName=request()->param('modularEnName/s')?request()->param('modularEnName/s'):'';
            if(!$modular->modularEnName) errorMsg(400,'模块英文名称不能为空');
            $modular->modularFunName=request()->param('modularFunName/s')?request()->param('modularFunName/s'):'';
            if(!$modular->modularFunName) errorMsg(400,'模块方法名称不能为空');
            $where['modularName']=$modular->modularName;
            $where['modularEnName']=$modular->modularEnName;
            $where['modularFunName']=$modular->modularFunName;
            $result=db('modular')->where($where)->find();
            if($result){
                if($result['modularId']!=$id) errorMsg(400,'当前模块方法名称已存在');
            }
            $modular->modularIcon=request()->param('modularIcon/s')?request()->param('modularIcon/s'):'';
            if(!$modular->modularIcon) errorMsg(400,'模块图标字体不能为空');
            $modular->modularClass=request()->param('modularClass/s')?request()->param('modularClass/s'):'';
            $modular->isNav=request()->param('isNav/d')?request()->param('isNav/d'):0;
            $modular->showName=request()->param('showName/d')?request()->param('showName/d'):0;
            $modular->parentModularId=request()->param('parentModularId/d')?request()->param('parentModularId/d'):'';
            if($modular->parentModularId==0){
                $data['level']=1;
            }else{
                $result=db('modular')->where('modularId',$modular->parentModularId)->find();
                if(!$result) errorMsg(400,'所属上级模块有误，请刷新重试！');
                if((!$result['isNav']) && ($result['level'])>1) errorMsg(400,'所属上级模块属于功能键，不允许添加下级模块');
                if($modular->isNav && $result['level']==3) errorMsg(400,'所选上级模块已经是最底层导航，只能添加功能键！');
                $data['level']=$result['level']+1;
            }
            $modular->remark=request()->param('remark/s')?request()->param('remark/s'):'';
            //数据库操作
            //开始事务
            Db::startTrans();
            try{
                //添加模块
                $modular->save();
                //更新管理员权限
                if($modular->isNav==0){
                    $this->setButtonNav('',0);
                }else{
                    $this->getNavStr();
                    $this->getSubNavStr();
                }
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            if($id>0){
                $data=db('modular')->where('modularId',$id)->where('status',1)->find();
                if(!$data){
                    $this->error('未查询到模块信息',$this->referer);
                }
            }else{
                $this->error('错误的模块信息',$this->referer);
            }
            $modularList=$this->getModularOption(0,$data['parentModularId']);

            $this->assign('nav','auth');
            $this->assign('subNav','modularList');
            $this->assign('data',$data);
            $this->assign('modularList',$modularList);
            $this->assign('id',$id);
            return $this->fetch();
        }
    }

    //作者：于明明
    //功能：删除模块
    public function delModular(){
        $modularId=request()->param('modularId/d')?request()->param('modularId/d'):0;
        if($modularId<=0) errorMsg(400,'模块信息错误，请刷新重试！');
        $modular=Modular::get($modularId);
        $modularAuth=$modular->modularAuth;
        if($modularAuth) errorMsg(400,'当前模块已添加到其他权限组下，不允许删除！');
        $where['parentModularId']=$modularId;
        $where['status']=1;
        $result=db('modular')->where($where)->select();
        if($result) errorMsg(400,'当前模块拥有下级模块，不能删除！');
        try{
            //数据库操作
            //开始事务
            Db::startTrans();
            //加入回收站
            $modular->status=0;
            $modular->save();
            //更新管理员权限
            if($modular->isNav==0){
                $this->setButtonNav('',0);
            }else{
                $this->getNavStr();
                $this->getSubNavStr();
            }
            Db::commit();
            errorMsg(200,'done');
        }catch(\Exception $e){
            Db::rollback();
            errorMsg(400,$e->getMessage());
        }
        errorMsg(400,'none');
    }

    //作者：于明明
    //功能：启用模块
    public function useModular(){
        $modularId=request()->param('modularId/d')?request()->param('modularId/d'):0;
        if($modularId){
            $modular=Modular::get($modularId);
            $where['modularId']=$modular->parentModularId;
            $where['status']=0;
            $result=db('modular')->where($where)->select();
            if($result) errorMsg(400,'当前模块的上级模块已被加入回收站，不能启用！');
            try{
                //数据库操作
                //开始事务
                Db::startTrans();
                //加入回收站
                $modular->status=1;
                $modular->save();
                //更新管理员权限
                if($modular->isNav==0){
                    $this->setButtonNav('',0);
                }else{
                    $this->getNavStr();
                    $this->getSubNavStr();
                }
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }

        }else{
            errorMsg(400,'模块信息错误，请刷新重试！');
        }
    }

    //作者：于明明
    //功能：彻底删除模块
    public function purgeModular(){
        $modularId=request()->param('modularId/d')?request()->param('modularId/d'):0;
        if($modularId){
            $modular=Modular::get($modularId);
            $where['parentModularId']=$modularId;
            $result=db('modular')->where($where)->select();
            if($result) errorMsg(400,'当前模块的有用下级模块，不能彻底删除！');
            try{
                //数据库操作
                //开始事务
                Db::startTrans();
                //加入回收站
                $modular->delete();
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }

        }else{
            errorMsg(400,'模块信息错误，请刷新重试！');
        }
    }

    //作者：于明明
    //功能：权限组列表
    public function authsList(){
        $value=request()->param('value/s')?request()->param('value/s'):'';
        $where='companyId='.$this->companyId.' and status=1';
        if($value){
            $where.=" and ( (authName='{$value}') or (authId='{$value}') )";
        }
        $lists=db('auths')->where($where)->paginate();
        $data=array();
        foreach($lists as $key=>$list){
            $data[$key]=$list;
            $where=array();
            $where['ua.authId']=$list['authId'];
            $where['u.status']=1;
            $users=db('User_auth')->alias('ua')->join('erp_users u','u.userId=ua.userId','left')->where($where)->field('u.userId,u.nickName')->select();
            $str=$strName='';
            $count=0;
            if($users){
                foreach($users  as $user){
                    $strName.='<a href="/Index/Ucenter/userList/value/'.$user['userId'].'">'.$user['nickName'].'</a>';
                    $count++;
                    if($count<3){
                        $str.=$user['nickName'].'，';
                    }elseif($count==3){
                        $str.=$user['nickName'].' ...【共';
                    }
                }
                if($count>=3){
                    $str.=$count.'人】';
                }
            }
            $data[$key]['str']=$str;
            $data[$key]['strName']=$strName;
        }

        $this->assign('button',Config::get('func'.$this->authId.'.authsList'));
        $this->assign('nav','auth');
        $this->assign('subNav','authsList');
        $this->assign('data',$data);
        $this->assign('value',$value);
        $this->assign('list',$lists);
        return $this->fetch();
    }

    //作者：于明明
    //权限：权限组回收站
    public function authDelList(){
        $value=request()->param('value/s')?request()->param('value/s'):'';
        $where='companyId='.$this->companyId.' and status=0';
        if($value){
            $where.=" and ( (authName='{$value}') or (authId='{$value}') )";
        }
        $lists=db('auths')->where($where)->paginate();

        $this->assign('button',Config::get('func'.$this->authId.'.authsList'));
        $this->assign('nav','auth');
        $this->assign('subNav','authsList');
        $this->assign('value',$value);
        $this->assign('list',$lists);
        return $this->fetch();
    }

    //作者：于明明
    //功能：添加权限组
    public function addAuth(){
        if(request()->isPOst()){
            $auth=new Auth;
            $auth->authName=request()->param('authName/s')?request()->param('authName/s'):'';
            if(!$auth->authName) errorMsg(400,'权限名称不能为空');
            $auth->companyId=$this->companyId;
            $where['authName']=$auth->authName;
            $where['companyId']=$auth->companyId;
            $result=db('auths')->where($where)->find();
            if($result) errorMsg(400,'该权限名称已经存在');
            $auth->remark=request()->param('remark/s')?request()->param('remark/s'):'';

            //权限组拥有模块
            $check1=request()->param('check1/s')?request()->param('check1/s'):'';
            $check2=request()->param('check2/s')?request()->param('check2/s'):'';
            $check3=request()->param('check3/s')?request()->param('check3/s'):'';
            $isFunc=request()->param('isFunc/s')?request()->param('isFunc/s'):'';
            if(!($check1)) errorMsg(400,'请选择模块');
            $check1=trim($check1,',');
            $check2=trim($check2,',');
            $check3=trim($check3,',');
            $isFunc=trim($isFunc,',');
            $check1Arr=explode(',',$check1);
            $check2Arr=explode(',',$check2);
            $check3Arr=explode(',',$check3);
            $isFuncArr=explode(',',$isFunc);
            foreach($check1Arr as $modular){
                $authModularArr[]['modularId']=$modular;
            }
            foreach($check2Arr as $modular){
                $authModularArr[]['modularId']=$modular;
            }
            foreach($check3Arr as $modular){
                $authModularArr[]['modularId']=$modular;
            }
            foreach($isFuncArr as $modular){
                $authModularArr[]['modularId']=$modular;
            }
            //数据库操作
            //开始事务
            Db::startTrans();
            try{
                $auth->save();
                $auth->authModular()->saveAll($authModularArr);
                $this->getNavStr($check1.','.$check2,$auth->authId);
                $this->getSubNavStr($check3,$auth->authId);
                $this->setButtonNav($isFunc,$auth->authId);
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            $modularList=$this->getTopModularCheck();

            $this->assign('nav','auth');
            $this->assign('subNav','authsList');
            $this->assign('modularList',$modularList);
            return $this->fetch();
        }
    }

    //作者：于明明
    //功能：修改权限组
    public function editAuth(){
        $id=request()->param('id/d')?request()->param('id/d'):0;
        if(request()->isPost()){
            if(!$id) errorMsg(400,'模块信息有误，请刷新重试！');
            $auth=Auth::get($id);
            $auth->authName=request()->param('authName/s')?request()->param('authName/s'):'';
            $where['authName']=$auth->authName;
            $where['companyId']=$this->companyId;
            $result=db('auths')->where($where)->find();
            if($result){
                if($result['authId']!=$id) errorMsg(400,'当前权限名称已存在');

            }
            $auth->remark=request()->param('remark/s')?request()->param('remark/s'):'';
            //权限组拥有模块
            $check1=request()->param('check1/s')?request()->param('check1/s'):'';
            $check2=request()->param('check2/s')?request()->param('check2/s'):'';
            $check3=request()->param('check3/s')?request()->param('check3/s'):'';
            $isFunc=request()->param('isFunc/s')?request()->param('isFunc/s'):'';
            if(!($check1)) errorMsg(400,'请选择模块');
            $check1=trim($check1,',');
            $check2=trim($check2,',');
            $check3=trim($check3,',');
            $isFunc=trim($isFunc,',');
            $check1Arr=explode(',',$check1);
            $check2Arr=explode(',',$check2);
            $check3Arr=explode(',',$check3);
            $isFuncArr=explode(',',$isFunc);
            foreach($check1Arr as $modular){
                $authModularArr[]['modularId']=$modular;
            }
            foreach($check2Arr as $modular){
                $authModularArr[]['modularId']=$modular;
            }
            foreach($check3Arr as $modular){
                $authModularArr[]['modularId']=$modular;
            }
            foreach($isFuncArr as $modular){
                $authModularArr[]['modularId']=$modular;
            }
            //数据库操作
            //开始事务
            Db::startTrans();
            try{
                //添加模块
                $auth->save();
                $auth->authModular()->delete();
                $auth->authModular()->saveAll($authModularArr);
                $this->getNavStr($check1.','.$check2,$auth->authId);
                $this->delSubNavFile($auth->authId);
                $this->getSubNavStr($check3,$auth->authId);
                $this->setButtonNav($isFunc,$auth->authId);
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            if($id>0){
                $data=db('auths')->where('authId',$id)->where('status',1)->find();
                if(!$data){
                    $this->error('未查询到权限组信息',$this->referer);
                }
            }else{
                $this->error('错误的权限组信息',$this->referer);
            }
            $modularList=$this->getTopModularCheck($id);

            $this->assign('nav','auth');
            $this->assign('subNav','authsList');
            $this->assign('data',$data);
            $this->assign('modularList',$modularList);
            $this->assign('id',$id);
            return $this->fetch();
        }
    }

    //作者：于明明
    //功能：配置权限组
    public function delAuth(){
        $authId=request()->param('authId/d')?request()->param('authId/d'):0;
        if($authId<=0) errorMsg(400,'模块信息错误，请刷新重试！');
        try{
            $auth=Auth::get($authId);
            $authUser=$auth->authUser;
            if($authUser) errorMsg(400,'当前权限组下拥有用户，不允许删除！');
            //数据库操作
            //开始事务
            Db::startTrans();
            //添加模块
            $auth->status=0;
            $auth->save();
            Db::commit();
            errorMsg(200,'done');
        }catch(\Exception $e){
            Db::rollback();
            errorMsg(400,$e->getMessage());
        }
        errorMsg(400,'none');
    }

    //作者：于明明
    //功能：启用权限组
    public function useAuth(){
        $authId=request()->param('authId/d')?request()->param('authId/d'):0;
        if($authId<=0) errorMsg(400,'模块信息错误，请刷新重试！');
        try{
            $auth=Auth::get($authId);
            //数据库操作
            //开始事务
            Db::startTrans();
            //添加模块
            $auth->status=1;
            $auth->save();
            Db::commit();
            errorMsg(200,'done');
        }catch(\Exception $e){
            Db::rollback();
            errorMsg(400,$e->getMessage());
        }
        errorMsg(400,'none');
    }

    //作者：于明明
    //功能：彻底删除权限组
    public function purgeAuth(){
        $authId=request()->param('authId/d')?request()->param('authId/d'):0;
        if($authId<=0) errorMsg(400,'模块信息错误，请刷新重试！');
        try{
            $auth=Auth::get($authId);
            //数据库操作
            //开始事务
            Db::startTrans();
            //添加模块
            $auth->delete();
            $this->delAuthFile($authId);
            Db::commit();
            errorMsg(200,'done');
        }catch(\Exception $e){
            Db::rollback();
            errorMsg(400,$e->getMessage());
        }
        errorMsg(400,'none');
    }

    //递归查询模块树形结构
    protected function getModular($parentModularId,$modularId=0){
        $where['parentModularId']=$parentModularId;
        $where['status']=1;
        $result=db('modular')->where($where)->select();
        global $data;
        if($result){
            foreach($result as $value){
                $key=$value['modularId'];
                if($modularId>0){
                    if($key==$modularId){
                        $data[$key]['isModular']=1;
                    }else{
                        $data[$key]['isModular']=0;
                    }
                }else{
                    $data[$key]['isModular']=0;
                }
                $data[$key]['modularName']=$value['modularName'];
                $data[$key]['modularId']=$value['modularId'];
                $data[$key]['level']=$value['level'];
                $data[$key]['parentModularId']=$value['parentModularId'];
                $this->getModular($value['modularId'],$modularId);
            }
        }
        return $data;
    }

    //一次查询模块树状结构字符串
    protected function getModularTree(){
        $result=db('modular')->where('status',1)->order('modularId')->select();
        $data=array();
        $str='';
        if($result){
            foreach($result as $val){
                if($val['level']==1){
                    $data['parent'][$val['modularId']]=$val;
                }else{
                    $data['child'][$val['parentModularId']][$val['modularId']]=$val;
                }
            }
            $modularButton=Config::get('func'.$this->authId.'.modularList');
            foreach($data['parent'] as $value){
                $str.='<li><span class="folder">'.$value['modularName'].'</span>';
                if($modularButton['addModular']){
                    $str.='<a href="/Index/Auth/addModular/parentId/'.$value['modularId'].'"><i class="fa fa-plus"></i></a>';
                }
                $str.='<ul>';
                if(isset($data['child'])){
                    $str.=$this->getModularChildTree($data['child'],$value['modularId']);
                }
                $str.='</ul></li>';
            }
        }
        return $str;
    }

    //获取tree的子模块信息
    protected function getModularChildTree($data,$modularId){
        $str='';
        if(isset($data[$modularId])){
            $result=$data[$modularId];
            unset($data[$modularId]);
            $modularButton=Config::get('func'.$this->authId.'.modularList');
            foreach($result as $value){
                if($value['level']==4){
                    $str.='<li>'.$value['modularName'].'</li>';
                }else{
                    if($value['isNav']){
                        $str.='<li><span class="folder">'.$value['modularName'].'</span>';
                        if($modularButton['addModular']){
                            $str.='<a href="/Index/Auth/addModular/parentId/'.$value['modularId'].'"><i class="fa fa-plus"></i></a>';
                        }
                        $str.='<ul>';
                        $str.=$this->getModularChildTree($data,$value['modularId']);
                        $str.='</ul></li>';
                    }else{
                        $str.='<li>'.$value['modularName'].'</li>';
                    }
                }
            }
        }
        return $str;
    }

    //递归查询模块树状结构字符串
    protected function getModularTreeRec($parentModularId=0){
        $map['parentModularId']=$parentModularId;
        $map['status']=1;
        $result=db('modular')->where($map)->select();
        $str='';
        if($result){
            $modularButton=Config::get('func'.$this->authId.'.modularList');
            foreach($result as $value){
                if($value['level']==4){
                    $str.='<li>'.$value['modularName'].'</li>';
                }elseif($value['level']==1){
                    $str.='<li><span class="folder" style="display:inline;">'.$value['modularName'].'</span>';
                    if($modularButton['addModular']){
                        $str.='&nbsp;<a href="/Index/Auth/addModular/parentId/'.$value['modularId'].'" class="a_c_1785c8"><i class="fa fa-plus"></i></a>';
                    }
                    $str.='<ul>';
                    $str.=$this->getModularTreeRec($value['modularId']);
                    $str.='</ul></li>';
                }else{
                    if($value['isNav']){
                        $str.='<li><span class="folder" style="display:inline;">'.$value['modularName'].'</span>';
                        if($modularButton['addModular']){
                            $str.='&nbsp;<a href="/Index/Auth/addModular/parentId/'.$value['modularId'].'" class="a_c_1785c8"><i class="fa fa-plus"></i></a>';
                        }
                        $str.='<ul>';
                        $str.=$this->getModularTreeRec($value['modularId']);
                        $str.='</ul></li>';
                    }else{
                        $str.='<li>'.$value['modularName'].'</li>';
                    }
                }
            }
        }
        return $str;
    }

    //一次查询模块表格结构字符串
    protected function getModularTab(){
        $result=db('modular')->where('status',1)->order('modularId')->select();
        $data=array();
        $str='';
        if($result) {
            foreach ($result as $val) {
                if ($val['level'] == 1) {
                    $data['parent'][$val['modularId']] = $val;
                } else {
                    $data['child'][$val['parentModularId']][$val['modularId']] = $val;
                }
            }
            $modularButton = Config::get('func' . $this->authId . '.modularList');
            foreach ($data['parent'] as $value) {
                $str .= '<tr class="tr1">';
                $str .= '<td><input type="checkbox" id="checkbox_a' . $value['modularId'] . '" class="chk_3" /><label for="checkbox_a' . $value['modularId'] . '"></label></td>';
                $str .= '<td>' . $value['modularId'] . '</td>';
                $str .= '<td colspan=4 class="font_b">' . $value['modularName'] . '</td>';
                $str .= '<td>顶级菜单</td>';
                $str .= '<td>' . $value['modularEnName'] . '</td>';
                $str .= '<td>' . $value['modularFunName'] . '</td>';
                $str .= '<td>' . $value['modularClass'] . '</td>';
                $str .= '<td>' . $value['modularIcon'] . '</td>';
                $str .= '<td>' . $value['createTime'] . '</td>';
                $str .= '<td class="text_center">' . $value['remark'] . '</td>';
                $str .= '<td class="text_center">';
                if ($modularButton['editModular']) {
                    $str .= '<a href="/Index/Auth/editModular/id/' . $value['modularId'] . '" class="a_list c_1785c8 m_r_10"><i class="fa fa-pencil-square"></i></a>';
                }
                if ($modularButton['delModular']) {
                    $str .= '<a href="javascript:delModular(' . $value['modularId'] . ');" class="a_list c_1785c8"><i class="fa fa-trash-o"></i></a>';
                }
                $str .= '</td></tr>';
                if(isset($data['child'])){
                    $str .= $this->getModularChildTab($data['child'], $value['modularId'], $value['modularName']);
                }
            }
        }
        return $str;
    }

    //获取tab的子模块信息
    protected function getModularChildTab($data,$modularId,$parentName=''){
        $str='';
        if(isset($data[$modularId])){
            $result=$data[$modularId];
            unset($data[$modularId]);
            $modularButton=Config::get('func'.$this->authId.'.modularList');
            foreach($result as $value){
                $str.='<tr class="tr1">';
                $str.='<td><input type="checkbox" id="checkbox_a'.$value['modularId'].'" class="chk_3" /><label for="checkbox_a'.$value['modularId'].'"></label></td>';
                $str.='<td>'.$value['modularId'].'</td>';
                $count=5-$value['level'];
                if($value['level']>1) {
                    for ($i = 1; $i < $value['level']; $i++) {
                        $str .= '<td></td>';
                    }
                }
                if($count>0){
                    $str.='<td colspan="'.$count.'">'.$value['modularName'].'</td>';
                }else{
                    $str.='<td>'.$value['modularName'].'</td>';
                }
                $str.='<td>'.$parentName.'</td>';
                $str.='<td>'.$value['modularEnName'].'</td>';
                $str.='<td>'.$value['modularFunName'].'</td>';
                $str.='<td>'.$value['modularClass'].'</td>';
                $str.='<td>'.$value['modularIcon'].'</td>';
                $str.='<td>'.$value['createTime'].'</td>';
                $str.='<td class="text_center">'.$value['remark'].'</td>';
                $str.='<td class="text_center">';
                if($modularButton['editModular']){
                    $str.='<a href="/Index/Auth/editModular/id/'.$value['modularId'].'" class="a_list c_1785c8 m_r_10"><i class="fa fa-pencil-square"></i></a>';
                }
                if($modularButton['delModular']){
                    $str.='<a href="javascript:delModular('.$value['modularId'].');" class="a_list c_1785c8"><i class="fa fa-trash-o"></i></a>';
                }
                $str.='</td></tr>';
                $str.=$this->getModularChildTab($data,$value['modularId'],$value['modularName']);
            }
        }
        return $str;
    }

    //递归查询模块表格结构字符串
    protected function getModularTabRec($parentModularId=0,$parentName='顶级菜单'){
        $where['parentModularId']=$parentModularId;
        $where['status']=1;
        $result=db('modular')->where($where)->select();
        $str='';
        if($result){
            $modularButton=Config::get('func'.$this->authId.'.modularList');
            foreach($result as $value){
                $str.='<tr class="tr1">';
                $str.='<td><input type="checkbox" id="checkbox_a'.$value['modularId'].'" class="chk_3" /><label for="checkbox_a'.$value['modularId'].'"></label></td>';
                $str.='<td>'.$value['modularId'].'</td>';
                $count=5-$value['level'];
                if($value['level']>1) {
                    for ($i = 1; $i < $value['level']; $i++) {
                        $str .= '<td></td>';
                    }
                }
                if($count==4){
                    $str.='<td colspan="'.$count.'" class="font_b">'.$value['modularName'].'</td>';
                }elseif($count>0){
                    $str.='<td colspan="'.$count.'">'.$value['modularName'].'</td>';
                }else{
                    $str.='<td>'.$value['modularName'].'</td>';
                }
                $str.='<td>'.$parentName.'</td>';
                $str.='<td>'.$value['modularEnName'].'</td>';
                $str.='<td>'.$value['modularFunName'].'</td>';
                $str.='<td>'.$value['modularClass'].'</td>';
                $str.='<td>'.$value['modularIcon'].'</td>';
                $str.='<td>'.$value['createTime'].'</td>';
                $str.='<td class="text_center">'.$value['remark'].'</td>';
                $str.='<td class="text_center">';
                if($modularButton['editModular']){
                    $str.='<a href="/Index/Auth/editModular/id/'.$value['modularId'].'" class="a_list c_1785c8 m_r_10"><i class="fa fa-pencil-square"></i></a>';
                }
                if($modularButton['delModular']){
                    $str.='<a href="javascript:delModular('.$value['modularId'].');" class="a_list"><i class="fa fa-trash-o"></i></a>';
                }
                $str.='</td></tr>';
                $str.=$this->getModularTabRec($value['modularId'],$value['modularName']);
            }
        }
        return $str;
    }

    //递归查询模块树形select结构
    protected function getModularOption($parentModularId,$modularId=0){
        $where['parentModularId']=$parentModularId;
        $where['status']=1;
        $result=db('modular')->where($where)->select();
        global $str;
        if($result){
            foreach($result as $value){
                $name=$value['level'];
                for($i=$value['level'];$i>0;$i--){
                    $name.='——';
                }
                $name.=$value['modularName'];
                if($modularId>0){
                    if($value['modularId']==$modularId){
                        $str.='<option value="'.$value['modularId'].'" selected="selected">'.$name.'</option>';
                    }else{
                        $str.='<option value="'.$value['modularId'].'">'.$name.'</option>';
                    }
                }else{
                    $str.='<option value="'.$value['modularId'].'">'.$name.'</option>';
                }
                $this->getModularOption($value['modularId'],$modularId);
            }
        }
        return $str;
    }

    //查询一级导航模块
    protected function getTopModularCheck($authId=0){
        $result=db('modular')->where('parentModularId',0)->where('status',1)->select();
        $strTopNav='<div class="authority_tab">';
        $strTab='';
        $isFirst=1;
        foreach($result as $value){
            $isTrue=0;
            if($authId>0){
                //如果传递了权限id，那么判断当前模块是否属于该权限
                $cret=db('auth_modular')->where('modularId',$value['modularId'])->where('authId',$authId)->find();
                if($cret){
                    $isTrue=1;
                }
            }
            if($isFirst){//如果是第一个一级菜单，那么html页面多添加：l_line_eee、a两个类
                if($isTrue){//如果属于传递的权限，那么设置选中
                    $strTopNav.='<a href="javascritp:void(0);" class="l_line_eee a auth_nav"><input type="checkbox" name="modular" id="checkbox_a'.$value['modularId'].'" class="chk_1 check'.$value['level'].'" value="'.$value['modularId'].'" checked="checked" /><label for="checkbox_a'.$value['modularId'].'"></label><span class="p_left_3">'.$value['modularName'].'</span></a>';
                }else{
                    $strTopNav.='<a href="javascritp:void(0);" class="l_line_eee a auth_nav"><input type="checkbox" name="modular" id="checkbox_a'.$value['modularId'].'" class="chk_1 check'.$value['level'].'" value="'.$value['modularId'].'" /><label for="checkbox_a'.$value['modularId'].'"></label><span class="p_left_3">'.$value['modularName'].'</span></a>';
                }
                $strTab.='<table class="sdms_table" border="0" cellspacing="0" cellpadding="0">';
            }else{
                if($isTrue){
                    $strTopNav.='<a href="javascritp:void(0);" class="auth_nav"><input type="checkbox" name="modular" id="checkbox_a'.$value['modularId'].'" class="chk_1 check'.$value['level'].'" value="'.$value['modularId'].'" checked="checked" /><label for="checkbox_a'.$value['modularId'].'"></label><span class="p_left_3">'.$value['modularName'].'</span></a>';
                }else{
                    $strTopNav.='<a href="javascritp:void(0);" class="auth_nav"><input type="checkbox" name="modular" id="checkbox_a'.$value['modularId'].'" class="chk_1 check'.$value['level'].'" value="'.$value['modularId'].'" /><label for="checkbox_a'.$value['modularId'].'"></label><span class="p_left_3">'.$value['modularName'].'</span></a>';
                }
                $strTab.='<table class="sdms_table sdms_table_hide" border="0" cellspacing="0" cellpadding="0">';
            }
            //获取当前一级菜单的子菜单
            $strTab.=$this->getSecNavModularCheck($value['modularId'],$authId);
            $strTab.='</table>';
            $isFirst=0;
        }
        $strTopNav.='</div>';
        $strTopNav.=$strTab;
        return $strTopNav;
    }

    //查询二级菜单模块
    protected function getSecNavModularCheck($parentModularId=0,$authId=0){
        $where['parentModularId']=$parentModularId;
        $where['level'] = 2;
        $where['status'] = 1;
        $result=db('modular')->where($where)->select();
        $strSecNav='';
        if($result){
            foreach($result as $secNav){
                if($secNav['isNav']){//如果当前模块是菜单，那么html使用tr2类
                    $strSecNav.='<tr class="tr2">';
                }else{
                    $strSecNav.='<tr class="tr3">';
                }
                $isTrue=0;
                if($authId>0){
                    //如果传递了权限id，那么判断当前模块是否属于该权限
                    $cret=db('auth_modular')->where('modularId',$secNav['modularId'])->where('authId',$authId)->find();
                    if($cret){
                        $isTrue=1;
                    }
                }
                if($isTrue){//如果属于传递的权限，那么设置选中
                    if($secNav['isNav']){//如果是导航模块，那么html使用check类
                        $strSecNav.='<td><input type="checkbox" name="modular" id="checkbox_a'.$secNav['modularId'].'" checked="checked" class="chk_1 check'.$secNav['level'].'" value="'.$secNav['modularId'].'" /><label for="checkbox_a'.$secNav['modularId'].'"></label> <span>'.$secNav['modularName'].'</span></td>';
                    }else{//如果不是导航模块，那么html使用isFunc类
                        $strSecNav.='<td><input type="checkbox" name="modular" id="checkbox_a'.$secNav['modularId'].'" checked="checked" class="chk_1 isFunc" value="'.$secNav['modularId'].'" /><label for="checkbox_a'.$secNav['modularId'].'"></label> <span>'.$secNav['modularName'].'</span></td>';
                    }
                }else{
                    if($secNav['isNav']){
                        $strSecNav.='<td><input type="checkbox" name="modular" id="checkbox_a'.$secNav['modularId'].'" class="chk_1 check'.$secNav['level'].'" / value="'.$secNav['modularId'].'"><label for="checkbox_a'.$secNav['modularId'].'"></label> <span>'.$secNav['modularName'].'</span></td>';
                    }else{
                        $strSecNav.='<td><input type="checkbox" name="modular" id="checkbox_a'.$secNav['modularId'].'" class="chk_1 isFunc" / value="'.$secNav['modularId'].'"><label for="checkbox_a'.$secNav['modularId'].'"></label> <span>'.$secNav['modularName'].'</span></td>';
                    }
                }
                $strSecNav.='</tr>';
                $strSecNav.=$this->getThirdNavModularChecn($secNav['modularId'],$authId);
            }
        }
        return $strSecNav;
    }

    //查询三级菜单模块
    protected function getThirdNavModularChecn($parentModularId=0,$authId=0){
        $where['parentModularId']=$parentModularId;
        $where['level'] = 3;
        $where['status'] = 1;
        $result=db('modular')->where($where)->select();
        $strThirdNav='';
        if($result){
            foreach($result as $thirdNav){
                $strThirdNav.='<tr class="tr3">';
                $isTrue=0;
                if($authId>0){
                    //如果传递了权限id，那么判断当前模块是否属于该权限
                    $cret=db('auth_modular')->where('modularId',$thirdNav['modularId'])->where('authId',$authId)->find();
                    if($cret){
                        $isTrue=1;
                    }
                }
                if($isTrue){//如果属于传递的权限，那么设置选中
                    if($thirdNav['isNav']){//如果是导航模块，那么html使用check类
                        $strThirdNav.='<td><input type="checkbox" name="modular" id="checkbox_a'.$thirdNav['modularId'].'" checked="checked" class="chk_1 check'.$thirdNav['level'].'" value="'.$thirdNav['modularId'].'" /><label for="checkbox_a'.$thirdNav['modularId'].'"></label> <span>'.$thirdNav['modularName'].'</span></td>';
                    }else{//如果不是导航模块，那么html使用isFunc类
                        $strThirdNav.='<td><input type="checkbox" name="modular" id="checkbox_a'.$thirdNav['modularId'].'" checked="checked" class="chk_1 isFunc" value="'.$thirdNav['modularId'].'" /><label for="checkbox_a'.$thirdNav['modularId'].'"></label> <span>'.$thirdNav['modularName'].'</span></td>';
                    }
                }else{
                    if($thirdNav['isNav']){
                        $strThirdNav.='<td><input type="checkbox" name="modular" id="checkbox_a'.$thirdNav['modularId'].'" class="chk_1 check'.$thirdNav['level'].'" value="'.$thirdNav['modularId'].'" /><label for="checkbox_a'.$thirdNav['modularId'].'"></label> <span>'.$thirdNav['modularName'].'</span></td>';
                    }else{
                        $strThirdNav.='<td><input type="checkbox" name="modular" id="checkbox_a'.$thirdNav['modularId'].'" class="chk_1 isFunc" value="'.$thirdNav['modularId'].'" /><label for="checkbox_a'.$thirdNav['modularId'].'"></label> <span>'.$thirdNav['modularName'].'</span></td>';
                    }
                }
                $strThirdNav.=$this->getNotNavModularCheck($thirdNav['modularId'],$authId);
                $strThirdNav.='</tr>';
            }
        }
        return $strThirdNav;
    }

    //查询功能模块
    protected function getNotNavModularCheck($parentModularId=0,$authId=0){
        $where['parentModularId']=$parentModularId;
        $where['level']=4;
        $where['status']=1;
        $result=db('modular')->where($where)->select();
        $strNotNav='';
        $count=0;
        if($result){
            foreach($result as $notNav){
                $count++;
                $isTrue=0;
                if($authId>0){
                    $cret=db('auth_modular')->where('modularId',$notNav['modularId'])->where('authId',$authId)->find();
                    if($cret){
                        $isTrue=1;
                    }
                }
                if($isTrue){
                    $strNotNav.='<td><input type="checkbox" name="modular" id="checkbox_a'.$notNav['modularId'].'" class="chk_1 isFunc" value="'.$notNav['modularId'].'" checked="checked" /><label for="checkbox_a'.$notNav['modularId'].'"></label> <span>'.$notNav['modularName'].'</span></td>';
                }else{
                    $strNotNav.='<td><input type="checkbox" name="modular" id="checkbox_a'.$notNav['modularId'].'" class="chk_1 isFunc" value="'.$notNav['modularId'].'"/><label for="checkbox_a'.$notNav['modularId'].'"></label> <span>'.$notNav['modularName'].'</span></td>';
                }
            }
        }
        if($count==0){
            $strNotNav.='<td></td><td></td><td></td>';
        }elseif($count==1){
            $strNotNav.='<td></td><td></td>';
        }elseif($count==2){
            $strNotNav.='<td></td>';
        }
        return $strNotNav;
    }

    //递归查询绑定模块树形结构checkbox
    protected function getBandModularCheck($parentModularId=0,$authId=0){
        $where['parentModularId']=$parentModularId;
        $where['status']=1;
        $result=db('modular')->where($where)->select();
        global $str;
        if($result){
            foreach($result as $value){
                $isTrue=0;
                if($authId>0){
                    $cret=db('auth_modular')->where('modularId',$value['modularId'])->where('authId',$authId)->find();
                    if($cret){
                        $isTrue=1;
                    }
                }
                if($value['isNav']){
                    if($isTrue){
                        $str.='<a href="javascritp:void(0);" class="l_line_eee a auth_nav"><input type="checkbox" value="'.$value['modularId'].'"'.$value['modularName'].'</a>';
                    }else{
                        $str.='<a href="javascritp:void(0);" class="l_line_eee a auth_nav"><input type="checkbox" value="'.$value['modularId'].'"'.$value['modularName'].'</a>';
                    }
                }
                $str.='<div class="modular">';
                if($isTrue){
                    $str.='<label class="checkbox"><input type="checkbox" class="checkbox'.$value['level'].'" name="modular" value="'.$value['modularId'].'" checked="checked" />'.$value['modularName'].'</label>';
                }else{
                    $str.='<label class="checkbox"><input type="checkbox" class="checkbox'.$value['level'].'" name="modular" value="'.$value['modularId'].'" />'.$value['modularName'].'</label>';
                }
                $str.='<div class="modular'.$value['level'].'">';
                $this->getBandModularCheck($value['modularId'],$authId);
                $str.='</div>';
                $str.="</div>";
            }
        }
        return $str;
    }

    //生成nav文档
    protected function getNavStr($modulars='',$authId=''){
        $where['status']=1;
        $where['isNav']=1;
        if($modulars){
            $where['modularId']=['in',$modulars];
        }else{
            $where['level']=['in','1,2'];
        }
        $nav=db('modular')->where($where)->order('level asc')->select();
        if($nav){
            $fileName=APP_PATH.'index/view/nav/nav'.$authId.'.html';
            $str="<dl class=\"sdms_nav_spread sdms_nav\" id=\"sdmsnav\">\n<a href=\"javascript:changeNav();\" class=\"zooming\"><i class=\"fa fa-bars fa-rotate-90\"></i></a>\n";
            $data=array();
            foreach($nav as $value){
                if($value['level']==1){
                    $data[$value['modularId']]['modularName']=$value['modularName'];
                    $data[$value['modularId']]['modularFunName']=$value['modularFunName'];
                    $data[$value['modularId']]['modularClass']=$value['modularClass'];
                }elseif($value['level']==2){
                    $data[$value['parentModularId']]['child'][$value['modularId']]=$value;
                }
            }
            foreach($data as $val){
                if(isset($val['child'])){
                    $childNav='';
                    $condition='';
                    foreach($val['child'] as $v){
                        $condition.=" (\$nav eq '{$v['modularEnName']}') or";
                        $childNav.="<if condition=\"\$nav eq '{$v['modularEnName']}'\">\n";
                        $childNav.="<dd class=\"tips\" data-tipso=\"{$v['modularName']}\"><a href=\"{$v['modularFunName']}\" class='a'><i class=\"fa {$v['modularIcon']}\"></i><span>{$v['modularName']}</span></a></dd>\n";
                        $childNav.="<else />\n";
                        $childNav.="<dd class=\"tips\" data-tipso=\"{$v['modularName']}\"><a href=\"{$v['modularFunName']}\"><i class=\"fa {$v['modularIcon']}\"></i><span>{$v['modularName']}</span></a></dd>\n";
                        $childNav.="</if>\n";
                    }
                    $condition=trim($condition,' or');
                    $str.="<if condition=\"".$condition."\">\n";
                    $str.="<dt class=\"tips\" data-tipso=\"{$val['modularName']}\"><a href=\"{$val['modularFunName']}\" class=\"{$val['modularClass']}\"><i class=\"fa fa-caret-down\"></i><span>{$val['modularName']}</span></a></dt>\n";
                    $str.="<div class=\"nav_dd\">\n<else />\n";
                    $str.="<dt class=\"tips\" data-tipso=\"{$val['modularName']}\"><a href=\"{$val['modularFunName']}\" class=\"{$val['modularClass']}\"><i class=\"fa fa-caret-right\"></i><span>{$val['modularName']}</span></a></dt>\n<div class=\"nav_dd nav_none\">\n</if>\n";
                    $str.=$childNav."</div>\n";
                }
            }
            $str.='</dl>';
            @file_put_contents($fileName,$str);
        }
    }

    //生成子菜单文档
    protected function getSubNavStr($modulars='',$authId=''){
        if($modulars){
            $where['m.modularId']=['in',$modulars];
        }else{
            $where['m.level']=3;
        }
        $where['m.status']=1;
        $where['m.isNav']=1;
        $nav=db('modular')->alias('m')->join('erp_modular p','m.parentModularId=p.modularId','left')->where($where)->order('m.level asc,m.modularId asc')->field('m.parentModularId,m.modularEnName,m.modularName,m.modularId,m.modularFunName,p.modularName as pmodularName,p.modularEnName as pmodularEnName')->select();
        if($nav){
            $data=array();
            foreach($nav as $value){
                $data[$value['parentModularId']]['modularEnName']=$value['modularEnName'];
                $data[$value['parentModularId']]['modularName']=$value['modularEnName'];
                $data[$value['parentModularId']]['pmodularName']=$value['pmodularName'];
                $data[$value['parentModularId']]['pmodularEnName']=$value['pmodularEnName'];
                $data[$value['parentModularId']]['child'][$value['modularId']]=$value;
            }
            foreach($data as $val){
                if(isset($val['child'])){
                    $fileName=APP_PATH.'index/view/nav/'.$authId.'subNav'.$val['pmodularEnName'].'.html';
                    $str="<ul class=\"sdms_second_nav\">\n<h3>{$val['pmodularName']}</h3>\n";
                    foreach($val['child'] as $v){
                        $str.="<if condition=\"\$subNav eq '{$v['modularEnName']}'\">\n";
                        $str.="<li><a href=\"{$v['modularFunName']}\" class=\"a\">{$v['modularName']}</a></li>\n";
                        $str.="<else />\n";
                        $str.="<li><a href=\"{$v['modularFunName']}\">{$v['modularName']}</a></li>\n";
                        $str.="</if>\n";
                    }
                    $str.="</ul>";
                    @file_put_contents($fileName,$str);
                }
            }
        }
    }

    //删除所有子菜单文档
    protected function delSubNavFile($auth=''){
        if($auth){
            $path=APP_PATH.'index/view/nav/';
            $fileNames=scandir($path);
            if($fileNames){
                foreach($fileNames as $fileName){
                    if(strpos($fileName,strval($auth))===0){
                        unlink($path.$fileName);
                    }
                }
            }
        }
    }

    //删除权限组所有配置
    protected function delAuthFile($auth=''){
        if($auth){
            $path=APP_PATH.'index/view/nav/';
            $fileNames=scandir($path);
            if($fileNames){
                foreach($fileNames as $fileName){
                    if(strpos($fileName,strval($auth))===0){
                        unlink($path.$fileName);
                    }elseif($fileName=='nav'.$auth.'.html'){
                        unlink($path.$fileName);
                    }
                }
            }
            $funcName=APP_PATH.'extra/func'.$auth.'.php';
            if(is_file($funcName)){
                unlink($funcName);
            }
        }
    }

    //生成功能按钮配置
    protected function setButtonNav($modulars='',$authId=''){
        $where['m.status']=1;
        $where['m.isNav']=0;
        $where['m.level']=['>',1];
        if($authId>0){
            $FuncArr=db('modular')->alias('m')->join('erp_modular p','m.parentModularId=p.modularId','left')->join('erp_auth_modular am',"am.modularId=m.modularId and am.authId={$authId} and am.modularId in ({$modulars})",'left')->where($where)->field('m.modularName,m.modularEnName,m.modularFunName,m.modularIcon,m.modularClass,m.showName,p.modularEnName as pmodularEnName,am.modularId as amodularId')->order('m.level asc,m.modularId asc')->select();
            if($FuncArr){
                $data=array();
                foreach($FuncArr as $Fun){
                    $data[$Fun['pmodularEnName']]['pmodularEnName']=$Fun['pmodularEnName'];
                    $data[$Fun['pmodularEnName']]['child'][]=$Fun;
                }
                $this->setButtonNavFile($data,$authId);
            }
        }else{
            $FuncArr=db('modular')->alias('m')->join('erp_modular p','m.parentModularId=p.modularId','left')->join('erp_auths a','a.status=m.status','left')->join('erp_auth_modular am',"am.modularId=m.modularId and am.authId=a.authId",'left')->where($where)->field('m.modularName,m.modularEnName,m.modularFunName,m.modularIcon,m.modularClass,m.showName,p.modularEnName as pmodularEnName,a.authId,am.modularId as amodularId')->order('a.authId ASC,m.modularId asc')->select();
            if($FuncArr){
                $datas=array();
                foreach($FuncArr as $Fun){
                    $datas[$Fun['authId']][$Fun['pmodularEnName']]['pmodularEnName']=$Fun['pmodularEnName'];
                    $datas[$Fun['authId']]['authId']=[$Fun['authId']];
                    $datas[$Fun['authId']][$Fun['pmodularEnName']]['child'][]=$Fun;
                    $datas['isAdmin']=$datas[$Fun['authId']];
                    $datas['isAdmin']['authId'][0]='isAdmin';
                }
                foreach($datas as $data){
                    $authId=$data['authId'][0];
                    unset($data['authId']);
                    if($authId=='isAdmin'){
                        $authId='';
                    }
                    $this->setButtonNavFile($data,$authId);
                }
            }
        }
    }

    //生成功能按钮配置文件
    protected function setButtonNavFile($data,$authId=''){
        $str="<?php\nreturn [\n";
        foreach($data as $value){
            if(isset($value['child'])){
                $str.="'{$value['pmodularEnName']}'=>[\n";
                foreach($value['child'] as $val){
                    $str.="'{$val['modularEnName']}'=>'";
                    if(($val['amodularId'] && $authId>0) || $authId===''){
                        $str.="<a href=\"{$val['modularFunName']}\" class=\"{$val['modularClass']}\">";
                        if($val['showName']){
                            if($value['pmodularEnName']=='top'){
                                if($val['modularIcon']){
                                    $str.="{$val['modularName']} <i class=\"fa {$val['modularIcon']}\"></i></a>";
                                }else{
                                    $str.="{$val['modularName']}</a>";
                                }
                            }else{
                                if($val['modularIcon']){
                                    $str.="<i class=\"fa {$val['modularIcon']}\"></i> {$val['modularName']}</a>";
                                }else{
                                    $str.="{$val['modularName']}</a>";
                                }
                            }
                        }else{
                            if($val['modularIcon']){
                                $str.="<i class=\"fa {$val['modularIcon']}\"></i></a>";
                            }else{
                                $str.="</a>";
                            }
                        }
                    }
                    $str.="',\n";
                }
                $str.="],\n";
            }
        }
        $str.='];';
        $fileName=APP_PATH.'extra/func'.$authId.'.php';
        @file_put_contents($fileName,$str);
    }
}
