<?php

namespace app\index\controller;

use think\Exception;
use think\Session;
use think\Config;
use think\Db;
use app\index\model\Users;
use app\index\model\Role;

class UcenterController extends BaseController
{
    //作者：于明明
    //功能：我的资料
    public function myUser(){
        $where['u.userId']=$this->userId;
        $where['u.status']=1;
        $data=db('users')->alias('u')->join('erp_user_auth ua','ua.userId=u.userId','left')->join('erp_auths a','a.authId=ua.authId','left')->join('erp_user_role ur','ur.userId=u.userId','left')->join('erp_role r','r.roleId=ur.roleId','left')->join('erp_role pr','pr.roleId=r.parentRoleId','left')->where($where)->field('u.userId,u.nickName,u.enName,u.sex,u.email,u.mobile,u.remark,u.isAdmin,a.authName,r.roleName,pr.roleName as proleName')->find();
        if(!$data){
            $this->error('未查询到用户信息',$this->referer);
        }

        $this->assign('button',Config::get('func'.$this->authId.'.myUser'));
        $this->assign('data',$data);
        $this->assign('nav','ucenter');
        $this->assign('subNav','myUser');
        $this->assign('id',$this->userId);
        return $this->fetch();
    }

    //作者：于明明
    //功能：修改我的资料
    public function editMyUser(){
        $id=request()->param('id/d')?request()->param('id/d'):0;
        if(!$id) errorMsg(400,'我的信息有误，请刷新重试！');
        $users=Users::get($id);
        $users->mobile=request()->param('mobile/s')?request()->param('mobile/s'):'';
        if(!$users->mobile) errorMsg(400,'登陆手机不能为空');
        $result=$users->where('mobile',$users->mobile)->where('status',1)->find();
        if($result){
            if($result['userId']!=$id){
                errorMsg(400,'该登陆手机已经存在');
            }
        }
        //用户数据
        $users->nickName=request()->param('nickName/s')?request()->param('nickName/s'):'';
        if(!$users->nickName) errorMsg(400,'姓名不能为空');
        $users->enName=request()->param('enName/s')?request()->param('enName/s'):'';
        if(!$users->enName) errorMsg(400,'英文名不能为空');
        $users->sex=request()->param('sex/d')?request()->param('sex/d'):0;
        $users->email=request()->param('email/s')?request()->param('email/s'):'';
        if(!$users->email) errorMsg(400,'邮箱不能为空');
        $oldpass=request()->param('oldpass/s')?request()->param('oldpass/s'):'';
        $password=request()->param('password/s')?request()->param('password/s'):'';
        $repass=request()->param('repass/s')?request()->param('repass/s'):'';
        if($password!==$repass) errorMsg(400,'两次密码输入不一致');
        if($password){
            $oldpass=md5($oldpass.'erp');
            if($users->password!=$oldpass) errorMsg(400,'原始密码不正确！');
            $users->password=md5($password.'erp');
        }
        $users->remark=request()->param('remark/s')?request()->param('remark/s'):'';
        //开始事务
        Db::startTrans();
        try{
            //修改用户
            $users->save();
            Db::commit();
            errorMsg(200,'done');
        }catch(\Exception $e){
            Db::rollback();
            errorMsg(400,$e->getMessage());
        }
    }

    //作者：于明明
    //功能：用户列表
    public function userList()
    {
        $value=request()->param('value/s')?request()->param('value/s'):'';
        $where='u.companyId='.$this->companyId.' and u.status=1 and u.isAdmin=0';
        if($value){
            $where.=" and ( (u.userId='{$value}') or (u.mobile='{$value}') or (u.nickName='{$value}') )";
        }
        $lists=db('users')->alias('u')->join('erp_user_auth ua','ua.userId=u.userId','left')->join('erp_auths a','a.authId=ua.authId','left')->join('erp_user_role ur','ur.userId=u.userId','left')->join('erp_role r','r.roleId=ur.roleId','left')->join('erp_role pr','pr.roleId=r.parentRoleId','left')->where($where)->field('u.userId,u.nickName,u.mobile,u.loginTimes,u.loginTime,u.remark,a.authName,r.roleName,pr.roleName as proleName')->order('u.userId asc')->paginate();

        $this->assign('button',Config::get('func'.$this->authId.'.userList'));
        $this->assign('nav','ucenter');
        $this->assign('subNav','userList');
        $this->assign('value',$value);
        $this->assign('list',$lists);
        return $this->fetch();
    }

    public function test(){
        $this->assign('button',Config::get('func'.$this->authId.'.test'));
    }

    //作者：于明明
    //功能：用户回收站
    public function userDelList(){
        $value=request()->param('value/s')?request()->param('value/s'):'';
        $where='u.companyId='.$this->companyId.' and u.status=0 and u.isAdmin=0';
        if($value){
            $where.=" and ( (u.userId='{$value}') or (u.mobile='{$value}') or (u.nickName='{$value}') )";
        }
        $lists=db('users')->alias('u')->join('erp_user_auth ua','ua.userId=u.userId','left')->join('erp_auths a','a.authId=ua.authId','left')->join('erp_user_role ur','ur.userId=u.userId','left')->join('erp_role r','r.roleId=ur.roleId','left')->join('erp_role pr','pr.roleId=r.parentRoleId','left')->where($where)->field('u.userId,u.nickName,u.mobile,u.loginTimes,u.loginTime,u.remark,a.authName,r.roleName,pr.roleName as proleName')->order('u.userId asc')->paginate();

        $this->assign('button',Config::get('func'.$this->authId.'.userList'));
        $this->assign('nav','ucenter');
        $this->assign('subNav','userList');
        $this->assign('value',$value);
        $this->assign('list',$lists);
        return $this->fetch();
    }

    //作者：于明明
    //功能：修改用户
    public function editUser(){
        $id=request()->param('id/s')?request()->param('id/s'):0;
        if(request()->isPost()){
            try{
                if(!$id) errorMsg(400,'用户信息有误，请刷新重试!');
                $users=Users::get($id);
                $users->mobile=request()->param('mobile/s')?request()->param('mobile/s'):'';
                if(!$users->mobile) errorMsg(400,'登陆手机不能为空');
                $where['mobile']=$users->mobile;
                $where['companyId']=$this->companyId;
                $result=$users->where($where)->find();
                if($result){
                    if($result['userId']!=$id){
                        if($result['status']){
                            errorMsg(400,'该登陆手机已经存在');
                        }else{
                            errorMsg(400,'该登陆手机在回收站已经存在');
                        }
                    }
                }
                //用户数据
                $users->nickName=request()->param('nickName/s')?request()->param('nickName/s'):'';
                if(!$users->nickName) errorMsg(400,'姓名不能为空');
                $users->enName=request()->param('enName/s')?request()->param('enName/s'):'';
                if(!$users->enName) errorMsg(400,'英文名不能为空');
                $users->sex=request()->param('sex/d')?request()->param('sex/d'):0;
                $users->email=request()->param('email/s')?request()->param('email/s'):'';
                if(!$users->email) errorMsg(400,'邮箱不能为空');
                $password=request()->param('password/s')?request()->param('password/s'):'';
                $repass=request()->param('repass/s')?request()->param('repass/s'):'';
                if($password!==$repass) errorMsg(400,'两次密码输入不一致');
                if($password){
                    $users->password=md5($password.'erp');
                }
                $users->remark=request()->param('remark/s')?request()->param('remark/s'):'';
                //用户角色数据
                $userRole['roleId']=request()->param('roleId/d')?request()->param('roleId/d'):0;
                if(!$userRole['roleId']) errorMsg(400,'请选择职位');
                //用户权限
                $userAuth['authId']=request()->param('authId/d')?request()->param('authId/d'):0;
                if(!$userAuth['authId']) errorMsg(400,'请选择权限组');
            }catch(\Exception $e){
                errorMsg(400,$e->getMessage());
            }
            //开始事务
            Db::startTrans();
            try{
                //修改用户
                $users->save();
                //删除用户原有角色
                $users->userRole()->delete();
                //添加用户角色
                $users->userRole()->save($userRole);
                //删除用户原有权限
                $users->userAuth()->delete();
                //添加用户权限组
                $users->userAuth()->save($userAuth);
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            if(!$id){
                $this->error('用户信息错误',$this->referer);
            }
            $where['userId']=$id;
            $where['status']=1;
            $data=db('users')->where($where)->find();
            if(!$data){
                $this->error('未查询到用户信息',$this->referer);
            }

            $role1=$role2=$role3=$role4=array();
            $parentRoleId1=$parentRoleId2=$parentRoleId3=0;
            //查询同级职位和部门
            $sql='SELECT r.roleId as mroleId,cr.* FROM erp_user_role AS ur LEFT JOIN erp_role AS r ON r.roleId=ur.roleId LEFT JOIN erp_role AS cr ON cr.parentRoleId=r.parentRoleId WHERE ur.userId='.$id;
            $childRole=db('user_role')->query($sql);
            if($childRole) {
                $data['roleId'] = $childRole[0]['mroleId'];

                //查询上级同级部门和上级同级职位
                $sql = 'SELECT pr.* FROM erp_role AS r LEFT JOIN erp_role AS pr ON pr.parentRoleId=r.parentRoleId AND pr.type=1 WHERE r.roleId=' . $childRole[0]['parentRoleId'];
                $parentRole = db('role')->query($sql);
                if (!$parentRole) $this->error('未查询到组织信息', $this->referer);

                if ($childRole[0]['level'] == 2) {
                    foreach ($childRole as $r) {
                        if ($r['type']) {
                            $role2[] = $r;
                        } else {
                            $role4[] = $r;
                        }
                    }
                    $role1 = $parentRole;
                    $parentRoleId1 = $childRole[0]['parentRoleId'];
                }

                if ($childRole[0]['level'] == 3) {
                    foreach ($childRole as $r) {
                        if ($r['type']) {
                            $role3[] = $r;
                        } else {
                            $role4[] = $r;
                        }
                    }
                    $role2 = $parentRole;
                    $parentRoleId2 = $childRole[0]['parentRoleId'];
                    $parentRoleId1 = $parentRole[0]['parentRoleId'];
                    //查询上级同级部门和上级同级职位
                    $sql = 'SELECT pr.* FROM erp_role AS r LEFT JOIN erp_role AS pr ON pr.parentRoleId=r.parentRoleId AND pr.type=1 WHERE r.roleId=' . $role2[0]['parentRoleId'];
                    $role1 = db('role')->query($sql);
                    if (!$role1) $this->error('未查询到组织信息', $this->referer);
                }

                if ($childRole[0]['level'] == 4) {
                    $role3 = $parentRole;
                    $role4 = $childRole;
                    $parentRoleId3 = $childRole[0]['parentRoleId'];
                    $parentRoleId2 = $role3[0]['parentRoleId'];
                    //查询上级同级部门和上级同级职位
                    $sql = 'SELECT pr.* FROM erp_role AS r LEFT JOIN erp_role AS pr ON pr.parentRoleId=r.parentRoleId AND pr.type=1 WHERE r.roleId=' . $role3[0]['parentRoleId'];
                    $role2 = db('role')->query($sql);
                    if (!$role2) $this->error('未查询到组织信息', $this->referer);
                    $parentRoleId1 = $role2[0]['parentRoleId'];

                    //查询上级同级部门和上级同级职位
                    $sql = 'SELECT pr.* FROM erp_role AS r LEFT JOIN erp_role AS pr ON pr.parentRoleId=r.parentRoleId AND pr.type=1 WHERE r.roleId=' . $role2[0]['parentRoleId'];
                    $role1 = db('role')->query($sql);
                    if (!$role1) $this->error('未查询到组织信息', $this->referer);
                }
            }else{
                $map['companyId']=$this->companyId;
                $map['status']=1;
                $map['type']=1;
                $map['level']=1;
                $role1=db('role')->where($map)->select();
                if(!$role1){
                    $this->error('未查询到组织信息',$this->referer);
                }
            }

            //获取权限组信息
            $sql="select a.authId,a.authName,IFNULL(ua.userId,0) as userId from erp_auths as a left join erp_user_auth as ua on ua.authId=a.authId and ua.userId={$id} where a.companyId={$this->companyId} and a.status=1 order by a.authId";
            $authList=db('auths')->query($sql);
            if(!$authList){
                $this->error('未查询到权限组信息',$this->referer);
            }

            $this->assign('nav','ucenter');
            $this->assign('subNav','userList');
            $this->assign('id',$id);
            $this->assign('data',$data);
            $this->assign('role1',$role1);
            $this->assign('role2',$role2);
            $this->assign('role3',$role3);
            $this->assign('role4',$role4);
            $this->assign('parentRoleId1',$parentRoleId1);
            $this->assign('parentRoleId2',$parentRoleId2);
            $this->assign('parentRoleId3',$parentRoleId3);
            $this->assign('authList',$authList);
            return $this->fetch();
        }
    }

    //作者：于明明
    //功能：添加用户
    public function addUser(){
        if(request()->isPost()){
            //验证公司信息
            $cwhere['companyId']=$this->companyId;
            $cwhere['status']=1;
            $cresult=db('company')->where($cwhere)->find();
            if(!$cresult) errorMsg(300,'公司信息有误，请重新登陆！');
            if($cresult['cooperation']!=1){
                $count=db('users')->where($cwhere)->count();
                if($count>=$cresult['num']) errorMsg(400,'公司员工已经达到上线，请联系客服！');
            }
            $users= new Users;
            $users->mobile=request()->param('mobile/s')?request()->param('mobile/s'):'';
            if(!$users->mobile) errorMsg(400,'登陆手机不能为空');
            try{
                $where['mobile']=$users->mobile;
                $where['companyId']=$this->companyId;
                $result=db('users')->where($where)->find();
                if($result){
                    if($result['status']){
                        errorMsg(400,'该登陆手机已经存在');
                    }else{
                        errorMsg(400,'该登陆手机在回收站已经存在');
                    }
                }
                //用户数据
                $users->nickName=request()->param('nickName/s')?request()->param('nickName/s'):'';
                if(!$users->nickName) errorMsg(400,'姓名不能为空');
                $users->enName=request()->param('enName/s')?request()->param('enName/s'):'';
                if(!$users->enName) errorMsg(400,'英文名不能为空');
                $users->sex=request()->param('sex/d')?request()->param('sex/d'):0;
                $users->email=request()->param('email/s')?request()->param('email/s'):'';
                if(!$users->email) errorMsg(400,'邮箱不能为空');
                $password=request()->param('password/s')?request()->param('password/s'):'';
                if(!$password) errorMsg(400,'登陆密码不能为空');
                $repass=request()->param('repass/s')?request()->param('repass/s'):'';
                if($password!==$repass) errorMsg(400,'两次密码输入不一致');
                $users->password=md5($password.'erp');
                $users->remark=request()->param('remark/s')?request()->param('remark/s'):'';
                $users->companyId=$this->companyId;
                //用户角色数据
                $userRole['roleId']=request()->param('roleId/d')?request()->param('roleId/d'):0;
                if(!$userRole['roleId']) errorMsg(400,'请选择职位');
                //用户权限
                $userAuth['authId']=request()->param('authId/d')?request()->param('authId/d'):0;
                if(!$userAuth['authId']) errorMsg(400,'请选择权限组');
            }catch(\Exception $e){
                errorMsg(400,$e->getMessage());
            }
            //数据库操作
            //开始事务
            Db::startTrans();
            try{
                //添加用户
                $users->save();
                //添加用户角色
                $users->userRole()->save($userRole);
                //添加用户权限组
                $users->userAuth()->save($userAuth);
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else {
            $where['status']=1;
            $where['type']=1;
            $where['level']=1;
            $where['companyId']=$this->companyId;
            $roleList=db('role')->where($where)->select();
            if(!$roleList){
                $this->error('未查询到组织信息',$this->referer);
            }

            $awhere['companyId']=$this->companyId;
            $awhere['status']=1;
            $authList=db('auths')->where($awhere)->select();
            if(!$authList){
                $this->error('未查询到权限组信息',$this->referer);
            }

            $this->assign('nav','ucenter');
            $this->assign('subNav','userList');
            $this->assign('roleList',$roleList);
            $this->assign('authList',$authList);
            return $this->fetch();
        }

    }

    //作者：于明明
    //功能：删除用户
    public function delUser(){
        $userId=request()->param('userId/d')?request()->param('userId/d'):0;
        $status=request()->param('status/d')?request()->param('status/d'):0;
        if($userId){
            if($userId==1) errorMsg(400,'系统管理员不允许删除!');
            try{
                $users=Users::get($userId);
                if($users->isAdmin) errorMsg(400,'当前用户是管理员，不能删除！');
                if($users->isLogin) errorMsg(400,'当前用户正在登陆中，不能删除！');
                $users->status=$status;
                $users->updateTime=date('Y-m-d H:i:s');
                Db::startTrans();
                $users->save();
                Db::commit();
                errorMsg(200,'done');
            }catch(Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            errorMsg(400,'角色信息有误!');
        }
    }

    //作者：于明明
    //功能：启用用户
    public function useUser(){
        $userId=request()->param('userId/d')?request()->param('userId/d'):0;
        if($userId){
            //验证公司信息
            $cwhere['companyId']=$this->companyId;
            $cwhere['status']=1;
            $cresult=db('company')->where($cwhere)->find();
            if(!$cresult) errorMsg(300,'公司信息有误，请重新登陆！');
            if($cresult['cooperation']!=1){
                $count=db('users')->where($cwhere)->count();
                if($count>=$cresult['num']) errorMsg(400,'公司员工已经达到上线，请联系客服！');
            }
            try{
                $users=Users::get($userId);
                $users->status=1;
                $users->updateTime=date('Y-m-d H:i:s');
                Db::startTrans();
                $users->save();
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            errorMsg(400,'角色信息有误!');
        }
    }

    //作者：于明明
    //功能：彻底删除用户
    public function purgeUser(){
        $userId=request()->param('userId/d')?request()->param('userId/d'):0;
        if($userId){
            if($userId==1) errorMsg(400,'系统管理员不允许删除!');
            try{
                $users=Users::get($userId);
                Db::startTrans();
                $users->userAuth()->delete();
                $users->userRole()->delete();
                $users->delete();
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            errorMsg(400,'角色信息有误!');
        }
    }

    //获取二级部门和职位
    public function getRole(){
        $parentRoleId=request()->post('parentId/d')?request()->post('parentId/d'):0;
        if($parentRoleId>0){
            try{
                $where['parentRoleId']=$parentRoleId;
                $where['status']=1;
                $result=db('role')->where($where)->select();
                if($result){
                    errorMsg(200,$result);
                }else{
                    errorMsg(400,'未查询到下级部门和职位信息！');
                }
            }catch(\Exception $e){
                errorMsg(400,$e->getMessage());
            }

        }else{
            errorMsg(400,'非法参数，请刷新重试！');
        }
    }

    //作者：于明明
    //功能：角色列表
    public function roleList()
    {
        $list=$this->getRoleTab();
        //dump($list->render());

        $this->assign('button',Config::get('func'.$this->authId.'.roleList'));
        $this->assign('nav','ucenter');
        $this->assign('subNav','roleList');
        $this->assign('list',$list);
        return $this->fetch();
    }

    //作者：于明明
    //功能：角色列表
    public function roleDelList()
    {
        $value=request()->param('value/s')?request()->param('value/s'):'';
        $where='r.companyId='.$this->companyId.' and r.status=0';
        if($value){
            $where.=" and r.roleName='{$value}'";
        }
        $lists=db('role')->alias('r')->join('erp_role pr','pr.roleId=r.parentRoleId','left')->where($where)->field('r.roleId,r.roleName,r.type,r.createTime,r.remark,pr.roleName as proleName')->order('r.roleId asc')->paginate();

        $this->assign('button',Config::get('func'.$this->authId.'.roleList'));
        $this->assign('nav','ucenter');
        $this->assign('subNav','roleList');
        $this->assign('value',$value);
        $this->assign('list',$lists);
        return $this->fetch();
    }

    //作者：于明明
    //功能：添加角色
    public function addRole(){
        if(request()->isPost()){
            $role=new Role;
            $role->roleName=request()->param('roleName/s')?request()->param('roleName/s'):'';
            if(!$role->roleName) errorMsg(400,'组织名称不能为空');
            $role->remark=request()->param('remark/s')?request()->param('remark/s'):'';
            $role->parentRoleId=request()->param('parentRoleId/d')?request()->param('parentRoleId/d'):0;
            $role->isRespons=request()->param('isRespons/d')?request()->param('isRespons/d'):0;
            $role->type=request()->param('type/d')?request()->param('type/d'):0;
            $role->companyId=$this->companyId;
            $where['roleName']=$role->roleName;
            $where['parentRoleId']=$role->parentRoleId;
            $result=db('role')->where($where)->find();
            if($result){
                errorMsg(400,'该组织名称已经存在');
            }
            if($role->parentRoleId==0){
                $role->level=1;
            }else{
                $result=db('role')->where('roleId',$role->parentRoleId)->find();
                if(!$result) errorMsg(400,'所属上级组织有误，请刷新重试！');
                if((!$result['type']) && ($result['level'])>1) errorMsg(400,'所选上级组织属于职位，不允许添加下级部门');
                if($role->type && $result['level']==3) errorMsg(400,'所选上级组织已经是最底层部门，只允许添加职位！');
                $role->level=$result['level']+1;
            }
            Db::startTrans();
            try{
                $role->save();
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else {
            $roleList=$this->getRoleOption(0);

            $this->assign('nav','ucenter');
            $this->assign('subNav','roleList');
            $this->assign('roleList',$roleList);
            return $this->fetch();
        }
    }

    //作者：于明明
    //功能：修改角色
    public function editRole(){
        $id=request()->param('id/d')?request()->param('id/d'):0;
        if(request()->isPost()){
            $role=Role::get($id);
            $role->roleName=request()->param('roleName/s')?request()->param('roleName/s'):'';
            if(!$role->roleName) errorMsg(400,'角色名称不能为空');
            $role->parentRoleId=request()->param('parentRoleId/d')?request()->param('parentRoleId/d'):0;
            $role->isRespons=request()->param('isRespons/d')?request()->param('isRespons/d'):0;
            $role->type=request()->param('type/d')?request()->param('type/d'):0;
            $role->remark=request()->param('remark/s')?request()->param('remark/s'):'';
            try{
                $where['roleName']=$role->roleName;
                $where['parentRoleId']=$role->parentRoleId;
                $result=db('role')->where($where)->find();
                if($result){
                    if($result['roleId']!=$id){
                        errorMsg(400,'该角色名称已经存在');
                    }
                }
                if($role->parentRoleId==0){
                    $role->level=1;
                }else{
                    $result=db('role')->where('roleId',$role->parentRoleId)->find();
                    if(!$result) errorMsg(400,'所属上级组织有误，请刷新重试！');
                    if((!$result['type']) && ($result['level'])>1) errorMsg(400,'所选上级组织属于职位，不允许添加下级部门');
                    if($role->type && $result['level']==3) errorMsg(400,'所选上级组织已经是最底层部门，只允许添加职位！');
                    if($role->level <= $result['level']){
                        $child=db('role')->where('parentRoleId',$id)->select();
                        if($child) errorMsg(400,'当前部门拥有下级组织，不能更改为比当前部门级别更低的部门！');
                    }
                    $role->level=$result['level']+1;
                }
                Db::startTrans();
                try{
                    $role->save();
                    Db::commit();
                    errorMsg(200,'done');
                }catch(\Exception $e){
                    Db::rollback();
                    errorMsg(400,$e->getMessage());
                }
            }catch(\Exception $e){
                errorMsg(400,$e->getMessage());
            }
        }else {
            $where['roleId']=$id;
            $where['status']=1;
            $result=db('role')->where($where)->find();
            if(!$result){
                $this->error('未查询到组织数据',$this->referer);
            }
            $roleList=$this->getRoleOption(0,$result['parentRoleId']);

            $this->assign('nav','ucenter');
            $this->assign('subNav','roleList');
            $this->assign('roleList',$roleList);
            $this->assign('data',$result);
            $this->assign('id', $id);
            return $this->fetch();
        }
    }

    //作者：于明明
    //功能：删除角色
    public function delRole(){
        $roleId=request()->param('roleId/d')?request()->param('roleId/d'):0;
        if($roleId){
            try{
                $where['parentRoleId']=$roleId;
                $where['status']=1;
                $resultRole=db('role')->where($where)->select();
                if($resultRole) errorMsg(400,'当前部门拥有下级组织，不能删除！');
                $resultUser=db('User_role')->where('roleId',$roleId)->select();
                if($resultUser) errorMsg(400,'当前部门拥有员工，不能删除！');
                Db::startTrans();
                $role=Role::get($roleId);
                $role->status=0;
                $role->save();
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            errorMsg(400,'组织信息有误，请刷新后再试！');
        }
    }

    //作者：于明明
    //功能：启用角色
    public function useRole(){
        $roleId=request()->param('roleId/d')?request()->param('roleId/d'):0;
        if($roleId){
            $role=Role::get($roleId);
            $where['companyId']=$this->companyId;
            $where['roleId']=$role->parentRoleId;
            $where['status']=1;
            $result=db('role')->where($where)->find();
            if(!$result) errorMsg(400,'上级部门已经被删除，不能启用！');
            try{
                Db::startTrans();
                $role->status=1;
                $role->save();
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            errorMsg(400,'组织信息有误，请刷新后再试！');
        }

    }

    //作者：于明明
    //功能：彻底删除角色
    public function purgeRole(){
        $roleId=request()->param('roleId/d')?request()->param('roleId/d'):0;
        if($roleId){
            $role=Role::get($roleId);
            $where['parentRoleId']=$roleId;
            $where['status']=1;
            $resultRole=db('role')->where($where)->select();
            if($resultRole) errorMsg(400,'当前部门拥有下级组织，不能彻底删除！');
            $resultUser=db('User_role')->where('roleId',$roleId)->select();
            if($resultUser) errorMsg(400,'当前部门拥有员工，不能彻底删除！');
            try{
                Db::startTrans();
                $role->delete();
                Db::commit();
                errorMsg(200,'done');
            }catch(\Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            errorMsg(400,'组织信息有误，请刷新后再试！');
        }
    }

    //一次查询角色表格结构字符串
    protected function getRoleTab(){
        $where['companyId']=$this->companyId;
        $where['status']=1;
        $result=db('role')->where($where)->order('type asc,roleId asc')->select();
        $data=array();
        $str='';
        if($result){
            foreach($result as $val){
                if($val['level']==1){
                    $data['parent'][$val['roleId']]=$val;
                }else{
                    $data['child'][$val['parentRoleId']][$val['roleId']]=$val;
                }
            }
            $roleButton=Config::get('func'.$this->authId.'.roleList');
            foreach($data['parent'] as $value){
                $str.='<tr class="tr1">';
                $str.='<td><input type="checkbox" id="checkbox_a'.$value['roleId'].'" class="chk_3" /><label for="checkbox_a'.$value['roleId'].'"></label></td>';
                $str.='<td>'.$value['roleId'].'</td>';
                $str.='<td colspan=4 class="font_b">'.$value['roleName'].'</td>';
                $str.='<td></td>';
                $str.='<td>顶级部门</td>';
                $str.='<td>部门</td>';
                $str.='<td>'.$value['createTime'].'</td>';
                $str.='<td class="text_center">'.$value['remark'].'</td>';
                $str.='<td class="text_center">';
                if($roleButton['editRole']){
                    $str.='<a href="/Index/Ucenter/editRole/id/'.$value['roleId'].'" class="a_list c_1785c8 m_r_10"><i class="fa fa-pencil-square"></i></a>';
                }
                if($roleButton['delRole']){
                    $str.='<a href="javascript:delRole('.$value['roleId'].');" class="a_list c_1785c8"><i class="fa fa-trash-o"></i></a>';
                }
                $str.='</td></tr>';
                if(isset($data['child'])){
                    $str.=$this->getRoleChildTab($data['child'],$value['roleId'],$value['roleName']);
                }
            }
        }
        return $str;
    }

    //获取tab的子模块信息
    protected function getRoleChildTab($data,$roleId,$roleName=''){
        $str='';
        if(isset($data[$roleId])){
            $result=$data[$roleId];
            unset($data[$roleId]);
            $roleButton=Config::get('func'.$this->authId.'.roleList');
            foreach($result as $value){
                $str.='<tr class="tr1">';
                $str.='<td><input type="checkbox" id="checkbox_a'.$value['roleId'].'" class="chk_3" /><label for="checkbox_a'.$value['roleId'].'"></label></td>';
                $str.='<td>'.$value['roleId'].'</td>';
                $count=5-$value['level'];
                if($value['level']>1) {
                    for ($i = 1; $i < $value['level']; $i++) {
                        $str .= '<td></td>';
                    }
                }
                if($count>0){
                    $str.='<td colspan="'.$count.'">'.$value['roleName'].'</td>';
                }else{
                    $str.='<td>'.$value['roleName'].'</td>';
                }
                if($value['type']){
                    $str.='<td></td><td>'.$roleName.'</td>';
                    $str.='<td>部门</td>';
                }else{
                    if($value['isRespons']){
                        $str.='<td>部门负责人</td>';
                    }else{
                        $str.='<td></td>';
                    }
                    $str.='<td>'.$roleName.'</td>';
                    $str.='<td>职位</td>';
                }
                $str.='<td>'.$value['createTime'].'</td>';
                $str.='<td class="text_center">'.$value['remark'].'</td>';
                $str.='<td class="text_center">';
                if($roleButton['editRole']){
                    $str.='<a href="/Index/Ucenter/editRole/id/'.$value['roleId'].'" class="a_list c_1785c8 m_r_10"><i class="fa fa-pencil-square"></i></a>';
                }
                if($roleButton['delRole']){
                    $str.='<a href="javascript:delRole('.$value['roleId'].');" class="a_list c_1785c8"><i class="fa fa-trash-o"></i></a>';
                }
                $str.='</td></tr>';
                if($value['type']){
                    $str.=$this->getRoleChildTab($data,$value['roleId'],$value['roleName']);
                }
            }
        }
        return $str;
    }

    //递归查询角色树形select结构
    protected function getRoleOption($parentRoleId,$roleId=0){
        $where['companyId']=$this->companyId;
        $where['parentRoleId']=$parentRoleId;
        $where['status']=1;
        $where['type']=1;
        $result=db('role')->where($where)->select();
        global $str;
        if($result){
            foreach($result as $value){
                $name=$value['level'];
                for($i=$value['level'];$i>0;$i--){
                    $name.='——';
                }
                $name.=$value['roleName'];
                if($roleId>0){
                    if($value['roleId']==$roleId){
                        $str.='<option value="'.$value['roleId'].'" selected="selected">'.$name.'</option>';
                    }else{
                        $str.='<option value="'.$value['roleId'].'">'.$name.'</option>';
                    }
                }else{
                    $str.='<option value="'.$value['roleId'].'">'.$name.'</option>';
                }
                $this->getRoleOption($value['roleId'],$roleId);
            }
        }
        return $str;
    }
}
