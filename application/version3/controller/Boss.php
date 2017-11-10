<?php
/**
 * Created by PhpStorm.
 * User: wangfuruo
 * Date: 2017/8/17
 * Time: 下午5:48
 */
namespace app\version3\controller;
use app\version3\model\City;
use app\version3\model\Material;
use app\version3\model\Schedule;
use app\version3\model\User;
use app\version3\model\Boss as boss_model;
use app\version3\model\userSchedule;
use think\Db;
use think\Request;
use think\response\Json;


class Boss extends Basic {
    private $options = array('client_id'=>'YXA6ryLaYB6qEee9ag-MCEtXOA',
        'client_secret'=>'YXA6P275ejRYIcHpCHj_eqtonJUmJb4',
        'org_name'=>'1134170411178481',
        'app_name'=>'yanku');
    function bossAuth(){
        switch ($this->method){
            case 'get': // get请求处理代码，获取认证状态
                $user_id = Request::instance()->get('user_id');
                $token = Request::instance()->get('token');
                self::tokenAudit($user_id,$token);
                $date = array('bossPersion'=>'0','bossOrganization'=>'0');
                $res = Db::table("yk_nameaudit")->field("status")->where("user_id=$user_id")->order("status","ASC")->find();
                null==$res['status']?$date['nameaudit']='0':$date['nameaudit'] = $res['status'];
                $boss = new boss_model();
                $bossRes = $boss->field('organization,status,schedule_img')
                                ->where('user_id',$user_id)
                                ->group("status","ASC")
                                ->find();

                if ($bossRes['organization']==''&&$bossRes['schedule_img']==''){
                }elseif ($bossRes['organization']==''&&$bossRes['schedule_img']!=''){
                    $date['bossPersion'] = $bossRes['status'];
//                    $user = new User();
//                    $userRes = $user->field('yk_user.IDcard_number,yk_user.user_positive,yk_nameaudit.status')
//                                    ->join('yk_nameaudit','yk_user.user_id=yk_nameaudit.user_id')
//                                    ->where('yk_user.user_id',$user_id)
//                                    ->find();
//                    if (''==$userRes['IDcard_number']||3==$userRes['status']){
//                        return Json(self::status(1,$date));
//                    }else{
//                        $date['bossPersion'] = $bossRes['status'];
//                        return Json(self::status(1,$date));
//                    }
                }elseif($bossRes['organization']=!''){
                    $date['bossOrganization'] = $bossRes['status'];
                }else{
                }
                return Json(self::status(1,$date));
                break;
            case 'put': // put请求处理代码
                break;
            case 'post': // post请求处理代码，提交认证
                $user_id = Request::instance()->post('user_id');
                $token = Request::instance()->post('token');
                self::tokenAudit($user_id,$token);
                $bossIns = array();
                $type = Request::instance()->post('type');
                $where['user_id'] = $user_id;
                $user = new User();
                $bossIns['status'] = 1;
                if (0==$type){//个人认证
                    $nameaudit = Db::table("yk_nameaudit")->where("user_id=$user_id AND status=2")->find();
                    $file = self::difUpload();
                    //image[0]剧组认证
                    //image[1]身份手持身份证
                    foreach ($file as $k=>$v){
                        if (0==$k){
                            $file[$k]['path'] = 'bossAuth';
                            $bossIns['schedule_img'] = self::uploadOss($file[$k]);
                            $bossIns['user_id'] = $user_id;
                        }else{
                            $file[$k]['path'] = 'Audit';
                            $ins['user_positive'] = self::uploadOss($file[$k]);
                        }
                    }
                    if (''==$nameaudit){
                        $ins['IDcard_number'] = Request::instance()->post('idcard');
                        $ins['realname'] = Request::instance()->post('realname');
                        $userRes = $user->save($ins,$where);
                    }
                    $boss = new boss_model();
                    $boss->where($where)->delete();
                    $bossRes = $boss->save($bossIns);

                    if ($bossRes){
                        return Json(self::status(1));
                    }else{
                        return Json(self::status(0));
                    }
                }elseif (1==$type){//机构认证
                    $ins['realname'] = Request::instance()->post('realname');
                    $userRes = $user->save($ins,$where);
                    $boss = new boss_model();
                    $res = Db::table('yk_boss')->where("user_id",$user_id)->find();
                    if ($res){
                        $where = array('user_id'=>$user_id);
                    }else{
                        $where = '';
                    }
                    $bossIns['organization'] = Request::instance()->post('organization');
                    $file = self::upload();
                    $file['path'] = 'bossAuth';
                    $bossIns['organizationImg'] = self::uploadOss($file);
                    $bossIns['user_id'] = $user_id;
                    $bossRes = $boss->save($bossIns,$where);
                    //坑1 用&&上传重复实名可能会导致返回0
                    if ($userRes||$bossRes){
                        return Json(self::status(1));
                    }else{
                        return Json(self::status(0));
                    }
                }else{
                    return Json(self::status(0));
                }
                break;
            case 'delete': // delete请求处理代码
                return Json(self::status(0));
                break;
        }
    }
    function schedule(){
        switch ($this->method){
            case 'get': // get请求处理代码，获取已发布通告
                $user_id = Request::instance()->get('user_id');
                $token = Request::instance()->get('token');
                self::tokenAudit($user_id,$token);
                $start = Request::instance()->get('start',0);
                $type = Request::instance()->get('type',5);
                5==$type?$where='':$where="AND yk_schedule.status=$type";
                0==$start?$start='':$start="AND yk_schedule.schedule_id<$start";
                    $schdule = new Schedule();
                $all = $schdule->field('count(user_id)')
                               ->where("user_id=$user_id $where")
                               ->find();
                //0==$schdule?$all = $schdule->field('count(user_id)')->where("user_id=$user_id")->find():'';
                $result['all'] = $all['count(user_id)'];
                $result['schedule'] = $schdule->field('yk_schedule.schedule_id,yk_schedule.schedule_img,yk_schedule.valcode,yk_schedule.createtime,yk_schedule.acttime,yk_schedule.schedule_title,yk_schedule.schedule_type,yk_schedule.status,count(yk_user_schedule.schedule_id) as count')
                                  ->join("yk_user_schedule","yk_user_schedule.schedule_id=yk_schedule.schedule_id","LEFT")
                                  ->where("yk_schedule.user_id=$user_id $start $where") //AND yk_user_schedule.schedule_status=0
                                  ->group('yk_schedule.schedule_id')
                                  ->order("yk_schedule.schedule_id","DESC")
                                  ->limit(10)
                                  ->select();
//                图片拼接字符串
                $result['schedule'] = $this->obj_imgstr_url($result['schedule'],'schedule_img');
//                $result['schedule'] = $this->img_urls('schedule_img',$result['schedule']);
                return Json(self::status(1,$result));
                break;
            case 'put': // put请求处理代码，关闭通告
                $user_id = Request::instance()->put('user_id');
                $token = Request::instance()->put('token');
                self::tokenAudit($user_id,$token);
                $where['schedule_id'] = Request::instance()->put('id');
                $schdule = new Schedule();
                $find = DB::table('yk_schedule')->field('status')->where('schedule_id',$where['schedule_id'])->find();
                 if (0==$find['status']||3==$find['status']){
                    $update['status'] = 3;

                }else{
                    $update['status'] = 2;

                }
                $res = $schdule->save($update,$where);
                if ($res){
                    return Json(self::status(1));
                }else{
                    return Json(self::status(0));
                }
                break;
            case 'post': // post请求处理代码，发布通告
                $user_id = Request::instance()->post('user_id');
                $token = Request::instance()->post('token');
                self::tokenAudit($user_id,$token);
                $ins['schedule_title'] = Request::instance()->post('schedule_title');
                $ins['acttime'] = Request::instance()->post('acttime');
                $ins['address'] = Request::instance()->post('address','');
                $ins['sex'] = Request::instance()->post('sex',0);
                $ins['audit'] = Request::instance()->post('audit',0);
                $ins['schedule_type'] = Request::instance()->post('schedule_type',0);
                $pic = Request::instance()->post('pic',0);
                $ins['user_id'] = $user_id;
                $ins['createtime'] = time();
                $ins['valcode'] = rand(1000,9999);
                $city = Request::instance()->post('city');
                if ($ins['acttime']<time()){
                    return Json(self::status(30));
                    die();
                }
                $file = self::uploads();
                $citymodel = new City();
                $res = $citymodel->where('city_name',$city)->find();
                ''==Request::instance()->post('schedule_content')?$ins['schedule_content']='':$ins['schedule_content']=Request::instance()->post('schedule_content');
                isset($ins['schedule_img'])?$ins['schedule_img']='':'';
                $ins['schedule_img'] = Request::instance()->post('schedule_img','');
                switch ($pic){
                    case 1:
                        $num=1;
                        break;
                    default:
                        $num=0;
                        break;
                }
                if (''!=$res){
                    $ins['city'] = $res['city_id'];
                }else{
                    $insert['city_name'] = $city;
                    $citymodel->save($insert);
                    $ins['city'] = $citymodel->city_id;
                }
                if (''!=$file){
                    foreach ($file as $k=>$v){
                        if ($num==$k){
                            $v['path'] = 'Schedule';
                            $ins['schedule_img'] = self::uploadOss($v);
                        }else{
                            $v['path'] = 'Schedule';
                            $ins['schedule_content'] = '<URL>http://img.yankushidai.com/'.self::uploadOss($v);
                        }
                    }
                }
                $schdule = new Schedule();
                $id = $schdule->save($ins);
                if ($id){
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
    function scheduleEnroll(){
        switch ($this->method){
            case 'get': // get请求处理代码,获取参加通告
                $user_id = Request::instance()->get('user_id');
                $token = Request::instance()->get('token');
                self::tokenAudit($user_id,$token);
                $type = Request::instance()->get('type',0);
                $start = Request::instance()->get('start',0);
                $schedule_id = Request::instance()->get('schedule_id');
//                $res = Db::table('yk_user_schedule')
                $usmodel = new userSchedule();
                $res = $usmodel->field('yk_user_schedule.user_id,yk_user_schedule.schedule_status,yk_user.nickname,yk_user.user_img,yk_user.height,yk_user.weight,yk_user.birthday,yk_user.sex,yk_user_city.city_name,yk_user_schedule.consoletime')
                           ->join('yk_user','yk_user_schedule.user_id=yk_user.user_id','LEFT')
                           ->join('yk_user_city','yk_user.city=yk_user_city.city_id','LEFT')
                           ->where("yk_user_schedule.schedule_id=$schedule_id AND yk_user_schedule.schedule_status=$type AND yk_user_schedule.user_id>$start")
                           ->limit(10)
//                           ->fetchSql(true)
                           ->select();
                if ($res){
//                    拼接图片字符串
                    $res = $this->obj_imgstr_url($res,'user_img');
                    return Json(self::status(1,$res));
                }else{
                    return Json(self::status(0,$res));
                }
                break;
            case 'put': // put请求处理代码,左滑右滑
                $user_id = Request::instance()->put('user_id');
                $token = Request::instance()->put('token');
                self::tokenAudit($user_id,$token);
                $type = Request::instance()->put('type');
                $id = Request::instance()->put('id');
                $schedule_id = Request::instance()->put('schedule_id');
                $consoletime = time();
                $hx = new Hx($this->options);
                $res = Db::table("yk_user_schedule")
                    ->field("yk_schedule.schedule_title")
                    ->join("yk_schedule","yk_user_schedule.schedule_id=yk_schedule.schedule_id","LEFT")
                    ->where(array("yk_user_schedule.schedule_id"=>$schedule_id,"yk_user_schedule.user_id"=>$id))
                    ->find();
                if (1==$type){
                    $update = Db::table('yk_user_schedule')
                                ->where(array("user_id"=>$id,"schedule_id"=>$schedule_id))
                                ->update(array('schedule_status'=>1,'consoletime'=>$consoletime));
                    $ran = rand(1,2);
                    $str = msg($ran,$res,1);
                    $user_id = 'BOS'.$user_id;
                    $result = $hx->sendText($user_id,'users',array($id),$str,(object)array());
                    if ($update){
                        return Json(self::status(1));
                    }else{
                        return Json(self::status(0));
                    }
                }elseif (2 == $type){
                    $update = Db::table('yk_user_schedule')
                        ->where(array("user_id"=>$id,"schedule_id"=>$schedule_id))
                        ->update(array('schedule_status'=>2,'consoletime'=>$consoletime));
                    $ran = rand(1,2);
                    $str = msg($ran,$res,2);
                    $user_id = 'BOS'.$user_id;
                    $result = $hx->sendText($user_id,'users',array($id),$str,(object)array());
                    if ($update){
                        return Json(self::status(1));
                    }else{
                        return Json(self::status(0));
                    }

                }else{
                    return Json(self::status(0));
                }
                break;
            case 'post': // post请求处理代码,发送面试邀请
                $user_id = Request::instance()->post('user_id');
                $token = Request::instance()->post('token');
                self::tokenAudit($user_id,$token);
                $ins['user_id'] = $user_id;
                $ins['to_user_id'] = Request::instance()->post('id');
                $ins['interviewtime'] = Request::instance()->post('interviewtime');
                $ins['address'] = Request::instance()->post('address');
                $ins['mark'] = Request::instance()->post('mark');
                $ins['schedule_id'] = Request::instance()->post('schedule_id');
                $re = Db::table('yk_user_schedule')->field('schedule_status')->where("user_id=$ins[to_user_id] AND schedule_id=$ins[schedule_id]")->find();
                if (NULL==$re){
                    $rank = Db::table('yk_user_schedule')->field("count($ins[schedule_id]) as count")->where("schedule_id=$ins[schedule_id]")->find();
                    $con['ranking'] = $rank['count'];
                    $con['signtime'] = strtotime("now");
                    $con['user_id'] = $ins['to_user_id'];
                    $con['schedule_id'] = $ins['schedule_id'];
                    $insert = Db::table('yk_user_schedule')->insert($con);
                }
                $inter = Db::table('yk_interview')->field('inter_id,status')->where("to_user_id=$ins[to_user_id] AND schedule_id=$ins[schedule_id]")->find();
                if (2==$inter['status']){
                    return Json(self::status(32));
                    die();
                }elseif (null!=$inter['inter_id']&&2!=$inter['status']){
                    $res['before'] = $inter['inter_id'];
                    $inter['user_id'] = $user_id;
                    $inter['to_user_id'] = $ins['to_user_id'];
                    $inter['schedule_id'] = $ins['schedule_id'];
                    Db::table('yk_interview')->delete($inter);
                }else{
                    $res['before'] = '0';
                }
                $res['inter_id'] = Db::table('yk_interview')->insertGetId($ins);
                if ($res){
                    return Json(self::status(1,$res));
//                IOSB那个狗逼不让加的，但是老子已经写了,怎么舍得删
//                $send = $ins;
//                unset($send['user_id']);
//                unset($send['to_user_id']);
//                $send['msg_type'] = 1;
//                $send['status'] = 0;
//                if ($res){
//                    $user = Db::table('yk_user')
//                                 ->field('yk_user.user_img as iconURL,yk_user.nickname as name,yk_schedule.schedule_title,yk_schedule.schedule_id')
//                                 ->join('yk_schedule','yk_user.user_id=yk_schedule.user_id','RIGHT')
//                                 ->where("yk_schedule.schedule_id=$ins[schedule_id]")
//                                 ->find();
//                    $send = array_merge($send,$user);
//                    $send = $this->img_url('iconURL',$send);
//                    $hx = new Hx($this->options);
//                    $result = $hx->sendText($user_id,'users',array($ins['to_user_id']),'',$send);
//                    if ($result){
//                        return Json(self::status(1));
//                    }else{
//                        return Json(self::status(0));
//                    }
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
    //更换身份
    function change(){
        $user_id = Request::instance()->put('user_id');
        $token = Request::instance()->put('token');
        self::tokenAudit($user_id,$token);
        $ident = Request::instance()->put('ident',0);
        $up['user_type'] = $ident;
        $res = Db::table('yk_user')->where('user_id',$user_id)->update($up);
        2==$ident?$data = array('boss_id'=>'bos'.$user_id):$data = array();
        return Json(self::status(1,$data));
//        if ($res){
//            return Json(self::status(1,$data));
//        }else{
//            return Json(self::status(0));
//        }
    }

    //通告图片相关操作
    function getBackground(){
        switch ($this->method){
            case 'get': // 获取通告背景缩略图
                $user_id = Request::instance()->get('user_id');
                $token = Request::instance()->get('token');
//                self::tokenAudit($user_id,$token);
                $materialModel = new Material();
                $data = $materialModel->getSmallBackgound();
                return Json(self::status(1,$data));
                break;
            case 'put': // put请求处理代码
                break;
            case 'post': // 合成通告图，上传至阿里云OSS，返回缩略图url和数据库要存储的字段格式
                $type = Request::instance()->post('type');
                $user_id = Request::instance()->post('user_id');
                $token = Request::instance()->post('token');
                $text = Request::instance()->post('text')?:'';
                $material_id = Request::instance()->post('material_id')?:1;
//                token验证
//                self::tokenAudit($user_id,$token);
                if($type == 1){
                    //生成通告图（将文字写入背景图）
                    $path = miximg($text,$material_id);

                }else{
                    $up = $this->upload();
                    //判断图片上传是否成功
                    if(1){  //图片上传成功
                        imgZip($up['filename']);
                        $path = miximg2($text,$up['filename']);
                    }else{  //图片上传失败
                        return Json(self::status(9));
                        break;
                    }
//                    $path = miximg2($text,$material_id);
//                    return Json(self::status(1,$up['filename']));
                }
                $data['schedule_img'] = $data['savePath'] = 'mix/'.$path[2];
                //返回可以访问的缩略图url  和   缩略图存入数据库的字符串
                $data = $this->img_url('schedule_img',$data);
//                //上传缩略图至阿里云OSS
                $file['filename'] = $path[2];
                $file['path'] = 'mix';
                $res = $this->uploadOss($file);
                if($res){
                    return Json(self::status(1,$data));
                }else{
                    return Json(self::status(0));
                }
                break;
            case 'delete': // delete请求处理代码
                $user_id = Request::instance()->delete('user_id');
                var_dump($user_id);die();
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
//                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
//                var_dump($ip);
                var_dump(long2ip(1508404423));die();
//

//                var_dump(time());
                echo 1;
                $ip = $_SERVER['HTTP_X_REAL_IP'];
                var_dump($ip);
                die();
                var_dump(md5(date('Y-m-d h:i:s')));
                break;
            case 'put': // put请求处理代码
                break;
            case 'post': // post请求处理代码
                break;
            case 'delete': // delete请求处理代码
                $user_id = Request::instance()->delete('user_id');
                var_dump($user_id);die();
                return Json(self::status(0));
                break;
            default:
                return Json(self::status(0));
                break;
        }
    }

}
