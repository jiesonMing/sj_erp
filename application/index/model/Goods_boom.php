<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\index\model;
use think\Model;

/**
 * Description of Goods
 *
 * @author Administrator
 */
class Goods_boom extends Model{
    //put your code here
    public function country(){
        return $this->hasMany('Country')->field('name');
    }
}
