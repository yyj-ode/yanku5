<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/24
 * Time: 18:41
 */
namespace app\index\controller;
use think\Db;
use \think\Request;
use app\index\model;
use think\cache\driver\Redis;


class Safrica
{

    public function index(Request $request)
    {
        $data = $request->param();
        $con['mobile'] = $data['mobile'];
        $login = Db::table('yk_user')->field('nickname')->where($con)->find();
        $data = result(1,$login);
        return json($data);
    }
    public function sendsms(){
        $mobile = request()->post('mobile');
        $username = "bjykwlkj"; //用户名
        $pwd = "142121"; //密码
        $data = send_sms($username,$pwd,$mobile);
        echo $data;
    }
    public function register(){
        $data = request()->param();
        $mobile = (integer)$data['mobile'];
        $con['mobile'] = $mobile;
        $password = (integer)$data['pwd'];
        $code = (integer)$data['code'];
        $redis = new \Redis();
        $redis->connect("localhost", 6379); //localhost也可以填你服务器的ip
        $redis->select(5);
        $re_code = $redis->get($mobile);
        if ((String)$code == $re_code){
            $redis->del($mobile);
            $login = Db::table('yk_user')->field('nickname')->where($con)->find();
            if(!empty($login)){
                json(result(10));
                die();
            }else{
                $model = new model\User();
                $con['password_salt'] = $model->mkSalt();
                $con['password'] = $model->mkPassword($password);
                $con['nickname'] = $con['mobile'];
                $insert = Db::table('yk_user')->insert($con);
                $data['user_id'] = $insert;
                $options = array('client_id'=>'YXA6ryLaYB6qEee9ag-MCEtXOA','client_secret'=>'YXA6P275ejRYIcHpCHj_eqtonJUmJb4','org_name'=>'1134170411178481','app_name'=>'yanku');
                $token = new Hx($options);
                $result = $token->createUser("$insert",'yanku321');
                empty($result)?json(array('result'=>"2",'message'=>"注册失败")):json(array('result'=>"1",'message'=>"注册成功"));
            }
        }else{
            json(array('result'=>"0",'message'=>"验证失败"));
            die();
        }
    }

}