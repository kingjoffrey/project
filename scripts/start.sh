#!/bin/sh

if [ $(/sbin/pidof php| /usr/bin/wc -w) -gt 0 ]
then
    exit
else
    data=`date +%Y%m%d`
    czas=`date +%H.%M.%S`
    path="/var/www/html/scripts"


    export APPLICATION_ENV=cli

    echo "Starting..."
    /bin/cp $path/1_.log $path/$data-$czas.1_.log
    /usr/bin/php -c /etc/php-cli/ -f $path/wsServer.php &>$path/1_.log &
fi
