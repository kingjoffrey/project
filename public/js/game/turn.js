var Turn = new function () {
    var color = null,
        number = 0

    this.getColor = function () {
        return color
    }
    this.change = function (c, n) {
        color = c
        number = n

        if (Turn.isMy()) {
            Me.turnOn()
            WebSocketSendGame.startMyTurn()
            Execute.setExecuting(0)
        } else {
            Me.turnOff()
            var player = Players.get(color)
            $('#turnInfo')
                .html(translations.Waitingfor + ' ' + player.getLongName())
                .css({
                    'background': player.getBackgroundColor(),
                    'color': player.getTextColor()
                })
                .show()

            if (Players.get(color).isComputer()) {
                WebSocketSendGame.computer()
            }

            if (Players.get(color).isComputer() && !GameGui.getShow()) {
                Execute.setExecuting(0)
            } else {
                Players.showFirst(color, function () {
                    setTimeout(function () {
                        Execute.setExecuting(0)
                    }, 500)
                })
            }
        }
    }
    this.start = function (color) {
        if (Turn.isMy()) {
            if (!WebSocketTutorial.isOpen() && Turn.getNumber() == 1 && !Me.getCastle(Me.getFirsCastleId()).getProductionId()) {
                CastleWindow.show(Me.getCastle(Me.getFirsCastleId()))
                Players.showFirst(color, function () {
                    Execute.setExecuting(0)
                })
            } else if (Me.getArmies().count()) {
                if (!Me.findNext(1)) {
                    Players.showFirst(color, function () {
                        Execute.setExecuting(0)
                    })
                } else {
                    Execute.setExecuting(0)
                }
            } else {
                Players.showFirst(color, function () {
                    Execute.setExecuting(0)
                })
            }
        } else {
            Execute.setExecuting(0)
        }
    }
    this.isMy = function () {
        if (Me.colorEquals(color)) {
            return true
        }
    }
    this.getNumber = function () {
        return number
    }
    this.next = function () {
        var id = Message.simple(translations.nextTurn, translations.areYouSure)
        Message.addButton(id, 'Yes', WebSocketSendGame.nextTurn)
    }
}
