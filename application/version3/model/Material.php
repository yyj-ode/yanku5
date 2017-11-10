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

class Material extends Model
{
    protected $pk = 'material_id';

    public function getSmallBackgound(){
        $data = Db::table("yk_material")
            ->field("material_img")
            ->select();
        $basicModel = new Basic();
//        把结果集对象中的图片字段拼接成可访问的url字符串
        foreach($data as $k => $v){
            $arr = $v;
            preg_match('/\/(\d+)\.png/',$v['material_img'],$id);
            $arr['material_id'] = $id[1] + 0;
            $data[$k] = $arr;
        }
        $data = $basicModel->obj_imgstr_url($data,'material_img');
        return $data;
    }
}