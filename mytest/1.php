<?php
namespace mytest;
class test1
{
    public function __construct()
    {
        echo 1231;
    }
}

//并没有实例化，只是返回类名(含命名空间)
echo \mytest\test1::class;