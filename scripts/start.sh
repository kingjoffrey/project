#!/bin/sh

if [ $(/usr/bin/pidof php| /usr/bin/wc -w) -gt 0 ]
then
    exit
else
    data=`date +%Y%m%d`
    czas=`date +%H.%M.%S`

    export APPLICATION_ENV=cli

    echo "Starting..."
    /bin/cp ../log/_main.log ../log/$data-$czas.wsMainServer.log
    /bin/cp ../log/_editor.log ../log/$data-$czas.wsEditorServer.log
    /bin/cp ../log/_privateChat.log ../log/$data-$czas.wsPrivateChatServer.log
    /bin/cp ../log/_openGames.log ../log/$data-$czas.wsOpenGamesServer.log
    /bin/cp ../log/_exec.log ../log/$data-$czas.wsExecServer.log

    /usr/bin/php -f ./wsMainServer.php &>../log/_main.log &
    /usr/bin/php -f ./wsEditorServer.php &>../log/_editor.log &
    /usr/bin/php -f ./wsOpenGamesServer.php &>../log/_openGames.log &
    /usr/bin/php -f ./wsPrivateChatServer.php &>../log/_privateChat.log &
    /usr/bin/php -f ./wsExecServer.php &>../log/_execChat.log &
fi
