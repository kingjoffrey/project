<?php
namespace MyApp;
class Test2 extends \Thread
{
    public function __construct($data)
    {
    }

    public function run()
    {
        echo '#just Test2 run' . date(' H:i:s') . "\n";
    }
}
