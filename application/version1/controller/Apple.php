<?php
/**
 * Created by PhpStorm.
 * User: wangfuruo
 * Date: 2017/8/29
 * Time: 下午3:17
 */
namespace app\version1\controller;
use think\Request;
use think\Db;

class Apple extends Basic{
    function cb(){
        $ins['appleid'] = Request::instance()->get('appleid');
        $ins['idfa'] = Request::instance()->get('idfa');
        $ins['ip'] = Request::instance()->get('ip');
        $res = Db::table('yk_apple')->insert($ins);
        if ($res){
            return Json(array("status"=>"true","message"=>"success"));
        }else{
            return Json(array("status"=>"false","message"=>"fail"));
        }
    }
}