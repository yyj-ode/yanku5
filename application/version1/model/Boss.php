<?php
/**
 * Created by PhpStorm.
 * User: wangfuruo
 * Date: 2017/8/22
 * Time: 下午4:13
 */
namespace app\version1\model;

use think\Db;
use think\Model;

class Boss extends Model
{
    protected $pk = 'user_id';

    public function getBossData($user_id){
        $data =  Db::query('
            SELECT b.*,u.nickname,u.level,u.user_img,u.city,c.city_name user_city,u.sex,a1.attention_num,a2.fans_num
            FROM yk_boss b
            LEFT JOIN yk_user u ON b.user_id = u.user_id
            LEFT JOIN yk_city c on u.city = c.city_id
            LEFT JOIN (
                SELECT user_id,count(*) attention_num
                FROM yk_attention
                WHERE user_id = ?
            ) a1 ON b.user_id = a1.user_id 
            LEFT JOIN (
                SELECT attu_id,count(*) fans_num
                FROM yk_attention
                WHERE attu_id = ?
            ) a2 ON b.user_id = a2.attu_id 
            WHERE b.user_id = ?
            LIMIT 1 ',[$user_id,$user_id,$user_id]);
        if($data){
            $sex = [0 => '保密', 1 => '男', 2 => '女',];
            $data[0]['sex'] = $sex[$data[0]['sex']];
            if(!$data[0]['user_city']){
                $data[0]['user_city'] = '';
            }
            if(!$data[0]['attention_num']){
                $data[0]['attention_num'] = 0;
            }
            if(!$data[0]['fans_num']){
                $data[0]['fans_num'] = 0;
            }
            return $data[0];
        }
        return $data;

    }

    /**
     * @param $user_id
     * @param $start
     * @return false|\PDOStatement|string|\think\Collection
     * boss面试列表
     */
    public function getInterviewData($user_id,$start){
        $data = Db::table("yk_interview i")
            ->field("i.to_user_id user_id,i.schedule_id,u.nickname,u.sex,s.schedule_title,u.user_img,FROM_UNIXTIME(i.interviewtime, '%m-%d') date, i.status,i.inter_id")
            ->join("yk_user u","i.to_user_id = u.user_id","LEFT")
            ->join("yk_schedule s","i.schedule_id = s.schedule_id ","LEFT")
            ->where("i.user_id",'=',$user_id)
            ->limit($start,10)
            ->select();
//        $data =  Db::query('
//            SELECT i.* ,u.nickname,u.sex,u.realname,s.schedule_title,u.user_img,FROM_UNIXTIME(i.interviewtime, \'%m-%d\') date, us.schedule_status
//            FROM yk_interview i
//            LEFT JOIN yk_user u ON i.to_user_id = u.user_id
//            LEFT JOIN yk_schedule s ON i.schedule_id = s.schedule_id
//            LEFT JOIN yk_user_schedule us ON i.to_user_id = us.user_id AND i.schedule_id = us.schedule_id
//            WHERE i.user_id = ?
//            LIMIT ?,10',[$user_id,$start]);
//        echo '<pre>';print_r($data);die;
//        if($data){
//            $schedule_status = [0=>'报名',1=>'拒绝',2=>'待面试',3=>'已结束',];
//            foreach($data as $k =>&$v){
//                $v['schedule_status'] = $schedule_status[$v['schedule_status']];
//            }
//        }
        return $data;
    }

    /**
     * @param $user_id
     * @param $start
     * @return false|\PDOStatement|string|\think\Collection
     * 艺人面试列表
     */
    public function getArtistInterviewData($user_id,$start){
        $data = Db::table("yk_interview i")
            ->field("i.user_id,i.schedule_id,u.nickname,u.sex,s.schedule_title,u.user_img,FROM_UNIXTIME(i.interviewtime, '%m-%d') date, i.status,i.inter_id")
            ->join("yk_user u","i.user_id = u.user_id","LEFT")
            ->join("yk_schedule s","i.schedule_id = s.schedule_id ","LEFT")
            ->where("i.to_user_id",'=',$user_id)
            ->limit($start,10)
            ->select();
        return $data;
    }

    /**
     * @param $start
     * @return mixed
     * boss端首页
     */
    public function getIndexData($start){
        $data['list'] =  Db::query('
            SELECT yk_user.user_id,group_concat(yk_user_type.type) type,yk_user.nickname,yk_user.user_img,yk_user.nameaudit
            FROM yk_user LEFT JOIN yk_user_type ON yk_user.user_id=yk_user_type.user_id
            WHERE yk_user_type.type!=0 AND yk_user.nameaudit=2
            GROUP BY yk_user.user_id
            ORDER BY yk_user.user_id DESC 
            LIMIT ?,20
        ',[$start]);

        $data['banner'] = Db::query('SELECT * FROM yk_activity');

        if ($data['banner']==null){
            $data['banner']=array();
        }elseif ($data['list']==null){
            $data['list']=array();
        }
        return $data;

    }

    public function getBossHomeSchedule($search_id,$start){
//        $data =   Db::query("
//            SELECT schedule_title,schedule_img,schedule_id,status, FROM_UNIXTIME(acttime, '%m/%d') act_time,schedule_type
//            FROM yk_schedule
//            WHERE yk_schedule.user_id = ?
//            ORDER BY  yk_schedule.schedule_id DESC
//            LIMIT ?,10
//        ",[$search_id,$start]);
        $data = Db::table('yk_schedule')
            ->field("schedule_title,schedule_img,schedule_id,status, FROM_UNIXTIME(acttime, '%m/%d') act_time,schedule_type")
            ->where(" yk_schedule.user_id = $search_id")
            ->order(" yk_schedule.schedule_id desc")
            ->limit($start,10)
            ->select();
//        $schedule_type = [
//            0 => '影视通告',
//            1 => '模特招募',
//            2 => '群众演员',
//            3 => '主播',
//            4 =>  '主持',
//            5 => '歌手',
//        ];
//        $change_status = [
//            0=>'审核中',
//            1=>'招募中',
//            2=>'已截止',
//            3=>'未通过'
//        ];
//        foreach($data as $k => $v){
//            $data[$k]['schedule_type'] = $schedule_type[$v['schedule_type']];
//            $data[$k]['status'] = $change_status[$v['status']];
//        }
        return $data;
    }

    public function getWorkDataList($search_id){
        $data = Db::table("yk_work w")
            ->field("w.work_title,w.introduce,FROM_UNIXTIME(w.start_time,'%Y-%m-%d') start_time,GROUP_CONCAT(wi.work_img) imgs")
            ->join(" yk_work_img wi","w.work_id = wi.work_id","LEFT")
            ->where("w.user_id",'=',$search_id)
            ->group("w.work_id")
            ->order("w.start_time DESC")
            ->select();
//        var_dump($data);die;

//        $data = Db::query("
//        SELECT w.work_title,w.introduce,FROM_UNIXTIME(w.start_time,'%Y-%m-%d') start_time,GROUP_CONCAT(wi.work_img) imgs
//        FROM yk_work w
//        LEFT JOIN yk_work_img wi ON w.work_id = wi.work_id
//        WHERE w.user_id = ?
//        GROUP BY w.work_id
//        ORDER BY w.start_time DESC
//        ",[$search_id]);
//        var_dump($data);die;

        return $data;
    }

    public function getArtistInterviewDetail($inter_id){
        $data = Db::table("yk_interview i")
            ->field("s.schedule_title,i.address,i.status,u.user_img,u.nickname,FROM_UNIXTIME(interviewtime,'%Y-%m-%d %H:%i') interview_time")
            ->join(" yk_user u","i.user_id = u.user_id","LEFT")
            ->join(" yk_schedule s","i.schedule_id = s.schedule_id","LEFT")
            ->where("i.inter_id",'=',$inter_id)
            ->find();
        return $data;
    }
}