<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

use App\Http\Controllers\User\AdminController as Admin;


//下面2种都是通过Facade方式去获取请求实例
//1.直接use
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;

class TestController extends Controller
{
    //这里只是参考，应该把这个写在服务提供者里，要不然每个controller都要注入一下
    
    //参考Providers下的TestServiceProvider
    /*
    public function __construct()
    {
        //注入
        app()->singleton('Admin', function () {
            return new Admin;
        });
    }
    */


    public function index(App $app)
    {
        //实例化服务
        //$Admin = app()->make('Admin');
        //或者
        $Admin = $app::make('Admin');
        $Admin->info();
    }

}
