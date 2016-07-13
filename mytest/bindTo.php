<?php

class A
{

    function __construct($val)
    {
        $this->val = $val;
    }

    function getClosure()
    {
        return function(){return $this->val;};
    }
}

$ob1 = new A(1);
$cl = $ob1->getClosure();
echo $cl(),"<br/>";


$ob2 = new A(2);
$cl = $cl->bindTo($ob2); //闭包指定作用域在$ob2对象上。
echo $cl()."<br/>"; //2
