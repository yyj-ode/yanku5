<?php
/**
 * Created by PhpStorm.
 * User: wangfuruo
 * Date: 2017/11/8
 * Time: 上午11:22
 */
namespace app\web\model;
use think\Db;
use think\Model;

class Schedule extends Model
{
    function getScheduleinfo($id){
        $res['info'] = Db::table('yk_schedule')->field("yk_schedule.schedule_title,yk_schedule.acttime,yk_schedule.schedule_type,yk_schedule.schedule_img,yk_schedule.schedule_content,yk_schedule.sex,yk_city.city_name")
                    ->join("yk_city","yk_schedule.city = yk_city.city_id","LEFT")
                    ->where("yk_schedule.schedule_id",'=',$id)
                    ->find();
        $res['role'] = Db::table('yk_schedule_role')->field('role_id,role_img')//'role_id,role_name,role_information,role_character,role_img'
                    ->where('schedule_id','=',$id)
                    ->select();
        $res['enroll'] = Db::table('yk_user_schedule')->field('yk_user.user_id,yk_user.user_img')
                    ->join('yk_user','yk_user_schedule.user_id=yk_user.user_id','LEFT')
                    ->where('yk_user_schedule.schedule_id','=',$id)
                    ->order('rand()')
                    ->limit(10)
                    ->select();
        return $res;
    }

}