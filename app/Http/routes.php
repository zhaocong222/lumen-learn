<?php
//Concerns\RoutesRequests -> get() -> $this->addRoute('GET', $uri, $action);
//把路由和匿名函数，加入到路由管理中

// $this->routes['GET/']
$app->get('/', function () use ($app) {

    //加载自定义配置文件, config/app.php
    $app->configure('app');
    //print_r($app->configure('app'));//获取配置
    print_r(config('app'));
    exit();

    return $app->version();
});
