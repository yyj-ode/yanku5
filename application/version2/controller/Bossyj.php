<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/7 0007
 * Time: 09:47
 */
namespace app\version2\controller;
use app\version2\model\Boss;
use app\version2\model\City;
use app\version2\model\UserCity;
use app\version2\model\Attention;
use app\version2\model\Schedule;
use app\version2\model\User;
use app\version2\model\Boss as boss_model;
use app\version2\model\userSchedule;
use think\console\command\make\Controller;
use think\Db;
use think\Request;
use think\response\Json;
class Bossyj extends Basic {
    private $options = array('client_id'=>'YXA6ryLaYB6qEee9ag-MCEtXOA',
        'client_secret'=>'YXA6P275ejRYIcHpCHj_eqtonJUmJb4',
        'org_name'=>'1134170411178481',
        'app_name'=>'yanku');
    function personalCenter(){
        switch ($this->method){
            case 'get': // get请求处理代码
//                echo 1;die;
                $user_id = Request::instance()->get('user_id');
                $token = Request::instance()->get('token');
                self::tokenAudit($user_id,$token);
                $bossModel = new boss_model();
                $data = $bossModel->getBossData($user_id);
//                echo '<pre>';print_r($data);die;
                $redis = $this->redisConect(6);
                $sign = $redis->get($user_id);
//                var_dump($sign);die;
                $time = date('Y:m:d',time());
                if($sign['time'] == $time){
                    $data['showsignin'] = 0;
                }else{
                    $data['showsignin'] = 1;
                }
                $data['day'] = $sign['day'];
                if($data['day'] == null){
                    $data['day'] = 0;
                }
                return Json(self::status(1,$data));
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

    function interview(){
        switch ($this->method){
            case 'get': // get请求处理代码
//                echo 1;die;
                $user_id = Request::instance()->get('user_id');
                $token = Request::instance()->get('token');
                $start = Request::instance()->get('start');
                self::tokenAudit($user_id,$token);
                $bossModel = new boss_model();
                $data = $bossModel->getInterviewData($user_id,$start);

//                echo '<pre>';print_r($data);die;
                return Json(self::status(1,$data));
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

    function artistInterview(){
        switch ($this->method){
            case 'get': // get请求处理代码
//                echo 1;die;
                $user_id = Request::instance()->get('user_id');
                $token = Request::instance()->get('token');
                $start = Request::instance()->get('start');
                self::tokenAudit($user_id,$token);
                $bossModel = new boss_model();
                $data = $bossModel->getartistInterviewData($user_id,$start);

//                echo '<pre>';print_r($data);die;
                return Json(self::status(1,$data));
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

    function bossHomeHeader(){
        switch ($this->method){
            case 'get': // get请求处理代码
//                echo 1;die;
                $user_id = Request::instance()->get('user_id');
                $token = Request::instance()->get('token');
                $search_id = Request::instance()->get('search_id');
                self::tokenAudit($user_id,$token);
                $userModel = new User();
                $data = Db::table('yk_user u')
                    ->field('u.user_id,u.nickname,u.user_img,u.sex,u.level,u.hao_level')
                    ->where('u.user_id',$search_id)
                    ->find();
                $if_exist = Db::table('yk_attention a')
                    ->where('a.user_id',$user_id)
                    ->where('a.attu_id',$search_id)
                    ->find();
                if($if_exist == null){
                    $data['attu'] = 0;
                }else{
                    $data['attu'] = 1;
                }
                $basicModel = new Basic();
                $data = $basicModel->img_url('user_img',$data);
                return Json(self::status(1,$data));
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

    function bossHomeSchedule(){
        switch ($this->method){
            case 'get': // get请求处理代码
        //                echo 1;die;
                $user_id = Request::instance()->get('user_id');
                $token = Request::instance()->get('token');
                $search_id = Request::instance()->get('search_id');
                $start = Request::instance()->get('start');
                self::tokenAudit($user_id,$token);
                $bossModel = new boss_model();
                $data = $bossModel->getBossHomeSchedule($search_id,$start);
        //        echo '<pre>';print_r($data);die;
                return Json(self::status(1,$data));
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

    function bossHomeOrganization(){
        switch ($this->method){
            case 'get': // get请求处理代码
                //                echo 1;die;
                $user_id = Request::instance()->get('user_id');
                $token = Request::instance()->get('token');
                $search_id = Request::instance()->get('search_id');
                self::tokenAudit($user_id,$token);
                $bossModel = new boss_model();
                $data = $bossModel
                    ->field('organization,organizatioSummary,organizationAddress')
                    ->where('user_id',$search_id)
                    ->where('status',2)
                    ->find();
                if($data==null){
                    $data = (object)[];
                    return Json(self::status(3,$data));
                }
                return Json(self::status(1,$data));
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

    function personalUpdate(){
        switch ($this->method){
            case 'get': // get请求处理代码
                break;
            case 'put': // put请求处理代码
                $user_id = Request::instance()->put('user_id');
                $token = Request::instance()->put('token');
                self::tokenAudit($user_id,$token);
                $userData['nickname'] = Request::instance()->put('nickname');
                $userData['sex'] = Request::instance()->put('sex');
                $userWhere['user_id'] = $user_id;
                $cityData['city_name'] = Request::instance()->put('user_city');
                $cityData['city_name'] = str_replace('市市','市',$cityData['city_name']);
//                echo $cityData['city_name'];die;
                $bossData['organization'] = Request::instance()->put('organization');
                $bossData['organizatioSummary'] = Request::instance()->put('organizatioSummary');
                $bossData['organizationAddress'] = Request::instance()->put('organizationAddress');
                $bossWhere['user_id'] = $user_id;

                $bossModel = new boss_model();
                $userModel = new User();
                $cityModel = new UserCity();
                $city_id =  $cityModel->where('city_name',$cityData['city_name'])->value('city_id');
                if(!$city_id){
                    $cityModel->insert($cityData);
                    $userData['city'] =  $cityModel->where('city_name',$cityData['city_name'])->value('city_id');
                }else{
                    $userData['city'] = $city_id;
                }
//                判断该昵称是否存在
                $data_nickname = Db::table('yk_user u')
                    ->where('u.nickname','=',$userData['nickname'])
                    ->where('u.user_id','<>',$user_id)
                    ->find();
                $if_exist_name = (bool)$data_nickname;
                if( $if_exist_name){
                    return Json(self::status(27));//返回“该昵称已存在”
                }

//                判断该boss是否通过机构认证.如果没通过机构认证，提示该boss未通过机构认证，所以机构信息修改无效
                $data_boss_exist = Db::table('yk_boss b')
                    ->where('b.user_id','=',$user_id)
                    ->where('b.status','=',2)
                    ->find();
                $if_boss_exists = (bool)$data_boss_exist;
                if(!$if_boss_exists){
                    return Json(self::status(28));//提示无该boss信息，修改无效
                }

//                判断是否未修改任何数据
                $data_user = Db::table('yk_user u')
                ->where('u.user_id',$user_id)
                ->where('u.sex', $userData['sex'])
                ->where('u.city',$userData['city'])
                ->where('u.nickname',$userData['nickname'])
                ->find();
                $data_boss = Db::table('yk_boss b')
                    ->where('b.user_id',$user_id)
                    ->where('b.organization', $bossData['organization'])
                    ->where('b.organizatioSummary',$bossData['organizatioSummary'])
                    ->where('b.organizationAddress',$bossData['organizationAddress'])
                    ->find();
                $if_change_user = (bool)$data_user;
                $if_change_boss = (bool)$data_boss;
                if($if_change_user && $if_change_boss){
                    return Json(self::status(29));//返回“并未修改任何数据”
                }

                $res1 = $userModel->save($userData,$userWhere);
                $res2 = $bossModel->save($bossData,$bossWhere);
//                echo 1;die;

                if ($res1 || $res2){
                    return Json(self::status(1));
                }else{
                    return Json(self::status(0));
                }
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

    function experience(){
        switch ($this->method){
            case 'get': // get请求处理代码
                $user_id = Request::instance()->get('user_id');
                $token = Request::instance()->get('token');
                $search_id = Request::instance()->get('search_id');
                $bossModel = new Boss();
                $data = $bossModel->getWorkDataList($search_id);
//                echo '<pre>';print_r($data);die;
//                $result =  Json(self::status(1,$data));
//                var_dump($result);
                return  Json(self::status(1,$data));
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

    function interviewDetail(){
        switch ($this->method){
            case 'get': // get请求处理代码
                $user_id = Request::instance()->get('user_id');
                $token = Request::instance()->get('token');
                $inter_id = Request::instance()->get('inter_id');
                $identity = Request::instance()->get('identity');
                $bossModel = new Boss();
                $data = $bossModel->getInterviewDetail($inter_id,$identity);
                $data = $this->img_url('user_img',$data);
//                echo '<pre>';print_r($data);die;
                return  Json(self::status(1,$data));
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