<?php
/**
 * Created by PhpStorm.
 * User: wangfuruo
 * Date: 2017/8/27
 * Time: 下午3:41
 */
namespace app\version3\controller;
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
                $res = $this->img_urls("user_img",$res);
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
                self::tokenAudit($user_id,$token);
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

                if ($res){
                  return Json(self::status(1));
                }else{
                  return Json(self::status(25));
                }

                break;
            case 'post': // post请求处理代码
                //交换手机号
                $user_id = Request::instance()->post('user_id');
                $token = Request::instance()->post('token');
                self::tokenAudit($user_id,$token);
                $id = Request::instance()->post('id');
                $res = Db::table('yk_user')->field('user_img as iconURL,nickname as name,mobile')
                        ->where("user_id = $user_id")
                        ->find();
                if (preg_match("/^1[34578]{1}\d{9}$/", $res['mobile'])){
                    $res1 = Db::table('yk_user')->field('user_img as iconURL,nickname as name,mobile')
                        ->where("user_id= $id")
                        ->find();
                    $msg = '📱 '.$res['name'].':'.$res['mobile']; //用户ID(自己的）
                    $msg1 = '📱 '.$res1['name'].':'.$res1['mobile'];//bossID（别人的）
                    $res1['name'] = $res1['name'].'(BOSS)';
                    $res['status'] = 3;
                    $res1['status'] = 3;
                    $res['msg_type'] = 2;
                    $res1['msg_type'] = 2;
                    $id = 'bos'.$id;
                    $hx = new Hx($this->options);
                    $res = $this->img_url('iconURL',$res);
                    $res1 = $this->img_url('iconURL',$res1);
                    $result = $hx->sendText($user_id,'users',array($id),$msg,$res);
                    $result1 = $hx->sendText($id,'users',array($user_id),$msg1,$res1);
                    if ($result&&$result1){
                        return Json(self::status(1));
                    }else{
                        return Json(self::status(0));
                    }
                    break;
                }else{
                    return Json(self::status(34));
                    die();
                }
            case 'delete': // delete请求处理代码
                return Json(self::status(0));
                break;
            default:
                return Json(self::status(0));
                break;
        }
    }
    //联系库主
    function suggest(){
        $user_id = Request::instance()->post('user_id');
        $token = Request::instance()->post('token');
        self::tokenAudit($user_id,$token);
        $ins['suggestion'] = emoji_encode(Request::instance()->post('suggest'));
        $ins['createtime'] = time();
        $ins['user_id'] = $user_id;
        $res = Db::table("yk_suggest")->insert($ins);
        if ($res){
            return Json(self::status(1));
        }else{
            return Json(self::status(0));
        }

    }
    //修改手机号
    function mobile(){
        $user_id = Request::instance()->put('user_id');
        $token = Request::instance()->put('token');
        self::tokenAudit($user_id,$token);
        $mobile = Request::instance()->put('mobile');
        $find = Db::table("yk_user")
                ->field("mobile")
                ->where("mobile",$mobile)
                ->find();
        if (!$find){
            $res = Db::table("yk_user")
                    ->where('user_id',$user_id)
                    ->setField('mobile',$mobile);
            if ($res){
                return Json(self::status(1));
            }else{
                return Json(self::status(0));
            }
        }else{
            return Json(self::status(31));
        }
    }
    function live_list(){
        $user_id = Request::instance()->get('user_id','');
        $token = Request::instance()->get('token');
        self::tokenAudit($user_id,$token);
        $res['video'] = model('video')->field('vid,video_img,title,sort_introduce,push')
                ->where('push','<>',0)
                ->order('push','DESC')
                ->limit(7)
                ->select();
        $res['act'] = model('activity')->field('activity_img,activity_url')
                        ->where('activity_push','=',1)
                        ->select();
        $redis = new \Redis();
        $redis -> pconnect("localhost",6379); //localhost也可以填你服务器的ip
        $redis -> select(2);
        $key = $redis->keys('*');
        $val = $redis->mget($key);
        if ($val!=''){
            $val = array_map('fore',$val);
            $res['live'] = $val;
        }else{
            $res['live']=array();
        }
        $res['video'] = $this->obj_imgstr_url($res['video'],'video_img');
        $res['act'] = $this->obj_imgstr_url($res['act'],'activity_img');
        return Json(array('result'=>'1','message'=>'OK','Version'=>'3.0.0','data'=>$res));
    }
    function video_play(){
        $user_id = Request::instance()->get('user_id','');
        $token = Request::instance()->get('token');
        self::tokenAudit($user_id,$token);
        $vid = Request::instance()->get('vid',0);
        $res = model('video')->field('yk_video.introduce,yk_video.view,title')//group_concat(eid,episode_src,episode_num) as episode
                ->where('yk_video.vid','=',$vid)
                ->find();
        if (''==$res) {
            return Json(self::status(0));
        }
        Db::table('yk_video')->where('vid', $vid)->setInc('view');
        $res['episode'] = model('videoEpisode')->field('eid,episode_src,episode_num')
                ->where('vid','=',$vid)
                ->select();
        $res['join'] = model('VideoJoin')
                        ->field('yk_user.user_id,yk_user.user_img,yk_user.nickname')
                        ->join('yk_user','yk_video_join.uid=yk_user.user_id','LEFT')
                        ->where('yk_video_join.vid','=',$vid)
                        ->select();
        $this->obj_imgstr_url($res['join'],'user_img');
        $this->obj_imgstr_url($res['episode'],'episode_src');
        return Json(self::status(1,$res));
    }
    function videoComment(){
        switch ($this->method){
            case 'get': // get请求处理代码
                $user_id = Request::instance()->get('user_id','');
                $token = Request::instance()->get('token');
                self::tokenAudit($user_id,$token);
                $ser['eid'] = Request::instance()->get('eid',0);
                $cid = Request::instance()->get('cid',0);
                0==$cid?$cid=9999999999999999:'';
                $res = model('VideoComment')->field('yk_video_comment.cid,yk_video_comment.content,yk_video_comment.zan,yk_video_comment.cai,yk_video_comment.createtime,yk_user.user_id,yk_user.user_img,yk_user.nickname,yk_user.sex')
                        ->join('yk_user','yk_video_comment.user_id=yk_user.user_id','LEFT')
                        ->where('yk_video_comment.cid','<',$cid)
                        ->where($ser)
                        ->order('createtime','DESC')
                        ->limit(20)
                        ->select();
                $redis = new \Redis();
                $redis -> pconnect("localhost",6379); //localhost也可以填你服务器的ip
                $redis -> select(5);
                $zan = $redis->sInter("zan$user_id");
                $cai = $redis->sInter("cai$user_id");
                $res = $this->obj_imgstr_url($res,'user_img');
                for ($i=0;$i<count($res);$i++){
                    $res[$i]['mine'] = 0;
                    if (in_array($res[$i]['cid'],$zan)){
                        $res[$i]['mine'] = 1;
                    }elseif (in_array($res[$i]['cid'],$cai)){
                        $res[$i]['mine'] = 2;
                    }
                }
                return Json(self::status(1,$res));
                break;
            case 'put': // put请求处理代码
                $user_id = Request::instance()->put('user_id','');
                $token = Request::instance()->put('token');
                self::tokenAudit($user_id,$token);
                $type = Request::instance()->put('type',1);
                $cid = Request::instance()->put('cid',0);
                1 == $type?$type1='zan':$type1='cai';
                $up = Db::table('yk_video_comment')->where('cid', $cid)->setInc($type1);
                $where['user_id'] = $user_id;
                $where['cid'] = (int)$cid;
                $redis = new \Redis();
                $redis -> pconnect("localhost",6379); //localhost也可以填你服务器的ip
                $redis -> select(5);
                $res = $redis -> sAdd("$type1$user_id",$cid);
                if ($res){
                    return Json(self::status(1));
                }else{
                    return Json(self::status(0));
                }
                break;
            case 'post': // post请求处理代码
                $user_id = Request::instance()->post('user_id','');
                $token = Request::instance()->post('token');
                self::tokenAudit($user_id,$token);
                $ins['eid'] = Request::instance()->post('eid');
                $ins['content'] = Request::instance()->post('content');
                $ins['createtime'] = time();
                $ins['user_id'] = $user_id;
                $res['cid'] = Db::table('yk_video_comment')->insertGetId($ins);
                if ($res){
                    return Json(self::status(1,$res));
                }else{
                    return Json(self::status(0));
                }
                break;
            default:
                return Json(self::status(0));
                break;
        }
    }
    function personalPage(){
        switch ($this->method){
            case 'get': // get请求处理代码
                $user_id = Request::instance()->get('user_id','');
                $token = Request::instance()->get('token');
                self::tokenAudit($user_id,$token);
                $search_id = (int)Request::instance()->get('search_id',0);
                if (0==$search_id||1!=is_integer($search_id)){
                    return Json(self::status(11));
                }
                $res = Db::view('yk_user','user_id,user_img,nickname,sex,level,birthday,height,weight,threedimensional,acquirement')
                        ->view('yk_Introduce_myself','video_src,video_img','yk_user.user_id=yk_Introduce_myself.user_id','INNER')
                        ->view('yk_usercard','usercard_title','yk_user.user_id=yk_usercard.user_id','INNER')
                        ->view('yk_usercard_img','usercard_img','yk_usercard.usercard_id=yk_usercard_img.usercard_id','INNER')
                        ->view('yk_work','work_title,introduce','yk_user.user_id=yk_work.user_id','INNER')
                        ->view('yk_work_img','work_img','yk_work.work_id=yk_work_img.work_id','INNER')
                        ->where("yk_user.user_id",'=',$user_id)
                        ->fetchSql(true)
                        ->select();
                var_dump($res);
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
    function attention(){
        switch ($this->method){
            case 'get': // get请求处理代码
                $user_id = Request::instance()->get('user_id','');
                $token = Request::instance()->get('token');
                self::tokenAudit($user_id,$token);
                $search_id = Request::instance()->get('search_id');
                $start = Request::instance()->get('start',0);
                $type = Request::instance()->get('type',0);
                $mine_type = Request::instance()->get('mine_type',0);
                0==$start?$start=99999999999:'';
                if (1==$type){
                    $attu = 'yk_attention.attu_id';
                    $where['yk_attention.user_id'] = $search_id;
                    $type = 'yk_attention.a_type';
//                    $where['u_type']=$mine_type;
                }else{
                    $attu = 'yk_attention.user_id';
                    $where['yk_attention.attu_id'] = $search_id;
                    $type = 'yk_attention.u_type';
//                    $where['a_type']=$mine_type;
                }
                $res = Db::table('yk_attention')->field("yk_attention.attention_id,yk_user.user_id,yk_user.user_img,yk_user.nickname,yk_user.sex,yk_user.level,yk_user.hao_level,yk_user.signature,$type")
                        ->join('yk_user',"$attu=yk_user.user_id",'LEFT')
                        ->where("yk_attention.attention_id<$start")
                        ->where($where)
                        ->order('attention_id','DESC')
                        ->limit(20)
                        ->select();
                $res = $this->obj_imgstr_url($res,'user_img');
                return Json(self::status(1,$res));
                break;
            case 'post': // post请求处理代码
                $user_id = Request::instance()->post('user_id','');
                $token = Request::instance()->post('token');
                self::tokenAudit($user_id,$token);
                $ins['user_id'] = $user_id;
                $attu_id = Request::instance()->post('attu_id');
                $ins['addtime'] = time();
                $ins['attu_id'] = $attu_id;
                $ins['a_type'] = Request::instance()->post('a_type',0);
                $ins['u_type'] = Request::instance()->post('u_type',0);
                $ins = Db::table('yk_attention')->insert($ins);
                if ($ins){
//                    $rand = rand(0,3);
//                    $msg = $this->attu_msg($rand);
//                    require '/usr/local/apache/htdocs/yanku5/vendor/autoload.php';
//                    $client = new \JPush\Client($this->app_key, $this->master_secret);
//                    try {
//                        $client->push()
//                            ->setPlatform('all')
//                            ->addAlias((string)$attu_id)
//                            ->options(array('apns_production'=>true))
//                            ->setNotificationAlert('关注通知')
//                            ->iosNotification(array(
//                                'title' => '关注通知',
//                                'body' => $msg,
//                                'sound' => 'sound.caf',
//                                'badge' => '+1',
//                                // 'content-available' => true,
//                                // 'mutable-content' => true,
//                                'category' => 'jiguang',
//                                'extras' => array(
//                                    'type' => '2',
//                                    'key' => '',
//                                ),
//                            ))
//                            ->androidNotification(array(
//                                'title' => '关注通知',
//                                'body' => $msg,
//                                // 'build_id' => 2,
//                                'extras' => array(
//                                    'type' => '2',
//                                    'key' => '',
//                                ),
//                            ))
//                            ->send();
                        return Json(self::status(1));
//                    } catch (\JPush\Exceptions\APIConnectionException $e) {
//                        // try something here
//                        //                print $e;
//                        return Json(self::status(20));
//                    } catch (\JPush\Exceptions\APIRequestException $e) {
//                        // try something here
//                        //                print $e;
//                        return Json(self::status(20));
//                    }
                }else{
                    return Json(self::status(0));
                }
                break;
            case 'delete': // delete请求处理代码
                $user_id = Request::instance()->delete('user_id','');
                $token = Request::instance()->delete('token');
                self::tokenAudit($user_id,$token);
                $where['attu_id'] = Request::instance()->delete('attu_id');
//                $where['a_type'] = Request::instance()->delete('a_type',0);
//                $where['u_type'] = Request::instance()->delete('u_type',0);
                $where['user_id'] = $user_id;
                $del = Db::table('yk_attention')->where($where)->delete();
                if ($del){
                    return Json(self::status(1));
                }else{
                    return Json(self::status(0));
                }
                break;
            default:
                return Json(self::status(0));
                break;
        }
    }
    function live(){
        switch ($this->method){
            case 'get': // get请求处理代码
                $user_id = Request::instance()->get('user_id','');
                $token = Request::instance()->get('token');
                self::tokenAudit($user_id,$token);
                $res['video'] = model('video')->field('vid,video_img,title,sort_introduce,push')
                    ->where('push','<>',0)
                    ->order('push','DESC')
                    ->limit(7)
                    ->select();
                $res['act'] = model('activity')->field('activity_img,activity_url')
                    ->where('activity_push','=',1)
                    ->select();
                $redis = new \Redis();
                $redis -> pconnect("localhost",6379); //localhost也可以填你服务器的ip
                $redis -> select(2);
                $key = $redis->keys('*');
                $val = $redis->mget($key);
                if ($val!=''){
                    $val = array_map('fore',$val);
                    $res['live'] = $val;
                }else{
                    $res['live']=array();
                }
                $res['video'] = $this->obj_imgstr_url($res['video'],'video_img');
                $res['act'] = $this->obj_imgstr_url($res['act'],'activity_img');
                return Json(array('result'=>'1','message'=>'OK','Version'=>'3.0.0','data'=>$res));
                break;
            case 'put': // put请求处理代码
                break;
            case 'post': // post请求处理代码
                break;
            case 'delete': // delete请求处理代码
                break;
            default:
                break;
        }
    }
    function demo(){
        switch ($this->method){
            case 'get': // get请求处理代码
                break;
            case 'put': // put请求处理代码
                break;
            case 'post': // post请求处理代码
                break;
            case 'delete': // delete请求处理代码
                break;
            default:
                break;
        }
    }
}
