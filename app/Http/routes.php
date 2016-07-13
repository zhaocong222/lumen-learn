<?php
//Concerns\RoutesRequests -> get() -> $this->addRoute('GET', $uri, $action);
//把路由和匿名函数，加入到路由管理中

// $this->routes['GET/']
$app->get('/', function () use ($app) {
    return $app->version();
});
