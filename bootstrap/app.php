<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__.'/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

//realpath(__DIR__.'/../') -> /home/zc/web/lu  绝对路径
$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

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



