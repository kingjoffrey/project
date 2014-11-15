<?php
/**
 * ver. 0001
 */
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

    function getFirstBbb()
    {
        return $this->aaa[0];
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
        echo 'b' . $this->i . "\n";
    }

    function setI($i)
    {
        $this->i = $i;
    }
}

$Aaa = new Aaa();
$Aaa->a();

$Bbb = $Aaa->getFirstBbb();

$Bbb->b();
$Bbb->setI(1);
$Bbb->b();
$Aaa->getFirstBbb()->b();
$Aaa->getFirstBbb()->setI(2);
$Bbb->b();
