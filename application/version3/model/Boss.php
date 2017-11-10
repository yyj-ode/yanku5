<?php
/**
 * Created by PhpStorm.
 * User: wangfuruo
 * Date: 2017/8/22
 * Time: 下午4:13
 */
namespace app\version3\model;

use think\Db;
use think\Model;
use app\version3\controller\Basic;

class Boss extends Model
{
    protected $pk = 'user_id';

    public function getBossData($user_id){
        $data =  Db::query('
            SELECT b.*,u.nickname,u.level,u.user_img,u.city,c.city_name user_city,u.sex,a1.attention_num,a2.fans_num
            FROM yk_user u 
            LEFT JOIN yk_boss b ON u.user_id = b.user_id
            LEFT JOIN yk_user_city c on u.city = c.city_id
            LEFT JOIN (
                SELECT user_id,count(*) attention_num
                FROM yk_attention
                WHERE user_id = ?
            ) a1 ON u.user_id = a1.user_id 
            LEFT JOIN (
                SELECT attu_id,count(*) fans_num
                FROM yk_attention
                WHERE attu_id = ?
            ) a2 ON u.user_id = a2.attu_id 
            WHERE u.user_id = ?
            LIMIT 1 ',[$user_id,$user_id,$user_id]);
//        var_dump($data);die;
        $basicModel = new Basic();
        $data = $basicModel->img_urls('user_img',$data);
        if($data){
//            $sex = [0 => '保密', 1 => '男', 2 => '女',];
//            $data[0]['sex'] = $sex[$data[0]['sex']];
            if(!$data[0]['user_city']){
                $data[0]['user_city'] = '';
            }
            if(!$data[0]['attention_num']){
                $data[0]['attention_num'] = 0;
            }
            if(!$data[0]['fans_num']){
                $data[0]['fans_num'] = 0;
            }
            foreach($data[0] as $k => $v){
                if($v == null){
                    $data[0][$k] = '';
                }
            }
            $data[0]['user_id'] = $user_id;
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
            ->join("yk_schedule s","i.schedule_id = s.schedule_id ")
            ->where("i.user_id",'=',$user_id)
//            ->where("i.status",'>',0)
            ->order("i.interviewtime asc")
            ->limit($start,10)
            ->select();
        $basicModel = new Basic();
//        把结果集对象中的图片字段拼接成可访问的url字符串
        $data = $basicModel->obj_imgstr_url($data,'user_img');
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
            ->join("yk_schedule s","i.schedule_id = s.schedule_id ")
            ->where("i.to_user_id",'=',$user_id)
            ->order("i.interviewtime asc")
            ->limit($start,10)
            ->select();
        $basicModel = new Basic();
//        把结果集对象中的图片字段拼接成可访问的url字符串
        $data = $basicModel->obj_imgstr_url($data,'user_img');
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
        $data = Db::table('yk_schedule')
            ->field("schedule_title,schedule_img,schedule_id,status, FROM_UNIXTIME(acttime, '%m/%d') acttime,schedule_type")
            ->where(" yk_schedule.user_id = $search_id")
            ->where("status",'>',0)
            ->where("status",'<',3)
            ->order(" yk_schedule.schedule_id desc")
            ->limit($start,10)
            ->select();
        $basicModel = new Basic();
//        把结果集对象中的图片字段拼接成可访问的url字符串
        $data = $basicModel->obj_imgstr_url($data,'schedule_img');
        return $data;
    }

    public function getWorkDataList($search_id){
        $data = Db::table("yk_work w")
            ->field("w.work_title,w.introduce,FROM_UNIXTIME(w.start_time,'%Y-%m-%d') start_time,GROUP_CONCAT(wi.work_img) work_img,w.work_id")
            ->join(" yk_work_img wi","w.work_id = wi.work_id","LEFT")
            ->where("w.user_id",'=',$search_id)
            ->group("w.work_id")
            ->order("w.start_time DESC")
            ->select();
        $basicModel = new Basic();
//        把结果集对象中用逗号拼接的图片字段拼接成可访问的url数组
        $data = $basicModel->obj_imgstr_urls($data,'work_img');
        return $data;
    }

    public function getInterviewDetail($inter_id,$identity){
        if($identity == 0){
            $data = Db::table("yk_interview i")
                ->field("i.mark,s.schedule_id,s.schedule_title,i.address,i.status,u.user_img,u.nickname,FROM_UNIXTIME(interviewtime,'%Y-%m-%d %H:%i') interview_time")
                ->join(" yk_user u","i.user_id = u.user_id","LEFT")
                ->join(" yk_schedule s","i.schedule_id = s.schedule_id","LEFT")
                ->where("i.inter_id",'=',$inter_id)
                ->find();
        }else{
            $data = Db::table("yk_interview i")
                ->field("i.mark,s.schedule_title,i.address,i.status,u.user_img,u.nickname,FROM_UNIXTIME(interviewtime,'%Y-%m-%d %H:%i') interview_time")
                ->join(" yk_user u","i.to_user_id = u.user_id","LEFT")
                ->join(" yk_schedule s","i.schedule_id = s.schedule_id","LEFT")
                ->where("i.inter_id",'=',$inter_id)
                ->find();
        }
        return $data;
    }
}