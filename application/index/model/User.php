<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/26
 * Time: 9:24
 */
namespace app\index\model;

use think\Model;

class User extends Model
{
    // 开启批量验证
    protected $patchValidate = true;
    // 验证
    protected $_validate = [
        // 自己补充验证规则
    ];

    // 填充
    protected $_auto = [
        // 自己补充填充规则
        ['salt', 'mkSalt', 'callback'],
        ['password', 'mkPassword', 'callback'],
        // 仅仅需要在插入维护
        ['created_at', 'time', 'function'],
        // 更新时间, 插入和更新时 都需要更新
        ['updated_at', 'time', 'function'],
    ];

    // 生产盐值
    public function mkSalt($value=null)
    {
        // 生产一段5个长度的随机字符串
        $salt = substr(md5(time()), 0, 5);
        $this->salt = $salt;// 记录下来
        return $salt;
    }
    // 生产密码
    public function mkPassword($value)
    {
        // 盐值+密码 sha1 混淆
        return sha1($this->salt . $value);
    }
    function _call($function_name,$arguments)
    {
        return $this->$function_name($arguments);
    }
}