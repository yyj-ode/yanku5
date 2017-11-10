<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
//根据首字母排序
function getFirstCharter($data){
    $city = array();
    foreach ($data as $key=>&$v){
        if(empty($v['city_name'])){return '';}
        $str = $v['city_name'];
        $fchar=ord($str{0});
        if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
        $s1=iconv('UTF-8','gb2312',$str);
        $s2=iconv('gb2312','UTF-8',$s1);
        $s=$s2==$str?$s1:$str;
        $asc=ord($s{0})*256+ord($s{1})-65536;
        if($asc>=-20319&&$asc<=-20284) $v['initial'] = 'A';
        if($asc>=-20283&&$asc<=-19776) $v['initial'] = 'B';
        if($asc>=-19775&&$asc<=-19219) $v['initial'] = 'C';
        if($asc>=-19218&&$asc<=-18711) $v['initial'] = 'D';
        if($asc>=-18710&&$asc<=-18527) $v['initial'] = 'E';
        if($asc>=-18526&&$asc<=-18240) $v['initial'] = 'F';
        if($asc>=-18239&&$asc<=-17923) $v['initial'] = 'G';
        if($asc>=-17922&&$asc<=-17418) $v['initial'] = 'H';
        if($asc>=-17417&&$asc<=-16475) $v['initial'] = 'J';
        if($asc>=-16474&&$asc<=-16213) $v['initial'] = 'K';
        if($asc>=-16212&&$asc<=-15641) $v['initial'] = 'L';
        if($asc>=-15640&&$asc<=-15166) $v['initial'] = 'M';
        if($asc>=-15165&&$asc<=-14923) $v['initial'] = 'N';
        if($asc>=-14922&&$asc<=-14915) $v['initial'] = 'O';
        if($asc>=-14914&&$asc<=-14631) $v['initial'] = 'P';
        if($asc>=-14630&&$asc<=-14150) $v['initial'] = 'Q';
        if($asc>=-14149&&$asc<=-14091) $v['initial'] = 'R';
        if($asc>=-14090&&$asc<=-13319) $v['initial'] = 'S';
        if($asc>=-13318&&$asc<=-12839) $v['initial'] = 'T';
        if($asc>=-12838&&$asc<=-12557) $v['initial'] = 'W';
        if($asc>=-12556&&$asc<=-11848) $v['initial'] = 'X';
        if($asc>=-11847&&$asc<=-11056) $v['initial'] = 'Y';
        if($asc>=-11055&&$asc<=-10247) $v['initial'] = 'Z';
    }

    return $data;
}
//经验规则
function exp_rule($day){
    $data = array();
    $rule = file_get_contents('rule.json');
    $rule = json_decode($rule,true);
    foreach ($rule as $k=>$v){
        if ($day>=$v['s_day']&&$day<=$v['e_day']){
            $data['kubi'] = $v['kubi'];
            $data['experience'] = $v['exp'];
            break;
        }
    }
    if ($day>=7){
        $data['experience'] += 10;
    }else{
        if(1==$day){
            $data['experience'] += 1;
        }elseif (2==$day||3==$day){
            $data['experience'] += 2;
        }elseif (4==$day||5==$day||6==$day){
            $data['experience'] += 5;
        }
    }
//    $k = rand(168,300);
//    $data['kubi']=$data['kubi']+$k;
    return $data;
}
//创建手机验证码
function code($mobile){
    $code = rand(1000,9999);
    $redis = new Redis();
    $redis->connect("localhost", 6379); //localhost也可以填你服务器的ip
    $redis->select(5);
    $redis->set($mobile,$code);
    $redis->expire($mobile,6000); //EXPIREAT key 1377257300
    return $code;
}
function formatTime(){
    return date('mdHis');
}
function getMillisecond() {
    list($s1, $s2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
}
//发送手机验证码
function send_sms($username,$pwd,$mobile){
    $post_data = array();
    $post_data['UserName'] = $username;
    $timestamp = formatTime();//时间戳
    $code = code($mobile);
    $post_data['Key'] = md5($username.$pwd.$timestamp);
    $post_data['Timestemp'] = formatTime();
    $post_data['Mobiles'] = $mobile;
    $post_data['Content'] = urlencode("【演库科技】您的验证码是$code"."。演绎未来的通路");
    $post_data['CharSet'] = "utf-8";
    $post_data['SchTime'] = "";
    $post_data['Priority'] = "5";
    $post_data['PackID'] = "";
    $post_data['PacksID'] = "";
    $post_data['ExpandNumber'] = "";
    $post_data['SMSID'] = getMillisecond();//long型数据，此处案例使用了当前的毫秒值，也可根据实际情况进行处理
    $url='http://www.youxinyun.com:3070/Platform_Http_Service/servlet/SendSms';
    $o="";
    foreach ($post_data as $k=>$v)
    {
        $o.= "$k=".$v."&";
    }
    $post_data=substr($o,0,-1);
    $this_header = array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$this_header);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);//返回相应的标识，具体请参考我方提供的短信API文档
    curl_close($ch);
    return $result;
}
function array_to_object($arr) {
    if (gettype($arr) != 'array') {
        return $arr;
    }
    foreach ($arr as $k => $v) {
        if (gettype($v) == 'array' || getType($v) == 'object') {
            $arr[$k] = (object)array_to_object($v);
        }
    }
    return (object)$arr;
}
function object_to_array($object) {
    $array = array();
    if (is_object($object)) {
        foreach ($object as $key => $value) {
            $array[$key] = $value;
        }
    }
    else {
        $array = $object;
    }
    return $array;
}
function emoji_encode($str){
    $strEncode = '';

    $length = mb_strlen($str,'utf-8');

    for ($i=0; $i < $length; $i++) {
        $_tmpStr = mb_substr($str,$i,1,'utf-8');
        if(strlen($_tmpStr) >= 4){
            $strEncode .= '[[EMOJI:'.rawurlencode($_tmpStr).']]';
        }else{
            $strEncode .= $_tmpStr;
        }
    }

    return $strEncode;
}
function emoji_decode($str){
    $strDecode = preg_replace_callback('|\[\[EMOJI:(.*?)\]\]|', function($matches){
        return rawurldecode($matches[1]);
    }, $str);

    return $strDecode;
}
function asciitostr($sacii){
    $asc_arr= str_split(strtolower($sacii),2);
    $str='';
    for($i=0;$i<count($asc_arr);$i++){
        $str.=chr(hexdec($asc_arr[$i][1].$asc_arr[$i][0]));
    }
    return mb_convert_encoding($str,'UTF-8','GB2312');
}
//合成通告图，用户选定的背景图编号方式
function miximg($char,$pic){
    $imgTest = imagecreatetruecolor(750,400);
    $imgReal = imagecreatefromjpeg("/usr/local/apache/htdocs/yanku5/material/big/$pic.png");
    $width = imagesx ($imgTest);
    $height = imagesy ($imgTest);
    if (8>=mb_strlen($char)){
        $fontSize = 54;
        $textWidth = $fontSize * mb_strlen ($char)*4/3;
        $textHeight = $fontSize*4/3;
        $x = round(($width-$textWidth)/2);//计算文字的水平位置
        $y = round(($height+$textHeight)/2);//计算文字的垂直位置
        $arr = imgwrite($imgTest,$fontSize,$x,$y,$char);
        $x = round(($arr[0]+(750-$arr[2]))/2);
        imgwrite($imgReal,$fontSize,$x,$y,$char);
    }else{
        $char1 = mb_substr($char,-(mb_strlen ($char)-8));
        $char = mb_substr($char,0,8);
        $fontSize = 36;
        $textWidth = $fontSize * mb_strlen ($char)*4/3;
        $textHeight = $fontSize*4/3;
        $x = round(($width-$textWidth)/2);//计算文字的水平位置
        $y = round(($height-$textHeight)/2-18);//计算文字的垂直位置
        $arr = imgwrite($imgTest,$fontSize,$x,$y,$char);
        $x = round(($arr[0]+(750-$arr[2]))/2);
        imgwrite($imgReal,$fontSize,$x,$y,$char);
        $textWidth = $fontSize * mb_strlen ($char1)*4/3;
        $x = round(($width-$textWidth)/2);//计算文字的水平位置
        $y = round(($height+$textHeight)/2+18);//计算文字的垂直位置
        $arr = imgwrite($imgTest,$fontSize,$x,$y,$char1);
        $x = round(($arr[0]+(750-$arr[2]))/2);
        imgwrite($imgReal,$fontSize,$x,$y,$char1);
    }
    $ran = substr(md5(time()),0,15).rand(0,99);
    $save_path[1] = "/upload/Schedule/$ran.jpeg";
    imagejpeg ($imgReal,$save_path[1]);
    imagedestroy($imgTest);
    imagedestroy($imgReal);
    $save_path[2] = "Schedule/$ran.jpeg";
    return $save_path;
}
//合成通告图，用户自己上传图片方式
function miximg2($char,$pic){
    $imgTest = imagecreatetruecolor(750,400);
    $imgReal = imagecreatefromjpeg("/upload/$pic");
    $width = imagesx ($imgTest);
    $height = imagesy ($imgTest);
    if (8>=mb_strlen($char)){
        $fontSize = 54;
        $textWidth = $fontSize * mb_strlen ($char)*4/3;
        $textHeight = $fontSize*4/3;
        $x = round(($width-$textWidth)/2);//计算文字的水平位置
        $y = round(($height+$textHeight)/2);//计算文字的垂直位置
        $arr = imgwrite($imgTest,$fontSize,$x,$y,$char);
        $x = round(($arr[0]+(750-$arr[2]))/2);
        imgwrite($imgReal,$fontSize,$x,$y,$char);
    }else{
        $char1 = mb_substr($char,-(mb_strlen ($char)-8));
        $char = mb_substr($char,0,8);
        $fontSize = 36;
        $textWidth = $fontSize * mb_strlen ($char)*4/3;
        $textHeight = $fontSize*4/3;
        $x = round(($width-$textWidth)/2);//计算文字的水平位置
        $y = round(($height-$textHeight)/2-18);//计算文字的垂直位置
        $arr = imgwrite($imgTest,$fontSize,$x,$y,$char);
        $x = round(($arr[0]+(750-$arr[2]))/2);
        imgwrite($imgReal,$fontSize,$x,$y,$char);
        $textWidth = $fontSize * mb_strlen ($char1)*4/3;
        $x = round(($width-$textWidth)/2);//计算文字的水平位置
        $y = round(($height+$textHeight)/2+18);//计算文字的垂直位置
        $arr = imgwrite($imgTest,$fontSize,$x,$y,$char1);
        $x = round(($arr[0]+(750-$arr[2]))/2);
        imgwrite($imgReal,$fontSize,$x,$y,$char1);
    }
    $ran = substr(md5(time()),0,15).rand(0,99);
    $save_path[1] = "/upload/Schedule/$ran.jpeg";
    imagejpeg ($imgReal,$save_path[1]);
    imagedestroy($imgTest);
    imagedestroy($imgReal);
    $save_path[2] = "Schedule/$ran.jpeg";
    return $save_path;
}
function imgwrite($img,$fontSize,$x,$y,$char){
    $white = imagecolorallocate($img, 255, 255, 255);
    $shadow = imagecolorclosestalpha($img,0,0,0,40);
    $shadow1 = imagecolorclosestalpha($img,0,0,0,100);
    imagefttext($img, $fontSize, 0, $x+2, $y+2, $shadow, "/usr/local/apache/htdocs/yanku5/material/font.otf", $char);
    imagefttext($img, $fontSize, 0, $x+4, $y+4, $shadow1, "/usr/local/apache/htdocs/yanku5/material/font.otf", $char);
    $arr = imagefttext($img, $fontSize, 0, $x, $y, $white, "/usr/local/apache/htdocs/yanku5/material/font.otf", $char);
    return $arr;
}
function info(){
    $finfo = finfo_open(FILEINFO_MIME); // 返回 mime 类型
    $filename = '/usr/local/apache/htdocs/yanku5/material/big/1.png';
    var_dump(finfo_file($finfo, $filename));
    finfo_close($finfo);
    die;
}
function ip() {
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
         $ip = getenv('HTTP_CLIENT_IP');
    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
     } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
         $ip = getenv('REMOTE_ADDR');
     } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
         $ip = $_SERVER['REMOTE_ADDR'];
     }
     return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
}
function miximg1($char,$pic){
    $img = imagecreatefromjpeg("/usr/local/apache/htdocs/yanku5/material/big/$pic.png");
    $width = imagesx ($img);
    $height = imagesy ($img);
    if (8>=mb_strlen($char)){
        $fontSize = 54;
        $textWidth = $fontSize * mb_strlen ($char)*4/3;
        $textHeight = $fontSize*4/3;
        $x = round(($width-$textWidth)/2);//计算文字的水平位置
        $y = round(($height+$textHeight)/2);//计算文字的垂直位置
        imgwrite($img,$fontSize,$x,$y,$char);
    }else{
        $char1 = mb_substr($char,-3);
        $char = mb_substr($char,0,8);
        $fontSize = 36;
        $textWidth = $fontSize * mb_strlen ($char)*4/3;
        $textHeight = $fontSize*4/3;
        $x = round(($width-$textWidth)/2);//计算文字的水平位置
        $y = round(($height-$textHeight)/2-18);//计算文字的垂直位置
        imgwrite($img,$fontSize,$x,$y,$char);
        $textWidth = $fontSize * mb_strlen ($char1)*4/3;
        $x = round(($width-$textWidth)/2);//计算文字的水平位置
        $y = round(($height+$textHeight)/2+18);//计算文字的垂直位置
        imgwrite($img,$fontSize,$x,$y,$char1);
    }
    imagejpeg ($img,'/upload/2.jpeg');
    imagedestroy($img);
}
function cu_get($url){
    $ch = curl_init();
    //设置选项，包括URL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    //执行并获取HTML文档内容
    $output = curl_exec($ch);
    //释放curl句柄
    curl_close($ch);
    return $output;
}
function fore($arr){
    return json_decode($arr);
}
function msg($num,$res,$a){
    if (1==$a){
        $arr = array(
//            1=>"感谢您报名$res[schedule_title]，咱们山水有相逢，期待与您的下次合作",
//            2=>"感谢您报名$res[schedule_title]，胜败乃兵家常事，少侠请重新来过",
//            3=>"感谢您报名$res[schedule_title], 不能击败你的，会让你更强大, 期待与您的下次合作",
//            4=>"感谢您报名$res[schedule_title]，人生不如意，十之八九, 期待与您的下次合作",
            1=>"非常感谢您报名$res[schedule_title]，根据角色定位，我们觉得可能与您的情况有些差异，非常抱歉，但是我们非常期待日后与您合作。",
            2=>"非常感谢您报名$res[schedule_title]，根据角色定位，我们觉得可能与您的情况有些差异，非常抱歉，但是我们非常期待日后与您合作。"

        );
    }else{
        $arr = array(
            1=>"很高兴您报名$res[schedule_title],若您有空，咱们可以进一步邀约面试",
            2=>"很高兴您报名$res[schedule_title],若您有空，咱们可以进一步邀约面试",
//            2=>"感谢您报名$res[schedule_title]，此刻开始，战场将由你主宰，约戏吗",
        );
    }
    return $arr[$num];
}