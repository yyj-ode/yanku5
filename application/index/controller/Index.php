<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        return '演库时代---演绎未来的通路';
    }
    public function test(){
        $data=array();
        $data['key']=123456;
        $data['value']=123456;
        return json($data);
    }
}
