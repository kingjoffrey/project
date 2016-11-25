<?php
date_default_timezone_set('Europe/Warsaw');
///**
// * ver. 0001
// */
//date_default_timezone_set('Europe/Warsaw');
//
//class Aaa
//{
//    public $aaa = array();
//    public $a = 20;
//
//    function a()
//    {
//        for ($i = 0; $i < 10; $i++) {
//            $this->aaa[$i] = new Bbb($i);
//        }
//    }
//
//    function getFirstBbb()
//    {
//        return $this->aaa[0];
//    }
//
//    function get()
//    {
//        return $this->a;
//    }
//
//    function loop()
//    {
//        $value = 10;
//        echo date('H:i;s', time()) . "\n";
//
//        for ($i = 0; $i < 1000000000; $i++) {
//            if ($this->a >= $value) {
//
//            }
//        }
//
//        echo date('H:i;s', time()) . "\n";
//    }
//}
//
//class Bbb
//{
//    private $i;
//
//    public function __construct($i)
//    {
//        $this->i = $i;
//    }
//
//    function b()
//    {
//        echo 'b' . $this->i . "\n";
//    }
//
//    function setI($i)
//    {
//        $this->i = $i;
//    }
//}
//
//$Aaa = new Aaa();
//$Aaa->a();
//
//$Bbb = $Aaa->getFirstBbb();
//
//$Bbb->b();
//$Bbb->setI(1);
//$Bbb->b();
//$Aaa->getFirstBbb()->b();
//$Aaa->getFirstBbb()->setI(2);
//$Bbb->b();

#echo phpinfo();

class STD extends Thread
{
    public function put()
    {
        $this->synchronized(function () {
            for ($i = 0; $i < 7; $i++) {

                printf("%d\n", $i);
                $this->notify();
                if ($i < 6)
                    $this->wait();
                else
                    exit();
                sleep(1);
            }
        });
    }

    public function flush()
    {
        $this->synchronized(function () {
            for ($i = 0; $i < 7; $i++) {
                flush();
                $this->notify();
                if ($i < 6)
                    $this->wait();
                else
                    exit();
            }
        });
    }
}

class A extends Thread
{
    private $std;

    public function __construct($std)
    {
        $this->std = $std;
    }

    public function run()
    {
        $this->std->put();
    }
}

class B extends Thread
{
    private $std;

    public function __construct($std)
    {
        $this->std = $std;
    }

    public function run()
    {
        $this->std->flush();
    }
}

//ob_end_clean();
//echo str_repeat(" ", 1024);
//$std = new STD();
//$ta = new A($std);
//$tb = new B($std);
//$ta->start();
//$tb->start();

class workerThread extends Thread
{
    public function __construct($i)
    {
        $this->i = $i;
    }

    public function run()
    {
        while (true) {
            echo $this->i . date(' Y-m-d H:i:s ') . "\n";
            sleep(rand(0, 10));
        }
    }
}

for ($i = 0; $i < 10; $i++) {
    $workers[$i] = new workerThread($i);
    $workers[$i]->start();
}

?>
