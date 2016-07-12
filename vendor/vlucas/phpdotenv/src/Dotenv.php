<?php
namespace Dotenv;

//通过.env加载环境变量并且能够自动的通过getenv(),$_ENV和$_SERVER自动调用.
class Dotenv
{
    protected $filePath;

    protected $loader;

    public function __construct($path,$file='.env')
    {
        $this->filePath = $this->getFilePath($path,$file);
        //echo $this->filePath; // H:\xampp\htdocs\lu\bootstrap/../\.env
        $this->loader = new Loader($this->filePath,true);
    }

    //获得 .env的文件路径
    protected function getFilePath($path,$file)
    {
        //如果$file不是一个字符串
        if (!is_string($file)){
            //复制为.env
            $file = '.env';
        }

        //拼接路径 , DIRECTORY_SEPARATOR=> /
        $filePath = rtrim($path,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$file;

        return $filePath;

    }

    public function load()
    {
        return $this->loadData();
    }

    
    protected function loadData($overload = false)
    {
        $this->loader = new Loader($this->filePath, !$overload);
        //echo get_class($this->loader); //Dotenv\Loader
        // Dotenv\Loader->load();
        return $this->loader->load();
    }


}