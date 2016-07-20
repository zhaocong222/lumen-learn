<?php
//Concerns\RoutesRequests -> get() -> $this->addRoute('GET', $uri, $action);
//把路由和匿名函数，加入到路由管理中

// $this->routes['GET/']
/*
$app->get('/', function () use ($app) {

    //加载自定义配置文件, config/app.php
    $app->configure('app');
    //print_r($app->configure('app'));//获取配置
    //print_r(config('app'));

    //判断当前是否为local或者staging环境， 正确返回true
    $environment = app()->environment('local','staging');
    var_dump($environment);

    return $app->version();
});
*/

//当前
//$app->group(['middleware' => 'auth'], function () use ($app){
    /*
    $app->get('/', function () {
        echo 1231;
    });
    */

//});

$app->get('/user/{id:\d+}','User\UserController@index');

$app->get('/list','User\UserController@mylist');

$app->get('/cache','User\UserController@cache');

$app->get('/admin','User\AdminController@index');