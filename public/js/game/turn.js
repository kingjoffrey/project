var Turn = new function () {
    var number = null,
        color = null,
        beginDate = null

    this.init = function (turnHistory) {
        var j = 0,
            history = {}

        for (var i in turnHistory) {
            var date = turnHistory[i].date.substr(0, 19);
            history[j] = {
                shortName: turnHistory[i].shortName,
                number: turnHistory[i].number,
                start: date
            }

            if (isSet(history[j - 1])) {
                history[j - 1]['end'] = date
            }

            j++;
        }

        for (var i in history) {
            Timer.append(history[i].shortName, history[i].number, history[i].start, history[i].end)
        }

        number = turnHistory[i].number
        color = turnHistory[i].shortName
        beginDate = Date.parse(turnHistory[i].date.substr(0, 19)).getTime()
    }
    this.getNumber = function () {
        return number
    }
    this.getColor = function () {
        return color
    }
    this.getBeginDate = function () {
        return beginDate
    }
    this.change = function (c, nr) {
        if (!c) {
            console.log('Turn "color" not set');
            return
        }

        Players.rotate(c)
        beginDate = (new Date()).getTime()
        Timer.update()

        color = c

        if (isSet(nr)) {
            number = nr
        }

        Timer.append(color, number)
        Players.drawTurn()

        if (Turn.isMy()) {
            Me.turnOn()
            Websocket.startMyTurn();
            return
        } else {
            Me.turnOff()
            return
        }
    }
    this.isMy = function () {
        if (Me.colorEquals(color)) {
            return true
        }
    }
    this.next = function () {
        var id = Message.show(translations.nextTurn, $('<div>').html(translations.areYouSure))
        Message.ok(id, Websocket.nextTurn);
        Message.cancel(id)
    }
}
