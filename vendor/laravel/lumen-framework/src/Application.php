<?php
namespace Laravel\Lumen;

use Illuminate\Container\Container;

class Application extends Container
{
    use Concerns\RoutesRequests,
        Concerns\RegistersExceptionHandlers;

    //加载的配置
    protected $loadedConfigurations = [];


    //初始化一些东西
    public function __construct($basePath = null)
    {
        //设置时区 UTC为默认值，如果没有在.env中设置就为UTC
        date_default_timezone_set(env('APP_TIMEZONE','UTC'));

        //执行魔术方法__set,然后执行offsetSet方法， 将基础路径设为根目录 /home/zc/web/lu
        var_dump(isset($this->basePath));
        $this->basePath = $basePath;
        var_dump(isset($this->basePath)); //false
        exit();
        $this->bootstrapContainer();
        //注册错误和异常处理
        //$this->registerErrorHandling();
    }


    protected function bootstrapContainer()
    {
        //Container的setInstance方法
        //$this -> Laravel\Lumen\Application ,把自己存入静态变量->static::$instance
        static::setInstance($this);

        //把自己的实例注册到容器中（就是数组instances）
        $this->instance('app',$this);
        $this->instance('Laravel\Lumen\Application', $this);
        //$this->path(); home/zc/web/lu/app
        $this->instance('path', $this->path());

        //注册容器别名,放入aliases
        $this->registerContainerAliases();

    }

    //注册容器别名
    protected function registerContainerAliases()
    {
        $this->aliases = [
            'Illuminate\Contracts\Foundation\Application' => 'app',
            'Illuminate\Contracts\Auth\Factory' => 'auth',
            'Illuminate\Contracts\Auth\Guard' => 'auth.driver',
            'Illuminate\Contracts\Cache\Factory' => 'cache',
            'Illuminate\Contracts\Cache\Repository' => 'cache.store',
            'Illuminate\Contracts\Config\Repository' => 'config',
            'Illuminate\Container\Container' => 'app',
            'Illuminate\Contracts\Container\Container' => 'app',
            'Illuminate\Database\ConnectionResolverInterface' => 'db',
            'Illuminate\Database\DatabaseManager' => 'db',
            'Illuminate\Contracts\Encryption\Encrypter' => 'encrypter',
            'Illuminate\Contracts\Events\Dispatcher' => 'events',
            'Illuminate\Contracts\Hashing\Hasher' => 'hash',
            'log' => 'Psr\Log\LoggerInterface',
            'Illuminate\Contracts\Queue\Factory' => 'queue',
            'Illuminate\Contracts\Queue\Queue' => 'queue.connection',
            'request' => 'Illuminate\Http\Request',
            'Laravel\Lumen\Routing\UrlGenerator' => 'url',
            'Illuminate\Contracts\Validation\Factory' => 'validator',
            'Illuminate\Contracts\View\Factory' => 'view',
        ];
    }

    //返回跟目录下的app目录的绝对路径
    public function path()
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'app';
    }

    //返回版本号
    public function version()
    {
        return '尼玛这里是 Lumen (5.2.7)';
    }

    //自定义加载配置文件
    public function configure($name)
    {
        //注册树
        if (isset($this->loadedConfigurations[$name])){
            return;
        }

        //给一个默认值
        $this->loadedConfigurations[$name] = true;
        //获取配置文件的路径
        $path = $this->getConfigurationPath($name);



    }

    //获取配置文件的路径
    public function getConfigurationPath($name = null)
    {

        if (! $name) {

            $appConfigDir = $this->basePath('config').'/';

            if (file_exists($appConfigDir)) {
                return $appConfigDir;
            } elseif (file_exists($path = __DIR__.'/../config/')) {
                return $path;
            }
        } else {

            $appConfigPath = $this->basePath('config').'/'.$name.'.php';
            exit($appConfigPath);

            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            } elseif (file_exists($path = __DIR__.'/../config/'.$name.'.php')) {
                return $path;
            }
        }
    }


    //获取basePath
    public function basePath($path = null)
    {
        var_dump(isset($this->basePath));
        echo 1231;
        exit();
        //getcwd(); ->/home/zc/web/lu/public
        if (isset($this->basePath)){
            return rtrim($this->basePath,'/').($path ? DIRECTORY_SEPARATOR.$path : $path);
        }

        if ($this->runningInConsole()) {
            $this->basePath = getcwd();
        } else {
            $this->basePath = realpath(getcwd().'/../');
        }

        return $this->basePath($path);

    }

    //在命令行下运行
    public function runningInConsole()
    {
        //apache2handler
        //cli
        //***cgi
        return php_sapi_name() == 'cli';
    }






}