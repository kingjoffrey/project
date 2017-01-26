#!/bin/sh

if [ $(/usr/bin/pidof php| /usr/bin/wc -w) -gt 0 ]
then
    exit
else
    data=`date +%Y%m%d`
    czas=`date +%H.%M.%S`
    path=$HOME"/htdocs/scripts"


    export APPLICATION_ENV=cli

    echo "Starting..."
    /bin/cp $path/../log/1_.log $path/../log/$data-$czas.1_.log
    
    cd $path
    
    /usr/bin/php -f ./wsServer.php &>$path/../log/1_.log &
    #/usr/bin/php -c /etc/php-cli/ -f $path/wsServer.php &>$path/1_.log &
fi
