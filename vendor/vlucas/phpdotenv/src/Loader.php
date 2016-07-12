<?php

namespace Dotenv;
use Dotenv\Exception\InvalidPathException;
use Dotenv\Exception\InvalidFileException;

class Loader
{
    protected $filePath;

    protected $immutable;

    //赋值
    public function __construct($filePath,$immutable = false)
    {
        $this->filePath = $filePath;
        $this->immutable = $immutable;
    }

    public function load()
    {
        //确认文件是否可读
        $this->ensureFileIsReadable();

        $filePath = $this->filePath;
        //从文件读取行
        $lines = $this->readLinesFromFile($filePath);

        foreach ($lines as $line){

            // 不是已#开头的，并且有 = 的
            if (!$this->isComment($line) && $this->looksLikeSetter($line)){
                $this->setEnvironmentVariable($line);
            }
        }

        return $lines;
    }

    protected function setEnvironmentVariable($name,$value=null)
    {
        //$name -> APP_ENV=local 这样的字符串
        list($name,$value) = $this->normaliseEnvironmentVariable($name,$value);

        //var_dump($name);
        //var_dump($this->getEnvironmentVariable($name));
        if ($this->immutable && $this->getEnvironmentVariable($name) !== null){
            return;
        }

        if (function_exists('apache_getenv') && function_exists('apache_setenv') && apache_getenv($name)) {
            apache_setenv($name, $value);
        }
        //设置环境变量的值APP_ENV=local，环境变量仅存活于当前请求期间
        putenv("$name=$value");

        //var_dump(getenv('APP_DEBUG'));
        
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;

    }

    public function getEnvironmentVariable($name)
    {
        switch (true)
        {
            case array_key_exists($name, $_ENV):
                return $_ENV[$name];
            case array_key_exists($name, $_SERVER):
                return $_SERVER[$name];
            default:
                $value = getenv($name);
                return $value === false ? null : $value;
        }
    }


    protected function normaliseEnvironmentVariable($name, $value)
    {
        //用 = 切分配置文件 APP_ENV=local
        list($name,$value) = $this->splitCompoundStringIntoParts($name,$value);
        //将export  或者 ' 或者 " 替换成 空字符
        list($name, $value) = $this->sanitiseVariableName($name, $value);
        list($name, $value) = $this->sanitiseVariableValue($name, $value);

        return [$name,$value];
    }

    protected function sanitiseVariableValue($name,$value)
    {
        $value = trim($value);
        if (!$value){ //$value 为false 或者0直接返回
            return [$name,$value];
        }

        //是否以 " 或者 ' 开头
        if ($this->beginsWithAQuote($value)){
            //如果是
            $quote = $value[0];
            $regexPattern = sprintf(
                '/^
                %1$s          # match a quote at the start of the value
                (             # capturing sub-pattern used
                 (?:          # we do not need to capture this
                  [^%1$s\\\\] # any character other than a quote or backslash
                  |\\\\\\\\   # or two backslashes together
                  |\\\\%1$s   # or an escaped quote e.g \"
                 )*           # as many characters that match the previous rules
                )             # end of the capturing sub-pattern
                %1$s          # and the closing quote
                .*$           # and discard any string after the closing quote
                /mx',
                $quote
            );
            $value = preg_replace($regexPattern, '$1', $value);
            $value = str_replace("\\$quote", $quote, $value);
            $value = str_replace('\\\\', '\\', $value);
        } else {

            $parts = explode(' #', $value, 2);
            $value = trim($parts[0]);
            //如果匹配到了空格，比如 $value = 'loc al';
            if (preg_match('/\s+/', $value) == 1) {
                throw new InvalidFileException('Dotenv values containing spaces must be surrounded by quotes.');
            }
        }
        //echo $name.'-------'.$value."<br/>"; APP_DEBUG-------true
        return array($name, trim($value));
    }

    //在$value[0] 中寻找 字符 " 或者 '
    protected function beginsWithAQuote($value)
    {
        //strpbrk 在字符串中查找一组字符的任何一个字符
        return strpbrk($value[0],'"\'') !== false;
    }


    protected function sanitiseVariableName($name,$value)
    {
        //将export  或者 ' 或者 " 替换成 空字符
        $name = trim(str_replace(['export ','\'','"'],'',$name));
        return [$name,$value];
    }

    //切分字符串成数组
    protected function splitCompoundStringIntoParts($name,$value)
    {
        //能否找到 =
        if (strpos($name, '=') !== false){
            // $name 已 = 切分为2个数组,并且每个元素去掉空格
            list($name,$value) = array_map('trim',explode('=',$name,2));
        }
        return [$name,$value];
    }


    protected function looksLikeSetter($line)
    {
        //能找到 = 字符
        return strpos($line, '=') !== false;
    }

    protected function isComment($line)
    {
        //去掉开头的空格，查找第一个字符是否为0
        return strpos(ltrim($line),'#') === 0;
    }

    //读取H:\xampp\htdocs\lu\bootstrap/../\.env 文件
    protected function readLinesFromFile($filePath)
    {
        //当设为 On 时，PHP 将检查通过 fgets() 和 file() 取得的数据中的行结束符号是符合 Unix，MS-DOS，还是 Macintosh 的习惯。
        //auto_detect_line_endings 默认关闭
        $autodetect = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', '1'); //开启
        //把H:\xampp\htdocs\lu\bootstrap/../\.env 文件内容读入数组中
        //FILE_IGNORE_NEW_LINES ->在数组每个元素的末尾不要添加换行符
        //FILE_SKIP_EMPTY_LINES ->空行则跳过不读取

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        ini_set('auto_detect_line_endings', $autodetect); //还原
        return $lines;
    }


    //确认文件是否可读
    protected function ensureFileIsReadable()
    {
        //$this->filePath; -> H:\xampp\htdocs\lu\bootstrap/../\.env
        //如果文件不可读 或者 找不到此文件 抛出异常
        if (!is_readable($this->filePath) || !is_file($this->filePath)){
            //echo $this->filePath,' not found';
            throw new InvalidPathException(sprintf('no xx Unable to read the environment file at %s.', $this->filePath));
        }
    }






}
