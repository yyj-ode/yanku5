<?php
/**
 * Created by PhpStorm.
 * User: wangfuruo
 * Date: 2017/8/29
 * Time: 下午3:17
 */
namespace app\version3\controller;
use think\Request;
use think\Db;
use think\response\Json;

class Apple extends Basic{
    function ym_send(){
        $ser['ifa'] = Request::instance()->get('ifa','');
        $ins['callback_url'] = Request::instance()->get('callback_url','');
        if (''==$ins['callback_url']||''==$ser['ifa']){
            die();
        }
        $res = Db::table('yk_apple')->where($ser)->find();
        if ($res){
            $ins = Db::table('yk_apple')->where($ser)->update($ins);
            $url = urldecode($ins['callback_url']);
            $out = cu_get($url);
            $output_array = json_decode($out,true);
            return Json(array("code"=>1));
        }else{
            return Json(array("code"=>0));
        }
    }
    function mb_send(){
        $ins['ifa'] = Request::instance()->post('ifa','');
        $res = Db::table('yk_apple')->insert($ins);
        if ($res){
            return Json(array('code'=>1));
        }else{
            return Json(array('code'=>0));
        }
    }
}