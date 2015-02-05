var Game = new function () {
    var loading = true,
        timeoutId = null,
        capitals

    this.init = function (game) {
        if (loading) {
            loading = false
            capitals = game.capitals

            Units.init(game.units)
            Terrain.init(game.terrain)
            Three.init()
            Fields.init(game.fields)
            Gui.init()
            Turn.init(game)
            Players.init(game.players)
            timer.start(game)
            Ruins.init(game.ruins)
            Me.init(game.me)

            renderChatHistory()
        }

        //Players.updateOnline()

        if (Turn.isMy()) {
            Me.turnOn()
            if (!Me.getTurnActive()) {
                Websocket.startMyTurn()
            }
        } else {
            Me.turnOff()
            if (Players.get(Turn.color).isComputer()) {
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
        return capitals[color]
    }
}

$(document).ready(function () {
    Websocket.init();
})