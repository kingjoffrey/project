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
        Army.skippedArmies = {};
        Castle.showFirst();
        Message.turn();
        Gui.unlock()
        titleBlink('Your turn!');
        if (!Hero.findMy()) {
            $('#heroResurrection').removeClass('buttonOff')
        }
        if (game.me.gold > 1000) {
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

        if (Turn.isMy()) {
            Turn.on();
            Websocket.startMyTurn();
            return;
        } else {
            Turn.off();
            return;
        }
    },
    isMy: function () {
        if (Turn.color == game.me.color) {
            return true;
        }
    }
}
