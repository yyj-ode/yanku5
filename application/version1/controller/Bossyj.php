<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/7 0007
 * Time: 09:47
 */
namespace app\version1\controller;
use app\version1\model\Boss;
use app\version1\model\City;
use app\version1\model\Schedule;
use app\version1\model\User;
use app\version1\model\Work;
use app\version1\model\Boss as boss_model;
use app\version1\model\userSchedule;
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
                $data = $userModel->field('user_id,nickname,user_img,realname,sex,level,hao_level')->where('user_id',$search_id)->find();
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
                $data = $bossModel->field('organization,organizatioSummary,organizationAddress')->where('user_id',$search_id)->find();
//                        echo '<pre>';print_r($data);die;
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
                $bossData['organization'] = Request::instance()->put('organization');
                $bossData['organizatioSummary'] = Request::instance()->put('organizatioSummary');
                $bossData['organizationAddress'] = Request::instance()->put('organizationAddress');
                $bossWhere['user_id'] = $user_id;

                $bossModel = new boss_model();
                $userModel = new User();
                $cityModel = new City();
                $city_id =  $cityModel->where('city_name',$cityData['city_name'])->value('city_id');
                if(!$city_id){
                    $cityModel->insert($cityData);
                    $userData['city'] =  $cityModel->where('city_name',$cityData['city_name'])->value('city_id');
                }else{
                    $userData['city'] = $city_id;
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

    function artistInterviewDetail(){
        switch ($this->method){
            case 'get': // get请求处理代码
                $user_id = Request::instance()->get('user_id');
                $token = Request::instance()->get('token');
                $inter_id = Request::instance()->get('inter_id');
                $bossModel = new Boss();
                $data = $bossModel->getArtistInterviewDetail($inter_id);
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