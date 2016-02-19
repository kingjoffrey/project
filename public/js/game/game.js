var Game = new function () {
    var loading = true,
        timeoutId = null,
        stop = false,
        game

    this.init = function (g) {
        if (Models.getLoading() < 17) {
            setTimeout(function () {
                Game.init(g)
            }, 500)
            return
        }
        if (loading) {
            game = g
            map = $('#map')
            coord = $('#coord')

            loading = false

            Units.init(game.units)
            Terrain.init(game.terrain)
            Fields.init(game.fields, game.mapId)
            Turn.init(game.turnHistory)
            Players.init(game.players)
            Gui.init()
            Timer.init(game.begin, game.turnTimeLimit, game.timeLimit)
            Ruins.init(game.ruins)
            Me.init(game.color, game.gold, game.bSequence)
            Chat.init(game.chatHistory)
            Scene.initSun(Fields.getMaxY())
            Scene.render()
        }
        Players.initOnline(game.online)
        if (Turn.isMy()) {
            Me.turnOn()
            if (!Me.getTurnActive()) {
                WebSocketGame.startMyTurn()
            }
        } else {
            Me.turnOff()
        }
        Players.showFirst(Turn.getColor())

        if (Players.countHumans() > 1) {
            type = 'game'
            PrivateChat.enable()
        } else {
            PrivateChat.disable()
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
    this.getLoading = function () {
        return loading
    }
}

$(document).ready(function () {
    Scene.init()
    WebSocketGame.init()
    PrivateChat.init()
})