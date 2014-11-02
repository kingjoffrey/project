<?php

class Aaa
{
    public $aaa = array();

    function a()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->aaa[$i] = new Bbb($i);
        }
    }
}

class Bbb
{
    private $i;

    public function __construct($i)
    {
        $this->i = $i;
    }

    function b()
    {
        echo 'b' . $this->i;
    }
}

$Aaa = new Aaa();
$Aaa->a();

for ($i = 0; $i < 10; $i++) {
    $Aaa->aaa[$i]->b();
}

