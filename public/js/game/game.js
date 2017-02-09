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
            AStar.init()
            GameScene.init($(window).innerWidth(), $(window).innerHeight())
            Units.init(game.units)
            Terrain.init(game.terrain)
            Fields.init(game.fields)
            Turn.init(game.turnColor, game.turnNumber)
            Players.init(game.players)
            Ruins.init(game.ruins)
            Me.init(game.color, game.gold, game.bSequence, game.capitals)
            GameRenderer.init('game', GameScene)
            GameScene.initSun(Fields.getMaxY())
            GameRenderer.animate()
            BattleScene.init($(window).innerWidth(), $(window).innerHeight())
            BattleScene.initSun(Fields.getMaxY())
            GameGui.init()
            PickerCommon.init(PickerGame)

            $('#loading').hide()
            $('#game').show()
            $('.game').show()
        }

        if (Turn.isMy()) {
            Me.turnOn()
            if (!Me.getTurnActive()) {
                WebSocketSendGame.startMyTurn()
            }
        } else {
            Me.turnOff()
        }
        Players.showFirst(Turn.getColor())

        Sound.play('gamestart')
    }
}
