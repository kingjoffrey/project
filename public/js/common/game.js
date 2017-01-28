"use strict"
var Game = new function () {
    var loading = 1,
        timeoutId = null,
        game,
        touch = 0,
        gameId

    this.setGameId = function (id) {
        gameId = id
    }
    this.getGameId = function () {
        return gameId
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
    this.hasTouch = function () {
        return touch
    }
    this.init = function (g) {
        game = g
        if (loading) {
            loading = 0
            AStar.init()
            GameScene.init($(window).innerWidth(), $(window).innerHeight())
            Units.init(game.units)
            Terrain.init(game.terrain)
            Fields.init(game.fields)
            Turn.init(game.turnHistory)
            Players.init(game.players)
            Timer.init(game.begin, game.turnTimeLimit, game.timeLimit)
            Ruins.init(game.ruins)
            CommonMe.init(game.color, game.gold, game.bSequence, game.capitals)
            GameRenderer.init('game', GameScene)
            GameScene.initSun(Fields.getMaxY())
            GameRenderer.animate()
            BattleScene.init($(window).innerWidth(), $(window).innerHeight())
            BattleScene.initSun(Fields.getMaxY())
            GameGui.init()
            PickerCommon.init(PickerGame)
        }

        if (Turn.isMy()) {
            CommonMe.turnOn()
            if (!CommonMe.getTurnActive()) {
                WebSocketSendCommon.startMyTurn()
            }
        } else {
            CommonMe.turnOff()
        }
        Players.showFirst(Turn.getColor())

        Sound.play('gamestart')
        $('#loading').css('display', 'none')

        if (isSet(window.orientation)) {
            touch = 'ontouchstart' in document.documentElement
        }
    }
}
