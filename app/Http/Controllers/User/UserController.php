<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\AdminController as Admin;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $admin;


    //注入Admin类
    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }


    public function index(Request $request,$id)
    {
        echo "尼玛首页<br>";

        $this->admin->info();

        echo '<br/>';

        echo $id;

    }

    public function mylist(Request $request)
    {
        $data = $request->input();
        echo '<pre>';
        print_r($data);
    }


}
