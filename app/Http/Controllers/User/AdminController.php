<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

//下面2种都是通过Facade方式去获取请求实例
//1.直接use
use Illuminate\Support\Facades\Request;

//2.下面这方式， 取别名class_alias 一行代码应该放在Application.php的withFacades方法里
//class_alias('Illuminate\Support\Facades\Request', 'Request');
//use Request;

class AdminController extends Controller
{


    public function info()
    {
        echo '我是管理员。';
    }


    public function index()
    {
        $data = Request::input();
        var_dump($data);
    }

}
