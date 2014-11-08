// *** TURN ***

var Turn = {
    number: null,
    color: null,
    beginDate: null,
    init: function () {
        var j = 0,
            history = {}

        for (i in game.turnHistory) {
            var date = game.turnHistory[i].date.substr(0, 19);
            history[j] = {
                shortName: game.turnHistory[i].shortName,
                number: game.turnHistory[i].number,
                start: date
            }

            if (isSet(history[j - 1])) {
                history[j - 1]['end'] = date
            }

            j++;
        }

        for (i in history) {
            timer.append(history[i].shortName, history[i].number, history[i].start, history[i].end)
        }

        timer.scroll()
        this.number = game.turnHistory[i].number
        this.color = game.turnHistory[i].shortName
        this.beginDate = Date.parse(game.turnHistory[i].date.substr(0, 19)).getTime()
    },
    on: function () {
        makeMyCursorUnlock();
        Army.skippedArmies = {};
        $('#nextTurn').removeClass('buttonOff');
        $('#nextArmy').removeClass('buttonOff');
        Castle.showFirst();
        Message.turn();
        titleBlink('Your turn!');
        if (!Hero.findMy()) {
            $('#heroResurrection').removeClass('buttonOff')
        }
        if (my.gold > 1000) {
            $('#heroHire').removeClass('buttonOff')
        }
    },
    off: function () {
        Army.deselect();
        $('#nextTurn').addClass('buttonOff')
        $('#nextArmy').addClass('buttonOff')
        $('#heroResurrection').addClass('buttonOff')
        $('#heroHire').addClass('buttonOff')
        makeMyCursorLock();
    },
    change: function (color, nr) {
        if (!color) {
            console.log('Turn "color" not set');
            return;
        }

        Players.rotate(color)
        this.beginDate = (new Date()).getTime()
        timer.update()

        Turn.color = color;

        if (isSet(nr)) {
            Turn.number = nr;
        }

        timer.append(Turn.color, Turn.number)
        Players.drawTurn();

        if (Turn.color == game.me.color) {
            Turn.on();
            Websocket.startMyTurn();
            return;
        } else {
            Turn.off();
            return;
        }
    }
}
