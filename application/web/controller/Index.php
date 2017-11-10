<?php
/**
 * Created by PhpStorm.
 * User: wangfuruo
 * Date: 2017/11/8
 * Time: 上午10:48
 */
namespace app\web\controller;

use think\controller\Rest;
use think\Db;
use think\Request;
use app\web\model;
use think\response\Json;
use think\Session;

header('Access-Control-Allow-Origin:*');
header ( "X-FRAME-OPTIONS:DENY");

class index extends Basic {
    function index(){
        $model = new model\Schedule();
        $show = $model->getScheduleinfo('723');
        $show['info']['schedule_type'] = $this->schedule_type($show['info']['schedule_type']);
        $show['info']['sex'] = $this->sex_type($show['info']['sex']);
        $show['info'] = $this->img_url('schedule_img',$show['info']);
        $show['role'] = $this->obj_imgstr_url($show['role'],'role_img');
        $show['enroll'] = $this->obj_imgstr_url($show['enroll'],'user_img');
        return Json(self::status(1,$show));
    }
    function login(){
        $mobile = Request::instance()->post('mobile',0);
        $mobile = intval($mobile);
        $valcode = Request::instance()->post('valcode',0);
        if (preg_match("/^1[34578]{1}\d{9}$/", $mobile)){
            $redis = new \Redis();
            $redis -> pconnect("localhost",6379); //localhost也可以填你服务器的ip
            $redis -> select(5);
            $get = $redis->get($mobile);
            if ($valcode==$get){
                $redis -> select(0);
                $res['token'] = md5(time());
                $redis -> set($mobile,$res['token']);
                return Json(self::status(1,$res));
            }else{
                return Json(self::status(39));
            }
        }
    }
    function send_msg(){
        $mobile = Request::instance()->post('mobile',0);
        $mobile = intval($mobile);
        if (preg_match("/^1[34578]{1}\d{9}$/", $mobile)){
            $redis = new \Redis();
            $redis -> pconnect("localhost",6379); //localhost也可以填你服务器的ip
            $redis -> select(5);
            $get = $redis->get($mobile);
            $user = new model\User();
            if (!$get){
                $code = code($mobile);
            }else{
                return Json(self::status(38));
            }
            $res = Db::table('yk_user')
                ->field('user_id')
                ->where('mobile','=',$mobile)
                ->find();
            if (!$res){
                $data['mobile'] = $mobile;
                $data['password_salt'] = $user->getSalt();
                $data['password'] = $user->mkpasswd(substr($mobile,4,6));
                $rand = rand(0,6);
                $data['nickname'] = '手机用户'.substr(md5(time()),$rand,6);
                $ins = Db::table('yk_user')->insert($data);
                if ($ins){
                    $message = "【演库科技】您的验证码是$code"."。您可以通过此手机号登录演库app，您的密码为：$data[password]";
                }else{
                    return Json(self::status(0));
                }
            }else{
                $message = "【演库科技】您的验证码是$code"."。演绎未来的通路";
            }
        }
        $username = "bjykwlkj"; //用户名
        $pwd = "142121"; //密码
        $send = send_sms($username,$pwd,$mobile,$message);
        if ($send){
            return Json(self::status(36));
        }else{
            return Json(self::status(37));
        }
    }
    function user_list(){
        $res = Db::table('yk_user_schedule')->field('yk_user_schedule.user_id,yk_user.nickname,yk_user.mobile,yk_user_schedule.schedule_status,yk_user_schedule_role.role_id')
                ->join('yk_user_schedule_role','yk_user_schedule.schedule_id=yk_user_schedule_role.schedule_id','LEFT')
                ->join('yk_user','yk_user_schedule.user_id=yk_user.user_id','LEFT')
                ->where('yk_user_schedule.schedule_id','=',723)
                ->select();
        return Json(self::status(1,$res));
    }
    function change_user(){
        $role_id = Request::instance()->post('role_id');
        $user_id = Request::instance()->post('user_id');
        $insert['video_src'] = Request::instance()->post('video_src');
        $insert['user_id'] = $ins['user_id'] = $user_id;
        $ins['role_id'] = $role_id;
        $ins['schedule_id'] = 723;
        $ins['createtime'] = time();
        $inse['user_id'] = $user_id;
        $inse['vote'] = 0;
        $res = Db::table('yk_user_schedule_role')->insert($ins);
        $res1 = Db::table('yk_user_video')->insert($insert);
        $res2 = Db::table('yk_vote')->insert($inse);
        if ($res&&$res1&&$res2){
            return Json(self::status(1));
        }else{
            return Json(self::status(0));
        }
    }
    function vote(){
        $mobile = intval(Request::instance()->post('mobile',0));
        $token = Request::instance()->post('token',0);
        $user_id = intval(Request::instance()->post('user_id',0));
        $redis = new \Redis();
        $redis -> pconnect("localhost",6379); //localhost也可以填你服务器的ip
        $res = $redis -> get($mobile);
        if ($res!=$token){
            return Json(self::status(6));
        }
        $redis -> select(15);
        $res = $redis -> get($mobile);
        if ($res==date('Y:m:d',time())){
            return Json(self::status(40));
        }else{
            $plusOne = Db::table('yk_vote')->where('user_id',$user_id)->setInc('vote');
            if ($plusOne){
                $redis->set($mobile,date('Y:m:d',time()));
                return Json(self::status(1));
            }else{
                return Json(self::status(40));
            }
        }
    }
    function vote_list(){
        $type = Request::instance()->post('type',8);
        $start = Request::instance()->post('start',0);
        
    }
}