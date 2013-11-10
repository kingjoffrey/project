// *** TURN ***

var Turn = {
    number: null,
    shortName: null,
    init: function () {
        for (i in turnHistory) {

        }

        this.number = turnHistory[i].number;
        this.shortName = turnHistory[i].shortName;
    },
    on: function () {
        makeMyCursorUnlock();
        skippedArmies = new Array();
        my.turn = true;
        $('#nextTurn').removeClass('buttonOff');
        $('#nextArmy').removeClass('buttonOff');
        showFirstCastle();
        Message.turn();
        titleBlink('Your turn!');
    },
    off: function () {
        my.turn = false;
        unselectArmy();
        $('#nextTurn').addClass('buttonOff');
        $('#nextArmy').addClass('buttonOff');
        makeMyCursorLock();
    },
    change: function (color, nr) {
        if (!color) {
            console.log('Turn "color" not set');
            return;
        }

        Turn.shortName = color;

        if (isSet(nr)) {
            Turn.number = nr;
        }

        Players.turn();

        timer.update();

        console.log(Turn.shortName);

        if (Turn.shortName == my.color) {
            Turn.on();
            Websocket.startMyTurn();
            return;
        } else {
            Turn.off();
            return;
        }
    }
}
