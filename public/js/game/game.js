var Game = new function () {
    var loading = true,
        timeoutId = null,
        stop = false,
        game

    this.init = function (g) {
        if (loading) {
            game = g
            map = $('#map')
            coord = $('#coord')

            //console.log(g.turnHistory)
            loading = false

            Units.init(game.units)
            Terrain.init(game.terrain)
            Three.init()
            Picker.init(Three.getCamera(), Three.getRenderer().domElement)
            Fields.init(game.fields)
            Turn.init(game.turnHistory)
            Players.init(game.players)
            Gui.init()
            Timer.init(game.begin, game.turnTimeLimit, game.timeLimit)
            Ruins.init(game.ruins)
            Me.init(game.color, game.gold, game.bSequence)

            Chat.init(game.chatHistory)
        }

        Players.initOnline(game.online)

        if (Turn.isMy()) {
            Me.turnOn()
            if (!Me.getTurnActive()) {
                Websocket.startMyTurn()
            }
        } else {
            Me.turnOff()
        }

        Sound.play('gamestart')
    }
    this.getTimeoutId = function () {
        return timeoutId
    }
    this.setTimeoutId = function (value) {
        timeoutId = value
    }
    this.getCapitalId = function (color) {
        return game.capitals[color]
    }
    this.getStop = function () {
        return stop
    }
    this.getTurnsLimit = function () {
        return game.turnsLimit
    }
    this.getFirstUnitId = function () {
        return game.firstUnitId
    }
}

$(document).ready(function () {
    Websocket.init();
})