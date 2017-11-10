<?php
/**
 * Created by PhpStorm.
 * User: wangfuruo
 * Date: 2017/8/22
 * Time: 下午2:08
 */
namespace app\version2\controller;
use OSS\OssClient;
use OSS\Core\OssException;
use think\cache\driver\Redis;
use think\cache\Driver;
use think\controller\Rest;
use think\response\Json;
use think\image;

class Basic extends Rest {
    protected $result = array('result'=>'','message'=>'','Version'=>'2.0.0','data'=>array());
    public function status($code,$data=array()){
        static $_status = array(
            0 => 'False',//失败
            1 => 'OK',//成功
            2 => 'Created',//创建成功
            3 => '不存在',//不存在
            4 => 'No Content', //无内容
            5 => 'Param Incomplete', //参数不全
            6 => '请重新登录', //token错误
            7 => 'Consume False', //消费失败
            8 => 'Balance Not Enough', //余额不足
            9 => '文件上传失败', //文件上传失败
            10 => '已存在', //已存在
            11 => 'Param False', //参数错误
            12 => '粉丝暂无贡献',
            13 => '无数据',
            14 => '交易失败',
            15 => '该主播已下播',
            16 => '开播失败',
            17 => '空',
            18 => '注册信息不全',
            19 => '未认证',
            20 => '推送失败',
            21 => '主播暂未开播',
            22 => '房间不存在',
            23 => '不能与自己连麦哦',
            24 => '尚未通过认证',
            25 => '接受面试失败',
            26 => '您未报名该面试',
            27 => '该昵称已存在',
            28 => '您的boss认证尚未通过，无法修改信息',
            29 => '并未修改任何数据',
            30 => '截止时间必须大于今天',
            31 => '手机号已注册',
            32 => '演员已接受该通告面试，请勿重复发布',
            33 => '面试已过期',
            34 => '请绑定手机号'
        );
        $this->result['data'] = array_to_object($data);
        $this->result['result'] = $code;
        $this->result['message'] = $_status[$code];
        return $this->result;
    }
    function redisConect($select,$expire=0){
        $config = [
            'host'       => 'localhost',
            'port'       => 6379,
            'password'   => '',
            'select'     => $select,
            'timeout'    => 0,
            'expire'     => $expire,
            'persistent' => false,
            'prefix'     => '',
        ];
        $Redis =new Redis($config);
        return $Redis;
    }
    function tokenAudit($user_id,$token){
        $Redis = $this->redisConect(1);
        if ($token != $Redis->get($user_id) || '' == $token) {
            $res = $this->status(6,array());
            echo json_encode($res,JSON_UNESCAPED_UNICODE);
            die();
        }else{
            return 1;
        }
    }
    function upload(){
        $file = request()->file('image');
        // 移动到框架应用根目录/public/uploads/ 目录下
//        $info = $file->move(ROOT_PATH . 'upload');
//        $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'upload/');
        $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,gif,jpeg'])->move('/upload/');
        if($info){
            // 成功上传后 获取上传信息
            $up['filename'] = $info->getSaveName();
            return $up;
        }else{
            // 上传失败获取错误信息
            return $file->getError();
        }
    }
    public function uploads(){
        // 获取表单上传文件$path
        $files = request()->file('image');
        if(''==$files){
            return '';
            die();
        }
        foreach($files as $k=>$file){
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,gif,jpeg'])->move('/upload/');
//            $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'upload/');
//            var_dump($file);
            if($info){
                // 成功上传后 获取上传信息
                $data1['filename'] = $info->getSaveName();
                $data[] = $data1;
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
                die();
            }
        }
        return $data;
    }
    public function difUpload(){
        // 获取表单上传文件
        $files = request()->file('image');
        foreach($files as $k=>$file){
            // 移动到框架应用根目录/public/uploads/ 目录下
//            $info = $file->validate(['ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'upload/');
            $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,gif,jpeg'])->move('/upload/');
            if($info){
                $data[$k]['filename'] =  $info->getSaveName();
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
                die();
            }
        }
        return $data;
    }
    function uploadOss($file)
    {
        $accessKeyId = "LTAIysus6HEVSHMJ";
        $accessKeySecret = "0R9uNt346k4tLFe3HPPqULxVC7qeqd";
        $endpoint = "http://oss-cn-qingdao.aliyuncs.com";
        $bucket = "yanku";
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        } catch (OssException $e) {
            print $e->getMessage();
        }
        $object = $file['path']."/".$file['filename'];
        $filePath = '/upload/' . $file['filename'];
        try{
            $ossClient->uploadFile($bucket, $object, $filePath);
            unlink($filePath);
            return $object;
        } catch(OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
    }
    function img_url($filed,$arr){
        $arr[$filed] = 'http://img.yankushidai.com/'.$arr[$filed];
        return $arr;
    }
    function img_urls($filed,$arr){
        foreach ($arr as $k=>&$v){
          $v[$filed] = 'http://img.yankushidai.com/'.$v[$filed];
        }
        return $arr;
    }
    public function imgs_url($data,$filter,$url){
        foreach ($data as $key=>&$value) {
            foreach ($value[$filter] as $k=>&$v){
                $v = 'http://img.yankushidai.com/'. $v;
            }
        }
        return $data;
    }
    /**
     * @param $data
     * @param $filter
     * 把结果集对象中的图片字段拼接成可访问的url数组
     * 例如：结果集对象中的img字段'1.jpg,2.jpg,3.jpg'转为['http://1.jpg','http://2.jpg','http://3.jpg']
     */
    public function obj_imgstr_urls($data,$filter){
        foreach($data as $k=>$v){
            $arr = $v;
            $arr[$filter] = explode(',',$v[$filter]);
            foreach($arr[$filter] as $k1 => $v1){
                $arr[$filter][$k1] = 'http://img.yankushidai.com/'. $v1;
            }
            $data[$k] = $arr;
        }
        return $data;
    }
    /**
     * @param $data
     * @param $filter
     * 把结果集对象中的图片字段拼接成可访问的url字符串
     * 例如：结果集对象中的img字段'1.jpg'转为'http://1.jpg'
     */
    public function obj_imgstr_url($data,$filter){
        foreach($data as $k => $v ){
            $arr = $v;
            $arr = $this->img_url($filter,$arr);
            $data[$k] = $arr;
        }
        return $data;
    }
    function retur($res,$data=array()){
        if ($res){
            return Json(self::status(1,$data));
        }else{
            return Json(self::status(0,$data));
        }
    }
    function echojson(){
        $arr = array('pay'=>0,'update'=>0,'live'=>0);
        $array = json_encode($arr,JSON_UNESCAPED_UNICODE);
        $myfile = fopen("/usr/local/apache/htdocs/yanku5/upload/ios.json", "w");
        fwrite($myfile, $array);
        fclose($myfile);
    }
    function ce(){
        $accessKeyId = "LTAIysus6HEVSHMJ";
        $accessKeySecret = "0R9uNt346k4tLFe3HPPqULxVC7qeqd";
        $endpoint = "http://oss-cn-qingdao.aliyuncs.com";
        $bucket = "yanku";
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        } catch (OssException $e) {
            print $e->getMessage();
        }
        $object = $file['path']."/".$file['filename'];
        $filePath = ROOT_PATH . 'upload/' . $file['filename'];
        try{
            $ossClient->uploadFile($bucket, $object, $filePath);
            unlink($filePath);
            return $object;
        } catch(OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
    }
}