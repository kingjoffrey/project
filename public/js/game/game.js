"use strict"
var Game = new function () {
    var loading = 1,
        game,
        gameId

    this.setGameId = function (id) {
        gameId = id
    }
    this.getGameId = function () {
        return gameId
    }
    this.getCapitalId = function (color) {
        return game.capitals[color]
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
    this.resetLoading = function () {
        loading = 1
    }
    this.start = function (g) {
        game = g
        if (loading) {
            loading = 0
            Execute.setExecuting(0)
            GameScene.init($(window).innerWidth(), $(window).innerHeight())
            Units.init(game.units)
            Terrain.init(game.terrain)
            Fields.init(game.fields)
            Players.init(game.players)
            Turn.init(game.turnColor, game.turnNumber)
            Ruins.init(game.ruins)
            Me.init(game.color, game.gold, game.bSequence, game.capitals)
            GameRenderer.init('game', GameScene)
            GameScene.initSun(Fields.getMaxY())
            GameRenderer.animate()
            GameGui.init()
            PickerCommon.init(PickerGame)

            $('#loading').hide()
            $('#game').show()
            $('.game').show()
        }

        if (Turn.isMy() && !Me.getTurnActive()) {
            WebSocketSendGame.startMyTurn()
        }

        Players.showFirst(Turn.getColor())
        Sound.play('gamestart')
    }
}
