<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/*
 * @param $code
 * @param $msg
 * @param $data
 */
function errorMsg($code,$msg,$data = ''){
    $jsondata = array("statusCode"=>$code,"retMessage"=>$msg,"data"=>$data);
    echo json_encode($jsondata);
    exit(0);
}