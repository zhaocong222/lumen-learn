<?php
header("Content-type:text/html;charset=utf-8");

class Yifang
{
    public $title = '武汉亿房网';
    public $tpl = 'tpl1.php';

}

class Bentian
{
    public $title = '东风本田';
    public $tpl = 'tpl2.php';
}


class views
{

    public function render($obj)
    {
        $name = $obj->tpl;
        $closure = function() use($name){
            include './tpl/'.$name;
        };

        $closure = $closure->bindTo($obj);
        return $closure;

    }

}

$View = new views();
call_user_func($View->render(new Bentian()));
call_user_func($View->render(new Yifang()));



