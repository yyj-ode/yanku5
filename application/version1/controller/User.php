<?php
/**
 * Created by PhpStorm.
 * User: wangfuruo
 * Date: 2017/8/27
 * Time: 下午3:41
 */
namespace app\version1\controller;
use think\Db;
use think\Request;
use think\response\Json;
use think\Swoole\Server;
use Workerman\Worker;
class User extends Basic{// extends Basic
    private $options = array('client_id'=>'YXA6ryLaYB6qEee9ag-MCEtXOA',
        'client_secret'=>'YXA6P275ejRYIcHpCHj_eqtonJUmJb4',
        'org_name'=>'1134170411178481',
        'app_name'=>'yanku');
    function login(){

    }
    function userSchedule(){
        switch ($this->method){
            case 'get': // get请求处理代码
                //用户端获取通告列表
                $user_id = Request::instance()->get('user_id');
                $token = Request::instance()->get('token');
                self::tokenAudit($user_id,$token);
                //最后一条面试的ID
                $id = Request::instance()->get('id',0);
                $res = Db::table('yk_interview')
                         ->field('yk_interview.inter_id,yk_interview.user_id,yk_user.user_img,yk_user.sex,yk_user.nickname,yk_schedule.schedule_title,yk_interview.interviewtime,yk_interview.status')
                         ->join('yk_schedule','yk_interview.schedule_id=yk_schedule.schedule_id','LEFT')
                         ->join('yk_user','yk_user.user_id=yk_interview.user_id','LEFT')
                         ->where("yk_interview.to_user_id=$user_id AND yk_interview.inter_id>$id")
                         ->order('yk_interview.interviewtime','DESC')
                         ->limit(20)
                         ->select();
                if ($res){
                    return Json(self::status(1,$res));
                }else{
                    return Json(self::status(0));
                }
                break;
            case 'put': // put请求处理代码
                //同意或拒绝面试邀请
                $user_id = Request::instance()->put('user_id');
                $token = Request::instance()->put('token');
//                self::tokenAudit($user_id,$token);
                $schedule_id = Request::instance()->put('id');
                $inter_id = Request::instance()->put('inter_id');
                $up['status'] = Request::instance()->put('status');
                $res = Db::table('yk_interview')
                    ->where("to_user_id=$user_id AND schedule_id=$schedule_id AND inter_id=$inter_id")
                    ->update($up);
                $up1['schedule_status'] = $up['status'];
                $res1 = Db::table('yk_user_schedule')
                    ->where("user_id=$user_id AND schedule_id=$schedule_id")
                    ->update($up1);
                if ($res1){
                    if ($res){
                      return Json(self::status(1));
                    }else{
                      return Json(self::status(25));
                    }
                }else{
                    return Json(self::status(26));
                }
                break;
            case 'post': // post请求处理代码
                //交换手机号
                $user_id = Request::instance()->post('user_id');
                $token = Request::instance()->post('token');
                self::tokenAudit($user_id,$token);
                $id = Request::instance()->post('id');
                $res = Db::table('yk_user')->field('user_img as iconURL,nickname as name,mobile')
                        ->where("user_id = $user_id || user_id= $id")
                        ->select();
                $msg = '📱 '.$res[0]['name'].':'.$res[0]['mobile'];
                $msg1 = '📱 '.$res[1]['name'].':'.$res[1]['mobile'];
                $send = $res[0];
                $send1 = $res[1];
                $send['status'] = 3;
                $send1['status'] = 3;
                $send['msg_type'] = 2;
                $send1['msg_type'] = 2;
                $hx = new Hx($this->options);
                $this->img_url('iconURL',$send);
                $this->img_url('iconURL',$send1);
                $result = $hx->sendText($user_id,'users',array($id),$msg1,$send1);
                $result1 = $hx->sendText($id,'users',array($user_id),$msg,$send);
                if ($result&&$result1){
                    return Json(self::status(1));
                }else{
                    return Json(self::status(0));
                }
                break;
            case 'delete': // delete请求处理代码
                return Json(self::status(0));
                break;
            default:
                return Json(self::status(0));
                break;
        }
    }
    function boss(){
        switch ($this->method){
            case 'get': // get请求处理代码
                echo md5('yanku321');
//                return Json(self::status(1));
                break;
            case 'put': // put请求处理代码
                break;
            case 'post': // post请求处理代码
                break;
            case 'delete': // delete请求处理代码
                return Json(self::status(0));
                break;
            default:
                return Json(self::status(0));
                break;
        }
    }
    function demo(){
        switch ($this->method){
            case 'get': // get请求处理代码
                echo md5('yanku321');
//                return Json(self::status(1));
                break;
            case 'put': // put请求处理代码
                break;
            case 'post': // post请求处理代码
                break;
            case 'delete': // delete请求处理代码
                return Json(self::status(0));
                break;
            default:
                return Json(self::status(0));
                break;
        }
    }
}