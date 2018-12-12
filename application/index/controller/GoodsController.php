<?php
namespace app\index\controller;
use think\Session;
use think\Config;
use think\Db;
use app\index\model\Goods;
use app\index\model\Goods_boom;
/*
 *商品类
 * @jieson 2018.01.31
 */
class GoodsController extends BaseController{
    public function _initialize() {
        parent::_initialize();
    }
    /*
     * 商品列表
     */
    public function goodsList(){
        $where='gs.deleted=0 and gs.companyId='.$this->companyId." and concat(gs.goodsName,gs.goodsCode,gs.brand,gs.specType,un.unitName,gs.price,c.name) like '%".trim(request()->param('search_goods'))."%'";
        $goodsList=db('goods')->alias('gs')->join('country c','c.code=gs.country')->join('goods_unit un','un.unitCode=gs.stockUnit')->join('goods_cate gc','gc.id=gs.cateId')->where($where)->field('gs.*,c.name as cname,un.unitName,gc.cName as cateName')->order('id desc')->paginate();
        $this->assign('goodsList',$goodsList);
        $this->assign('search_goods',request()->param('search_goods'));
        $this->assign('button',Config::get('func'.$this->authId.'.goodsList'));
        Session::set('subNav','goods');
        $this->assign('nav','goods');
        $this->assign('subNav','goodsList');
        return $this->fetch();
    }
    /*
     * 添加商品
     */
    public function addGoods(){
        $this->goodsBase();
        $this->assign('button',Config::get('func'.$this->authId.'.goodsList'));
        Session::set('subNav','goods');
        $this->assign('nav','goods');
        $this->assign('subNav','goodsList');
        return $this->fetch();
    }
    //处理商品添加
    public function addGoods_handle(){
        Db::startTrans();
        try {
            $goodsModel = new Goods($_POST);
            // 过滤post数组中的非数据表字段数据
            $goodsModel->allowField(true)->save();
            Db::commit();
            errorMsg(200,'添加成功');
        } catch (Exception $ex) {
            Db::rollback();
            errorMsg(400, '添加失败');
        }
    }
    //编辑商品
    public function editGoods(){
        $this->goodsBase();
        $where='deleted=0 and companyId='.$this->companyId.' and id='.request()->param('id/s');
        $goodsList=db('goods')->where($where)->find();
        $this->assign('goodsList',$goodsList);
        Session::set('subNav','goods');
        $this->assign('nav','goods');
        $this->assign('subNav','goodsList');
        return $this->fetch();
    }
    //编辑商品处理
    public function editGoods_handle(){
        Db::startTrans();
        try {
            $goodsModel = new Goods();
            // 过滤post数组中的非数据表字段数据
            $goodsModel->allowField(true)->save($_POST,['id' => request()->param('id')]);
            Db::commit();
            errorMsg(200,'修改成功');
        } catch (Exception $ex) {
            Db::rollback();
            errorMsg(400, '修改失败');
        }
    }
    //删除商品
    public function del_goods(){
        Db::startTrans();
        try {
            $goodsModel = new Goods();
            $goodsModel->where('id','exp','in ('.request()->param('id/s').')')->update(['deleted'=>1]);
            Db::commit();
            errorMsg(200,'删除成功');
        } catch (Exception $ex) {
            Db::rollback();
            errorMsg(400, '删除失败');
        }
    }
    //商品批量导入
    public function goodsImport(){
        Session::set('subNav','goods');
        $this->assign('nav','goods');
        $this->assign('subNav','goodsList');
        return $this->fetch();
    }
    //
    public function goodsImport_handle(){
        $file = request()->file('file');
        // 移动到框架应用根目录/public/upload/enclosure/ 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upload' . DS . 'enclosure');
            if($info){
                $goodsData= self::getExcelData(ROOT_PATH . 'public' . DS . 'upload' . DS . 'enclosure' . DS . $info->getSaveName());
                $result=array();//返回的数据集合
                foreach ($goodsData as $key=> $v){
                    $result[$key]['status']=200;#成功
                    $result[$key]['successNum']=0;#成功数
                    $result[$key]['failNum']=0;#失败数
                    $result[$key]['goodsName']=null;#失败的商品名
                    $result[$key]['goodsCode']=null;#失败的商品编码
                    $result[$key]['info']=null;#提示信息
                    $existGoods=db('goods')->where("goodsCode ='".$v[3]."'")->field('goodsCode')->find();
                    if($existGoods){
                        $result[$key]['status']=400;#失败
                        $result[$key]['failNum']++;
                        $result[$key]['goodsName']=$v[0];
                        $result[$key]['goodsCode']=$v[3];
                        $result[$key]['info']= "该商品-".$v[0]."-已存在";
                        //echo "该商品-".$v[0]."-已存在";
                        continue;
                    }
                    //判断计量单位、原产国中是否有中文
                    if (preg_match("/[\x7f-\xff]/",$v[6])) {
                        $search=array('unitName'=>$v[6]);
                        $resUnit=db('unit')->where($search)->field('unitCode')->find();
                        if($resUnit){
                            $v[6]=$resUnit['unitCode'];
                        }else{
                            $result[$key]['status']=400;#失败
                            $result[$key]['failNum']++;
                            $result[$key]['goodsName']=$v[0];
                            $result[$key]['goodsCode']=$v[3];
                            $result[$key]['info']= '系统没有这个计量单位--'.$v[6];
                            //echo '系统没有这个计量单位--'.$v[6];exit;
                            continue;
                        }                   
                    }
                    if (preg_match("/[\x7f-\xff]/",$v[20]) || preg_match('/[a-zA-Z]/',$v[20])) {                     
                        $search="name='".$v[20]."' or eName='".$v[20]."'";
                        $sql="select code from sdms_country where name='".$v[20]."' or eName='".$v[20]."'";
                        $resCountry=db('goods')->query($sql);
                        if($resCountry){                           
                            $v[20]=$resCountry[0]['code'];
                        }else{
                            //echo '系统没有这个国家--'.$v[20];exit;
                            $result[$key]['status']=400;#失败
                            $result[$key]['failNum']++;
                            $result[$key]['goodsName']=$v[0];
                            $result[$key]['goodsCode']=$v[3];
                            $result[$key]['info']= '系统没有这个国家--'.$v[20];
                        }                        
                    }
                    if(preg_match("/[\x7f-\xff]/",$v[24]) || preg_match('/[a-zA-Z]/',$v[24])){
                        $result[$key]['status']=400;#失败
                        $result[$key]['failNum']++;
                        $result[$key]['goodsName']=$v[0];
                        $result[$key]['goodsCode']=$v[3];
                        $result[$key]['info']= '这个海关商品申报系数有误--'.$v[24];
                    }                    
                    $data=array(
                        'categoryName'=>str_replace("\r\n\t",' ',trim(htmlspecialchars($v[2]))),
                        'brand'=>str_replace("\r\n\t",' ',trim(htmlspecialchars($v[1]))),
                        'goodsCode'=>str_replace("\r\n\t",' ',trim($v[3])),
                        'goodsBarcode'=>str_replace("\r\n\t",' ',trim($v[4])),
                        'goodsName'=>str_replace("\r\n\t",' ',trim(htmlspecialchars($v[0]))),
                        'specType'=>str_replace("\r\n\t",' ',trim($v[5])),
                        'stockUnit'=>str_replace("\r\n\t",' ',trim($v[6])),//计量单位
                        'unitGrossWeight'=>str_replace("\r\n\t",' ',trim($v[7])),
                        'unitNetWeight'=>str_replace("\r\n\t",' ',trim($v[8])),
                        'length'=>str_replace("\r\n\t",' ',trim($v[9])),
                        'width'=>str_replace("\r\n\t",' ',trim($v[10])),
                        'height'=>str_replace("\r\n\t",' ',trim($v[11])),
                        'unitVol'=>str_replace("\r\n\t",' ',trim($v[12])),
                        'recordPrice'=>str_replace("\r\n\t",' ',trim($v[13])),
                        'postNo'=>str_replace("\r\n\t",' ',trim($v[14])),
                        'country'=>str_replace("\r\n\t",' ',trim($v[20])),//原产国
                        'producer'=>str_replace("\r\n\t",' ',trim(htmlspecialchars($v[21]))),
                        'producerAdr'=>str_replace("\r\n\t",' ',trim(htmlspecialchars($v[22]))),
                        'Hscode'=>str_replace("\r\n\t",' ',trim($v[15])),
                        'cusName'=>str_replace("\r\n\t",' ',trim(htmlspecialchars($v[16]))),
                        'ingredient'=>str_replace("\r\n\t",' ',trim(htmlspecialchars($v[17]))),
                        'additives'=>str_replace("\r\n\t",' ',trim($v[18])),
                        'nasties'=>str_replace("\r\n\t",' ',trim($v[19])),
                        'notes'=>str_replace("\r\n\t",' ',trim(htmlspecialchars($v[23]))),
                        'conversionFactor'=>str_replace("\r\n\t",' ',trim($v[24])),//海关申报系数
                        'secondNumber'=>str_replace("\r\n\t",' ',trim($v[25])),//第二数量
                        'record'=>0,
                        'companyId'=>$this->companyId//代理价
                        );
                    Db::startTrans();
                    try {
                        $goodsMedol = new Goods();
                        // 过滤post数组中的非数据表字段数据
                        $goodsMedol->allowField(true)->save($data);
                        
                        $result[$key]['status']=200;
                        $result[$key]['successNum']++;
                        $result[$key]['goodsName']=$v[0];
                        $result[$key]['goodsCode']=$v[3];
                        $result[$key]['info']='添加成功';
                        
                        Db::commit();
                    } catch (Exception $ex) {
                        $result[$key]['status']=400;
                        $result[$key]['failNum']++;
                        $result[$key]['goodsName']=$v[0];
                        $result[$key]['goodsCode']=$v[3];
                        $result[$key]['info']='添加失败：';//返回错误信息
                        
                        Db::rollback();
                    }
                }
                usleep(500000);//休眠0.5秒
                errorMsg(200, 'success',$result);
            }else{
                // 上传失败获取错误信息
                errorMsg(400, $file->getError());
            }
        }else{
            errorMsg(400, '没有这个文件','没有这个文件');
        }
        
    }
    //读取excel文件的内容
    protected function getExcelData($filename){
        //$filename会出现路径的问题
        vendor("phpexcel.PHPExcel");//引入phpexcel类
        $objPHPExcel = \PHPExcel_IOFactory::createReaderForFile($filename);//use excel2007 for 2007 format
        //$objPHPExcel =\PHPExcel_IOFactory::createReader('Excel2007');
        $objReader = $objPHPExcel->load($filename); //加载文件
        $objWorksheet = $objReader->setActiveSheetIndex();//读取第一个工作表
        $highestRow = $objWorksheet->getHighestRow(); //取得总行数
        $highestColumn = $objWorksheet->getHighestColumn(); //取得总列数
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData=[];$orderSn='';
        for ($row = 2; $row <= $highestRow; $row++) {            
            $orderSn=(string)$objWorksheet->getCellByColumnAndRow(1, $row)->getValue();           
            for ($col = 1; $col < $highestColumnIndex; $col++) {                
                $afcol = \PHPExcel_Cell::stringFromColumnIndex($col);               
                $value=(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();               
                $excelData[$row][]=$value;
            }
        }
        return $excelData;
    }
    //商品基础数据
    protected function goodsBase(){
        //品牌
        $goodsBrand=db('goods_brand')->where('status',1)->where('companyId',$this->companyId)->field('id,name')->select();
        $this->assign('goodsBrand',$goodsBrand);
        //分类
        $resCategory=db('goods_cate')->where('status',1)->where('companyId',$this->companyId)->field('id,topId,cName,createTime,status')->select();
        $goodsCate=$this->Cate($resCategory);
        $this->assign('goodsCate',$goodsCate);
        //计量单位
        $goodsUnit=db('goods_unit')->where('status',1)->field('id,unitCode,unitName')->select();
        $this->assign('goodsUnit',$goodsUnit);
        //行邮税
        $goodsRecord=db('goods_record')->where('status',1)->field('id,parcelNumber,goodsName')->select();
        $this->assign('goodsRecord',$goodsRecord);
        //hscode
        $goodsHscode=db('goods_hscode')->where('status',1)->field('id,hsCode,hsName')->select();
        $this->assign('goodsHscode',$goodsHscode);
        //国家
        $country=db('country')->where('status',1)->field('id,name,code')->select();
        $this->assign('country',$country);
    }
    
    /*
     * 商品分类
     */
    public function category(){
        $resCategory=db('goods_cate')->field('id,topId,cName,createTime,status')->where(array('status'=>1,'companyId'=>$this->companyId))->select();
        $Category=$this->Cate($resCategory);
        $this->assign('category',$Category);
        Session::set('subNav','goods');
        $this->assign('button',Config::get('func'.$this->authId.'.category'));
        $this->assign('nav','goods');
        $this->assign('subNav','category');
        return $this->fetch();
    }
    //无限级分类
    protected function Cate($data,$pid=0,$level=0) {
        $arr=array();
        foreach($data as $k=>$v){
            if($v['topId']==$pid){
                $v['level']=$level+1;               
                $arr[] = $v;
                unset($data[$k]); //注销当前节点数据，减少已无用的遍历
                $arr = array_merge($arr,$this->Cate($data,$v['id'],$level+1));
            }
        }
        return $arr;
    }
    //添加分类
    public function addCategory(){
        //获取分类二级分类
        $cateId=request()->param('topId')?request()->param('topId'):0;
        $topCate=db('goods_cate')->field('id,topId,cName')->where(array('topId'=>$cateId,'status'=>1,'companyId'=>$this->companyId))->select();
        if(request()->param('topId')){
            return $topCate;
        }
        //获取顶级分类   
        $this->assign('topCate',$topCate);
        Session::set('subNav','goods');
        $this->assign('nav','goods');
        $this->assign('subNav','category');
        return $this->fetch();
    }
    //post处理
    public function cate_handle_post(){
        $topId=request()->param('topId');
        $secondId=request()->param('secondId');
        $cName=request()->param('cName');
        $data=array('topId'=>$topId,'cName'=>$cName,'companyId'=>$this->companyId);
        if($secondId!=0){
            $data['topId']=$secondId;
        }
        Db::startTrans();
        try{
            Db::name('goods_cate')->insert($data);
            Db::commit();
            //日志
            //Db::name('action')->insert(array('userId'=>$this->authId,'content'=>'商品分类:'.$cName,'desc'=>'添加商品分类'));
            errorMsg(200, '添加成功');
        } catch (Exception $ex) {
            Db::rollback();
            errorMsg(400,'添加失败');
        }
    }
    //移除分类
    public function delCategory(){
        Db::startTrans();
        try{
            Db::name('goods_cate')->where('id',request()->param('cateId'))->update(array('status'=>0));
            Db::commit();
            //日志
            //Db::name('action')->insert(array('userId'=>$this->userId,'content'=>'商品分类Id:'.request()->param('cateId'),'desc'=>'移除商品分类'));
            errorMsg(200, '删除成功');
        } catch (Exception $ex) {
            Db::rollback();
            errorMsg(400,'删除失败');
        }
    }
    //商品品牌
    public function goodsBrand(){
        //搜索
        $search_brand=request()->param('search_brand')?request()->param('search_brand'):'';
        $where="status=1 and companyId=".$this->companyId;
        if(request()->param('search_brand')){
            $where="status=1 and companyId=".$this->companyId." and concat(name) like '%".request()->param('search_brand')."%'";
        }
        Session::set('subNav','goods');
        //数据 paginate分页
        $goodsBrand=Db::name('goods_brand')->where($where)->field('id,name,logo,desc,url,createTime')->order('id desc')->paginate();
        $this->assign('goodsBrand',$goodsBrand);
        $this->assign('search_brand',$search_brand);
        $this->assign('button',Config::get('func'.$this->authId.'.goodsBrand'));
        $this->assign('nav','goods');
        $this->assign('subNav','goodsBrand');
        return $this->fetch();
    }
    //添加品牌
    public function addGoodsBrand(){
        Session::set('subNav','goods');
        $this->assign('button',Config::get('func'.$this->authId.'.goodsBrand'));
        $this->assign('nav','goods');
        $this->assign('subNav','goodsBrand');
        return $this->fetch();
    }
    //添加品牌处理
    public function addGoodsBrand_handle(){
        //若是有logo
        if($_FILES){
            $filename =$_FILES['file']['name'];
            //ROOT_PATH . 'public' . DS . 'upload' . DS . 'enclosure'
            //$filepath = "F:/wamp/www/erp/public/upload/brand/".$filename;
            $filepath=ROOT_PATH . 'public' . DS . 'upload' . DS . 'brand' . DS . $filename;
            $tmpname  =$_FILES['file']['tmp_name'];
            Session::set('filename',$filename);
            move_uploaded_file($tmpname,$filepath);
            Session::set('filepath',$filepath);
            Session::set('tmpname',$tmpname);
            return true;
        }
        
        //数据处理
        $name=htmlspecialchars(trim(request()->param('name')));
        $desc=htmlspecialchars(trim(request()->param('desc')));
        $url =htmlspecialchars(trim(request()->param('url')));
        
        $logo    =Session::get('filename');
        $filepath=Session::get('filepath');
        $tmpname =Session::get('tmpname');
        
        $data=['name'=>$name,'desc'=>$desc,'url'=>$url,'logo'=>$logo,'companyId'=>$this->companyId,'createTime'=>time()];
        Db::startTrans();
        try{
            $inserId=Db::name('goods_brand')->insert($data);
            if($inserId && $logo && $filepath && $tmpname){
                //move_uploaded_file($tmpname,$filepath);
                Session::set('filepath','');
                Session::set('tmpname','');
                Session::set('filename','');
            }
            //写日志
            //Session::set('filename','');
            Db::commit();
            errorMsg(200,'添加成功');
        } catch (Exception $ex) {
            Db::rollback();
            errorMsg(400,'添加失败');
        }
    }
    //编辑品牌
    public function editGoodsBrand(){
        Session::set('subNav','goods');
        $goodsBrand=db('goods_brand')->where('id',request()->param('id/s'))->find();
        $this->assign('goodsBrand',$goodsBrand);
        $this->assign('button',Config::get('func'.$this->authId.'.goodsBrand'));
        $this->assign('nav','goods');
        $this->assign('subNav','goodsBrand');
        return $this->fetch();
    }
    //editGoodsBrand_handle
    public function editGoodsBrand_handle(){
        //数据处理
        $name=htmlspecialchars(trim(request()->param('name')));
        $desc=htmlspecialchars(trim(request()->param('desc')));
        $url =htmlspecialchars(trim(request()->param('url')));
        
        $data=['name'=>$name,'desc'=>$desc,'url'=>$url,'companyId'=>$this->companyId,'createTime'=>time()];
        Db::startTrans();
        try{
            Db::name('goods_brand')->where('id',request()->param('id/s'))->update($data);          
            //写日志
            
            Db::commit();
            errorMsg(200,'修改成功');
        } catch (Exception $ex) {
            Db::rollback();
            errorMsg(400,'修改失败');
        }
    }
    //删除品牌
    public function del_goods_brand(){
        Db::startTrans();
        try {
            // 过滤post数组中的非数据表字段数据
            db('goods_brand')->where('id','exp','in ('.request()->param('id/s').')')->update(array('status'=>0));
            Db::commit();
            errorMsg(200,'删除成功');
        } catch (Exception $ex) {
            Db::rollback();
            errorMsg(400, '删除失败');
        }
    }


    //商品sku
    public function goodsSKU(){
        $where='sku.status=1 and sku.companyId='.$this->companyId." and concat(sku.sku,gs.goodsName,gs.goodsCode,c.companyName,c.companyShortName,w.warehouseName) like '%".trim(request()->param('search_sku'))."%'";
        $goodsSku=db('goods_sku')->alias('sku')->join('goods gs','gs.goodsCode=sku.goodsCode')->join('company c','c.companyId=sku.companyId')->join('warehouse w','w.id=sku.warehouseId')->where($where)->field('sku.id,sku.goodsCode,sku.sku,sku.createTime,gs.goodsName,c.companyShortName,w.warehouseName')->paginate();
        $this->assign('goodsSku',$goodsSku);
        $this->assign('search_sku',request()->param('search_sku'));
        $this->assign('button',Config::get('func'.$this->authId.'.goodsSKU'));
        Session::set('subNav','goods');
        $this->assign('nav','goods');
        $this->assign('subNav','goodsSKU');
        return $this->fetch();
    }
    //添加商品sku
    public function addGoodsSKU(){
        //电商企业
        $where='status=1 and companyId='.$this->companyId;
        $company=db('company')->where($where)->field('companyId,companyName')->find();
        $this->assign('company',$company);
        //仓储企业
        $warehouse=db('warehouse')->where($where)->field('id,warehouseName')->select();
        $this->assign('warehouse',$warehouse);
        //商品
        $goods=db('goods')->where('deleted=0 and companyId='.$this->companyId)->where('id',request()->param('id'))->field('id,goodsCode,goodsName,specType')->find();
        $this->assign('goods',$goods);
        //商品对应的sku
        $goodsSKU=db('goods_sku')->alias('sku')->join('goods gs','gs.goodsCode=sku.goodsCode')->join('company c','c.companyId=sku.companyId')->join('warehouse w','w.id=sku.warehouseId')->where('gs.deleted=0 and gs.companyId='.$this->companyId)->where('gs.id',request()->param('id'))->field('c.companyShortName,w.warehouseName,gs.goodsCode,gs.goodsName,sku.id,sku.sku,sku.createTime')->select();
        $this->assign('goodsSKU',$goodsSKU);
        
        Session::set('subNav','goods');
        $this->assign('nav','goods');
        $this->assign('subNav','goodsSKU');
        return $this->fetch();
    }
    //处理提交的新增商品sku
    public function addGoodsSKU_handle(){
        $data['companyId']=request()->param('companyId');
        $data['warehouseId']=request()->param('warehouseId');
        $data['sku']=htmlspecialchars(trim(request()->param('sku')));
        $data['goodsCode']=request()->param('goodsCode');
        $data['createTime']=time();
        //判断
        $check=db('goods_sku')->where('companyId',$data['companyId'])->where('warehouseId',$data['warehouseId'])->where('goodsCode',$data['goodsCode'])->whereOr('sku',$data['sku'])->find();
        if($check){
            errorMsg(400,$data['goodsCode'].'-该商品编码已在该仓储企业存在');
        }
        Db::startTrans();
        try{
            Db::name('goods_sku')->insert($data);
            Db::commit();
            errorMsg(200,'添加成功');
        } catch (Exception $ex) {
            Db::rollback();
            errorMsg(400,'添加失败');
        }
    }
    //
    public function editGoodsSKU_handle(){
        $check=db('goods_sku')->where('companyId',$this->companyId)->where('sku',trim(request()->param('sku')))->find();
        if($check){
            errorMsg(400,request()->param('sku').'-该sku编码已在该存在');
        }
        Db::startTrans();
        try{
            Db::name('goods_sku')->where('id',request()->param('skuId'))->update(array('sku'=>trim(request()->param('sku')),'createTime'=>time()));
            Db::commit();
            errorMsg(200,'修改成功');
        } catch (Exception $ex) {
            Db::rollback();
            errorMsg(400,'修改失败');
        }
    }
    //删除sku
    public function del_goods_sku(){
        Db::startTrans();
        try {
            // 过滤post数组中的非数据表字段数据
            db('goods_sku')->where('id','exp','in ('.request()->param('id/s').')')->update(array('status'=>0));
            Db::commit();
            errorMsg(200,'删除成功');
        } catch (Exception $ex) {
            Db::rollback();
            errorMsg(400, '删除失败');
        }
    }
    //商品boom
    public function goodsBoom(){
        $where="gs.status=1 and gs.companyId=".$this->companyId." and concat(gs.goodsName,gs.goodsCode,gs.brand,gs.specType,un.unitName,gs.price,c.name) like '%".trim(request()->param('search_goods'))."%'";
        $goodsBoom=db('goods_boom')->alias('gs')->join('goods_unit un','un.unitCode=gs.stockUnit')->join('country c','c.code=gs.country')->where($where)->field('gs.boomId,gs.goodsCode,gs.goodsName,gs.specType,gs.brand,gs.price,gs.childGoods,un.unitName as stockUnit,c.name as country')->select();
        //获取子商品
        foreach($goodsBoom as $k=>$v){
            $goodsArr= json_decode($v['childGoods']);
            foreach ($goodsArr as $k1=>$gs){
                $goods=db('goods')->where('id',$gs->goodsId)->field('goodsCode,goodsName,specType,price')->find();
                $goods['num']=$gs->goodsNum;
                $childGoodsArr[$k1]=$goods;
            }
            $goodsBoom[$k]['childGoods_arr']=$childGoodsArr;
        }
        $this->assign('search_goods',request()->param('search_goods'));
        //dump($goodsBoom);exit;
        $this->assign('goodsBoom',$goodsBoom);
        $this->assign('button',Config::get('func'.$this->authId.'.goodsBoom'));
        Session::set('subNav','goods');
        $this->assign('nav','goods');
        $this->assign('subNav','goodsBoom');
        return $this->fetch();
    }
    //新增boom
    public function addGoodsBoom(){
        self::goodsBase();
        Session::set('subNav','goods');
        $this->assign('nav','goods');
        $this->assign('subNav','goodsBoom');
        return $this->fetch();
    }
    //保存goodsBoom
    public function addGoodsBoom_handle(){
         Db::startTrans();
        try {
            $goodsBoomModel = new Goods_boom($_POST);
            // 过滤post数组中的非数据表字段数据
            $goodsBoomModel->companyId=$this->companyId;
            foreach ($_POST['childGoods'] as $v){
                $goodsData[]=(array)json_decode($v);
            }
            $goodsBoomModel->childGoods= json_encode($goodsData);
            $goodsBoomModel->allowField(true)->save();
            Db::commit();
            errorMsg(200,'添加成功');
        } catch (Exception $ex) {
            Db::rollback();
            errorMsg(400, '添加失败');
        }
    }
    //打开新增boom子商品
    public function addGoodsBoom_open(){
        $where='gs.deleted=0 and gs.companyId='.$this->companyId." and concat(gs.goodsName,gs.goodsCode,gs.brand,gs.specType,un.unitName,gs.price) like '%".trim(request()->param('search_goods'))."%'";
        $goodsList=db('goods')->alias('gs')->join('goods_unit un','un.unitCode=gs.stockUnit')->where($where)->field('gs.id,gs.goodsCode,gs.goodsName,gs.brand,gs.specType,un.unitName')->order('gs.id desc')->paginate();
        $this->assign('goodsList',$goodsList);
        $this->assign('search_goods',request()->param('search_goods'));
        return $this->fetch();
    }
    //删除品牌
    public function del_goods_boom(){
        Db::startTrans();
        try {
            // 过滤post数组中的非数据表字段数据
            db('goods_boom')->where('boomId','exp','in ('.request()->param('id/s').')')->update(array('status'=>0));
            Db::commit();
            errorMsg(200,'删除成功');
        } catch (Exception $ex) {
            Db::rollback();
            errorMsg(400, '删除失败');
        }
    }
    //添加goodsBoom的数据中转处理
    public function addGoodsBoom_dataHandle(){
        $goodsNumArr=$_POST['childGoods_num'];
        $goodsIdArr=$_POST['goodsId'];
        $returnData=[];
        foreach($goodsIdArr as $key=> $v){
            $goodsData=db('goods')->alias('gs')->join('goods_unit un','un.unitCode=gs.stockUnit')->where('gs.id',$v)->field('gs.goodsCode,gs.goodsName,gs.specType,gs.price,un.unitName')->find();
            $goodsData['num']=$goodsNumArr[$key];
            $goodsData['goodsArr']= json_encode(['goodsId'=>$v,'goodsNum'=>$goodsNumArr[$key]]);
            $returnData[]=$goodsData;
        }
        errorMsg(200,'success',$returnData);
    }
}
