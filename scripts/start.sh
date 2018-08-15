#!/bin/sh

if [ $(/usr/bin/pidof php| /usr/bin/wc -w) -gt 0 ]
then
    exit
else
    data=`date +%Y%m%d`
    czas=`date +%H.%M.%S`
    path=`pwd`


    export APPLICATION_ENV=cli

    echo "Starting..."
    /bin/cp $path/../log/1_main.log $path/../log/$data-$czas.wsMainServer.log
    /bin/cp $path/../log/1_editor.log $path/../log/$data-$czas.wsEditorServer.log

    cd $path
    
    /usr/bin/php -f ./wsMainServer.php &>$path/../log/1_main.log &
    /usr/bin/php -f ./wsEditorServer.php &>$path/../log/1_editor.log &

    #/usr/bin/php -c /etc/php-cli/ -f $path/wsMainServer.php &>$path/1_.log &
fi
