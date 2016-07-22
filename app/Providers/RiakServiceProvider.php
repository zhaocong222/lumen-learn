<?php
namespace  App\Providers;

use Illuminate\Support\ServiceProvider;

//必须实现抽象类的抽象方法register
class RiakServiceProvider extends ServiceProvider
{
    /**
     * 在容器中注册绑定。
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Riak\Contracts\Connection', function($app) {
            return new Connection($app['config']['riak']);
        });
    }

}