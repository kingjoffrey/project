var Game = new function () {
    var loading = true,
        timeoutId = null,
        stop = false,
        game

    this.init = function (g) {
        if (loading) {
            game = g
            loading = false

            Units.init(game.units)
            Terrain.init(game.terrain)
            Three.init()
            Fields.init(game.fields)
            Gui.init()
            Turn.init(game.turnHistory)
            Players.init(game.players)
            Timer.init(game.begin, game.turnTimeLimit, game.timeLimit)
            Ruins.init(game.ruins)
            Me.init(game.me)

            Chat.init(game.chatHistory)
        }

        //Players.updateOnline()

        if (Turn.isMy()) {
            Me.turnOn()
            if (!Me.getTurnActive()) {
                Websocket.startMyTurn()
            }
        } else {
            Me.turnOff()
            if (Players.get(Turn.getColor()).isComputer()) {
                setTimeout('Websocket.computer()', 1000)
            }
        }

        //Sound.play('gamestart')
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
}

$(document).ready(function () {
    Websocket.init();
})