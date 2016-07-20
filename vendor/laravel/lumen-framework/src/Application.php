<?php
namespace Laravel\Lumen;

use Monolog\Logger;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Composer;
use Monolog\Handler\StreamHandler;
use Illuminate\Container\Container;
use Monolog\Formatter\LineFormatter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;
use Zend\Diactoros\Response as PsrResponse;
use Illuminate\Config\Repository as ConfigRepository;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

class Application extends Container
{
    use Concerns\RoutesRequests,
        Concerns\RegistersExceptionHandlers;

    protected static $aliasesRegistered = false;

    protected $basePath;

    //加载的配置
    protected $loadedConfigurations = [];
    //The service binding methods that have been executed.
    protected $ranServiceBinders = [];


    //初始化一些东西
    public function __construct($basePath = null)
    {
        //设置时区 UTC为默认值，如果没有在.env中设置就为UTC
        date_default_timezone_set(env('APP_TIMEZONE','UTC'));

        //执行魔术方法__set,然后执行offsetSet方法， 将基础路径设为根目录 /home/zc/web/lu
        $this->basePath = $basePath;
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

    //获取或者验证当前环境
    public function environment()
    {
        //获取APP_ENV配置，如果不存在，默认设置为production
        $env = env('APP_ENV','production');

        if (func_num_args() > 0){
            $patterns = is_array(func_get_args(0)) ? func_get_args(0) : func_get_args();

            foreach ($patterns as $pattern) {
                if (Str::is($pattern, $env)) {
                    return true;
                }
            }

            return false;

        }

        return $env;

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
        if($path = $this->getConfigurationPath($name)){
            //$this->make('config') -> 初始化 Illuminate\Config\Repository的$items成员属性
            //$this->make('config') -> 相当于 new Illuminate\Config\Repository()
            //设置 $name.php 里面的属性
            //$this->make('config')->set($name, require $path); //框架本身代码

            //修改后
            $this->make('config')->set($name,require $path);
            //返回设置的配置文件

            //print_r($this->make('config')->get($name));
            //自己修改返回的
            return $this->make('config')->get($name);
        }

    }


    public function make($abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($this->normalize($abstract));

        if (array_key_exists($abstract,$this->availableBindings) &&
            !array_key_exists($this->availableBindings[$abstract], $this->ranServiceBinders))
        {
            //$this->availableBindings['config'] = 'registerConfigBindings';
            $this->{$method = $this->availableBindings[$abstract]}(); //$this->registerConfigBindings();
            $this->ranServiceBinders[$method] = true;
        }

        return parent::make($abstract, $parameters);//实质上就是return new ConfigRepository;
    }

    //注册配置文件绑定
    protected function registerConfigBindings()
    {
        //执行bind ,把$this->bindings['config'] = ['concrete'=>匿名函数,'shared'=>1]
        $this->singleton('config', function () {
            return new ConfigRepository;
        });
    }


    //返回配置文件的路径 .$app->configure('xx') 根目录下config目录里 -> xx.php
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

    protected function registerRequestBindings()
    {
        $this->singleton('Illuminate\Http\Request', function () {
            return $this->prepareRequest(Request::capture());
        });
    }

    protected function prepareRequest(Request $request)
    {
        $request->setUserResolver(function () {
            return $this->make('auth')->user();
        })->setRouteResolver(function () {
            return $this->currentRoute;
        });

        return $request;
    }

    /**
     * Register container bindings for the PSR-7 request implementation.
     *
     * @return void
     */
    protected function registerPsrRequestBindings()
    {
        $this->singleton('Psr\Http\Message\ServerRequestInterface', function () {
            return (new DiactorosFactory)->createRequest($this->make('request'));
        });
    }

    /**
     * Register container bindings for the PSR-7 response implementation.
     *
     * @return void
     */
    protected function registerPsrResponseBindings()
    {
        $this->singleton('Psr\Http\Message\ResponseInterface', function () {
            return new PsrResponse();
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerTranslationBindings()
    {
        $this->singleton('translator', function () {
            $this->configure('app');

            $this->instance('path.lang', $this->getLanguagePath());

            $this->register('Illuminate\Translation\TranslationServiceProvider');

            return $this->make('translator');
        });
    }



    //可用的容器绑定
    public $availableBindings = [
        'auth' => 'registerAuthBindings',
        'auth.driver' => 'registerAuthBindings',
        'Illuminate\Contracts\Auth\Guard' => 'registerAuthBindings',
        'Illuminate\Contracts\Auth\Access\Gate' => 'registerAuthBindings',
        'Illuminate\Contracts\Broadcasting\Broadcaster' => 'registerBroadcastingBindings',
        'Illuminate\Contracts\Bus\Dispatcher' => 'registerBusBindings',
        'cache' => 'registerCacheBindings',
        'cache.store' => 'registerCacheBindings',
        'Illuminate\Contracts\Cache\Factory' => 'registerCacheBindings',
        'Illuminate\Contracts\Cache\Repository' => 'registerCacheBindings',
        'composer' => 'registerComposerBindings',
        'config' => 'registerConfigBindings',
        'db' => 'registerDatabaseBindings',
        'Illuminate\Database\Eloquent\Factory' => 'registerDatabaseBindings',
        'encrypter' => 'registerEncrypterBindings',
        'Illuminate\Contracts\Encryption\Encrypter' => 'registerEncrypterBindings',
        'events' => 'registerEventBindings',
        'Illuminate\Contracts\Events\Dispatcher' => 'registerEventBindings',
        'files' => 'registerFilesBindings',
        'hash' => 'registerHashBindings',
        'Illuminate\Contracts\Hashing\Hasher' => 'registerHashBindings',
        'log' => 'registerLogBindings',
        'Psr\Log\LoggerInterface' => 'registerLogBindings',
        'queue' => 'registerQueueBindings',
        'queue.connection' => 'registerQueueBindings',
        'Illuminate\Contracts\Queue\Factory' => 'registerQueueBindings',
        'Illuminate\Contracts\Queue\Queue' => 'registerQueueBindings',
        'request' => 'registerRequestBindings',
        'Psr\Http\Message\ServerRequestInterface' => 'registerPsrRequestBindings',
        'Psr\Http\Message\ResponseInterface' => 'registerPsrResponseBindings',
        'Illuminate\Http\Request' => 'registerRequestBindings',
        'translator' => 'registerTranslationBindings',
        'url' => 'registerUrlGeneratorBindings',
        'validator' => 'registerValidatorBindings',
        'Illuminate\Contracts\Validation\Factory' => 'registerValidatorBindings',
        'view' => 'registerViewBindings',
        'Illuminate\Contracts\View\Factory' => 'registerViewBindings',
    ];

    protected function registerAuthBindings()
    {
        $this->singleton('auth', function () {
            return $this->loadComponent('auth', 'Illuminate\Auth\AuthServiceProvider', 'auth');
        });

        $this->singleton('auth.driver', function () {
            return $this->loadComponent('auth', 'Illuminate\Auth\AuthServiceProvider', 'auth.driver');
        });

        $this->singleton('Illuminate\Contracts\Auth\Access\Gate', function () {
            return $this->loadComponent('auth', 'Illuminate\Auth\AuthServiceProvider', 'Illuminate\Contracts\Auth\Access\Gate');
        });
    }

    public function loadComponent($config, $providers, $return = null)
    {
        $this->configure($config);

        foreach ((array) $providers as $provider) {
            $this->register($provider);
        }

        return $this->make($return ?: $config);
    }

    public function register($provider, $options = [], $force = false)
    {
        if (! $provider instanceof ServiceProvider) {
            $provider = new $provider($this);
        }

        if (array_key_exists($providerName = get_class($provider), $this->loadedProviders)) {
            return;
        }

        $this->loadedProviders[$providerName] = true;

        $provider->register();
        $provider->boot();
    }

    protected function registerLogBindings()
    {
        $this->singleton('Psr\Log\LoggerInterface', function () {
            if ($this->monologConfigurator) {
                return call_user_func($this->monologConfigurator, new Logger('lumen'));
            } else {
                return new Logger('lumen', [$this->getMonologHandler()]);
            }
        });
    }


    public function withFacades()
    {
        Facade::setFacadeApplication($this); //->static::$app = $this;
        return; //修改的

        if (! static::$aliasesRegistered) {
            static::$aliasesRegistered = true;

            //class_alias — 为一个类创建别名
            class_alias('Illuminate\Support\Facades\Auth', 'Auth');
            class_alias('Illuminate\Support\Facades\Cache', 'Cache');
            class_alias('Illuminate\Support\Facades\DB', 'DB');
            class_alias('Illuminate\Support\Facades\Event', 'Event');
            class_alias('Illuminate\Support\Facades\Gate', 'Gate');
            class_alias('Illuminate\Support\Facades\Log', 'Log');
            class_alias('Illuminate\Support\Facades\Queue', 'Queue');
            class_alias('Illuminate\Support\Facades\Schema', 'Schema');
            class_alias('Illuminate\Support\Facades\URL', 'URL');
            class_alias('Illuminate\Support\Facades\Validator', 'Validator');

            //导入Request facde
            //class_alias('Illuminate\Support\Facades\Request', 'Request');
        }
    }






}