<?php
date_default_timezone_set('Europe/Warsaw');

class Aaa
{
    public $aaa = array();
    public $a = 20;

    function a()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->aaa[$i] = new Bbb($i);
        }
    }

    function get()
    {
        return $this->a;
    }

    function loop()
    {
        $value = 10;
        echo date('H:i;s', time()) . "\n";

        for ($i = 0; $i < 1000000000; $i++) {
            if ($this->a >= $value) {

            }
        }

        echo date('H:i;s', time()) . "\n";
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

$value = 10;
$Aaa = new Aaa();

$Aaa->loop();

//print_r(1);
echo date('H:i;s', time()) . "\n";

for ($i = 0; $i < 1000000000; $i++) {
    if ($Aaa->get() >= $value) {

    }
}

echo date('H:i;s', time()) . "\n";

$arr = array('Aaa' => 20);

//print_r(2);
echo date('H:i;s', time()) . "\n";

for ($i = 0; $i < 1000000000; $i++) {
    if ($arr['Aaa'] >= $value) {

    }
}

echo date('H:i;s', time()) . "\n";

//for ($i = 0; $i < 10; $i++) {
//    $Aaa->aaa[$i]->b();
//}

