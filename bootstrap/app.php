<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__.'/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {

}

//realpath(__DIR__.'/../') -> /home/zc/web/lu  绝对路径
$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

$app->withFacades();

//容器Container->singleton方法

//放入容器中
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class, // (string)Illuminate\Contracts\Debug\ExceptionHandler
    App\Exceptions\Handler::class // (string)App\Exceptions\Handler
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

//注册服务提供者
$app->register(App\Providers\AppServiceProvider::class);

//注册Test服务
$app->register(App\Providers\TestServiceProvider::class);



/*
var_dump($app);
exit();
*/
// $group-> trait Concerns\RoutesRequests;
//
$app->group(['namespace' => 'App\Http\Controllers'], function ($app) {
    //$app ->routes.php里面会使用
    require __DIR__.'/../app/Http/routes.php';
});

return $app;





