<?php
namespace app\admin\controller;

use think\Exception;
use think\Db;
use app\admin\model\Company;

class IndexController extends BaseController
{
    //作者：于明明
    //功能：公司列表
    public function index()
    {
        $value=request()->param('value/s')?request()->param('value/s'):'';
        $where='(status=1)';
        if($value){
            $where.=" and (companyName='{$value}' or companyShortName='{$value}' or companyEnName='{$value}')";
        }
        $list=db('company')->where($where)->paginate();

        $this->assign('nav','index');
        $this->assign('value',$value);
        $this->assign('list',$list);
        return $this->fetch();
    }

    //作者：于明明
    //功能：公司服务器
    public function companyDelList(){
        $value=request()->param('value/s')?request()->param('value/s'):'';
        $where='(status=0)';
        if($value){
            $where.=" and (companyName='{$value}' or companyShortName='{$value}' or companyEnName='{$value}')";
        }

        $list=db('company')->where($where)->paginate();
        $this->assign('nav','index');
        $this->assign('value',$value);
        $this->assign('list',$list);
        return $this->fetch();
    }

    //作者：于明明
    //功能：添加公司
    public function addCompany(){
        if(request()->isPost()){
            $company=new Company;
            $company->companyCode=request()->param('companyCode/s')?request()->param('companyCode/s'):'';
            $company->companyName=request()->param('companyName/s')?request()->param('companyName/s'):'';
            if(!$company->companyName) errorMsg(400,'公司名称不能为空');
            $company->companyShortName=request()->param('companyShortName/s')?request()->param('companyShortName/s'):'';
            if(!$company->companyShortName) errorMsg(400,'公司简称不能为空');
            $company->companyEnName=request()->param('companyEnName/s')?request()->param('companyEnName/s'):'';
            if(!$company->companyEnName) errorMsg(400,'公司英文名称不能为空');
            $company->companyAdd=request()->param('companyAdd/s')?request()->param('companyAdd/s'):'';
            if(!$company->companyAdd) errorMsg(400,'公司地址不能为空');
            $company->domain=request()->param('domain/s')?request()->param('domain/s'):'';
            if(!$company->domain) errorMsg(400,'公司域名不能为空');
            $company->businessId=request()->param('businessId/s')?request()->param('businessId/s'):'';
            $company->legalPerson=request()->param('legalPerson/s')?request()->param('legalPerson/s'):'';
            $company->legalPersonPhone=request()->param('legalPersonPhone/s')?request()->param('legalPersonPhone/s'):'';
            $types=request()->param('types/s')?request()->param('types/s'):'';
            if(!$types) errorMsg(400,'请选择公司性质');
            $company->companyType=trim($types,',');
            $company->cooperation=request()->param('cooperation/d')?request()->param('cooperation/d'):0;
            $company->advance=request()->param('advance/f')?request()->param('advance/f'):0;
            $company->unitPrice=request()->param('unitPrice/f')?request()->param('unitPrice/f'):0;
            $company->num=request()->param('num/d')?request()->param('num/d'):0;
            $where['companyName']=$company->companyName;
            $where['companyShortName']=$company->companyShortName;
            $where['companyEnName']=$company->companyEnName;
            $result=db('company')->where($where)->find();
            if($result) errorMsg(400,'该公司已经存在，请验证！');
            $data['nickName']=request()->param('nickName/s')?request()->param('nickName/s'):'';
            if(!$data['nickName']) errorMsg(400,'管理员昵称不能为空');
            $data['mobile']=request()->param('mobile/s')?request()->param('mobile/s'):'';
            if(!$data['mobile']) errorMsg(400,'管理员登陆手机号码不能为空');
            $password=request()->param('password/s')?request()->param('password/s'):'';
            if(!$password) errorMsg(400,'管理员登陆密码不能为空');
            $data['password']=md5($password.'erp');
            $data['email']=request()->param('email/s')?request()->param('email/s'):'';
            if(!$data['email']) errorMsg(400,'管理员邮箱不能为空');
            $data['isAdmin']=1;
            try{
                Db::startTrans();
                $company->save();
                $company->users()->save($data);
                Db::commit();
                errorMsg(200,'done');
            }catch(Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            $companyCode='C'.date('His').str_pad(rand(0,999), 3, '0', STR_PAD_LEFT);
            $this->assign('companyCode',$companyCode);
            $this->assign('nav','addCompany');
            return $this->fetch();
        }
    }

    //作者：于明明
    //功能：编辑公司
    public function editCompany(){
        $companyId=request()->param('id/d')?request()->param('id/d'):0;
        if(request()->isPost()){
            if(!$companyId) errorMsg(400,'公司信息有误，请验证！');
            $company=Company::get($companyId);
            $company->companyName=request()->param('companyName/s')?request()->param('companyName/s'):'';
            if(!$company->companyName) errorMsg(400,'公司名称不能为空');
            $company->companyShortName=request()->param('companyShortName/s')?request()->param('companyShortName/s'):'';
            if(!$company->companyShortName) errorMsg(400,'公司简称不能为空');
            $company->companyEnName=request()->param('companyEnName/s')?request()->param('companyEnName/s'):'';
            if(!$company->companyEnName) errorMsg(400,'公司英文名称不能为空');
            $company->companyAdd=request()->param('companyAdd/s')?request()->param('companyAdd/s'):'';
            if(!$company->companyAdd) errorMsg(400,'公司地址不能为空');
            $company->domain=request()->param('domain/s')?request()->param('domain/s'):'';
            if(!$company->domain) errorMsg(400,'公司域名不能为空');
            $company->businessId=request()->param('businessId/s')?request()->param('businessId/s'):'';
            $company->legalPerson=request()->param('legalPerson/s')?request()->param('legalPerson/s'):'';
            $company->legalPersonPhone=request()->param('legalPersonPhone/s')?request()->param('legalPersonPhone/s'):'';
            $types=request()->param('types/s')?request()->param('types/s'):'';
            if(!$types) errorMsg(400,'请选择公司性质');
            $company->companyType=trim($types,',');
            $company->cooperation=request()->param('cooperation/d')?request()->param('cooperation/d'):0;
            $company->advance=request()->param('advance/f')?request()->param('advance/f'):0;
            $company->unitPrice=request()->param('unitPrice/f')?request()->param('unitPrice/f'):0;
            $company->num=request()->param('num/d')?request()->param('num/d'):0;
            $where['companyName']=$company->companyName;
            $where['companyShortName']=$company->companyShortName;
            $where['companyEnName']=$company->companyEnName;
            $result=db('company')->where($where)->find();
            if($result){
                if($result['companyId']!=$companyId){
                    errorMsg(400,'该公司已经存在，请验证！');
                }
            }
            $data['nickName']=request()->param('nickName/s')?request()->param('nickName/s'):'';
            if(!$data['nickName']) errorMsg(400,'管理员昵称不能为空');
            $data['mobile']=request()->param('mobile/s')?request()->param('mobile/s'):'';
            if(!$data['mobile']) errorMsg(400,'管理员登陆手机号码不能为空');
            $password=request()->param('password/s')?request()->param('password/s'):'';
            if($password){
                $data['password']=md5($password.'erp');
            }
            $data['email']=request()->param('email/s')?request()->param('email/s'):'';
            if(!$data['email']) errorMsg(400,'管理员邮箱不能为空');
            try{
                Db::startTrans();
                $company->save();
                $uwhere['companyId']=$companyId;
                $uwhere['isAdmin']=1;
                db('users')->where($uwhere)->update($data);
                Db::commit();
                errorMsg(200,'done');
            }catch(Exception $e){
                Db::rollback();
                errorMsg(400,$e->getMessage());
            }
        }else{
            if(!$companyId) $this->error('公司信息有误，请验证！','/Admin/Index/index');
            $where['c.companyId']=$companyId;
            $where['c.status']=1;
            $data=db('company')->alias('c')->join('erp_users u','u.companyId=c.companyId and u.isAdmin=1')->where($where)->field('c.companyCode,c.companyName,c.companyShortName,c.companyEnName,c.companyAdd,c.businessId,c.legalPerson,c.legalPersonPhone,c.companyType,c.cooperation,c.advance,c.unitPrice,c.num,c.domain,c.remark,u.nickName,u.mobile,u.email')->find();
            $companyTypes[0]['typeName']='电商企业';
            $companyTypes[0]['typeVal']='1';
            $companyTypes[0]['isChecked']=0;
            if(strpos($data['companyType'],'1')!==false){
                $companyTypes[0]['isChecked']=1;
            }
            $companyTypes[1]['typeName']='仓库企业';
            $companyTypes[1]['typeVal']='2';
            $companyTypes[1]['isChecked']=0;
            if(strpos($data['companyType'],'2')!==false){
                $companyTypes[1]['isChecked']=1;
            }
            $companyTypes[2]['typeName']='贸易公司';
            $companyTypes[2]['typeVal']='3';
            $companyTypes[2]['isChecked']=0;
            if(strpos($data['companyType'],'3')!==false){
                $companyTypes[2]['isChecked']=1;
            }
            $this->assign('nav','addCompany');
            $this->assign('data',$data);
            $this->assign('companyTypes',$companyTypes);
            $this->assign('id',$companyId);
            return $this->fetch();
        }
    }

    //作者：于明明
    //功能：禁用/启用公司
    public function delCompany(){
        $companyId=request()->param('companyId/d')?request()->param('companyId/d'):0;
        $status=request()->param('status/d')?request()->param('status/d'):0;
        if(!$companyId) errorMsg(400,'公司信息有误，请验证！');
        $company=Company::get($companyId);
        Db::startTrans();
        try{
            $company->status=$status;
            $company->save();
            Db::commit();
            errorMsg(200,'done');
        }catch(Exception $e){
            Db::rollback();
            errorMsg(400,$e->getMessage());
        }
    }

    //作者：于明明
    //功能：彻底删除公司
    public function purgeCompany(){
        $companyId=request()->param('companyId/d')?request()->param('companyId/d'):0;
        if(!$companyId) errorMsg(400,'公司信息有误，请验证！');
        $company=Company::get($companyId);
        Db::startTrans();
        try{
            $company->delete();
            $company->users()->delete();
            Db::commit();
            errorMsg(200,'done');
        }catch(Exception $e){
            Db::rollback();
            errorMsg(400,$e->getMessage());
        }
    }
}
