<?php

class Cli_Model_Test1 extends Thread
{
    public function run()
    {
        for ($i = 0; $i < 2; $i++) {
            echo 1 . date(' H:i:s') . "\n";
            sleep(10);
        }
    }
}
