var Turn = {
    number: null,
    color: null,
    beginDate: null,
    init: function (game) {
        var j = 0,
            history = {}

        for (var i in game.turnHistory) {
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
        Players.drawTurn()

        if (Turn.isMy()) {
            Me.turnOn()
            Websocket.startMyTurn();
            return
        } else {
            Me.turnOff()
            return
        }
    },
    isMy: function () {
        if (Turn.color == Me.getColor()) {
            return true;
        }
    }
}
