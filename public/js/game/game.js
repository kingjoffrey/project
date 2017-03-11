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

            PickerCommon.reset()
            GameScene.clear()
            GameRenderer.clear()

            Execute.setExecuting(0)
            Units.init(game.units)
            Fields.init(game.fields)
            Players.init(game.players)
            Me.init(game.color, game.gold, game.bSequence, game.capitals)
            Turn.change(game.turnColor, game.turnNumber)
            Ruins.init(game.ruins)

            GameGui.init()
            GameScene.initSun(Fields.getMaxY())
            GameRenderer.start()

            PickerCommon.init(PickerGame)

            $('#loading').hide()
            $('#game').show()
            $('.game').show()
        }

        if (Turn.isMy() && Me.getTurnActive()) {
            Turn.start(game.color)
        }

        Sound.play('gamestart')
    }
}
