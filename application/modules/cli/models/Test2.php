<?php

class Cli_Model_Test2 extends Thread
{
    public function run()
    {
        echo 2 . date(' H:i:s') . "\n";
    }
}
