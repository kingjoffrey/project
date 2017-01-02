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
    this.getMapElement = function () {
        return $('#map')
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
    this.hasTouch = function () {
        return touch
    }
    this.init = function (g) {
        game = g
        if (loading) {
            loading = 0
            GameScene.init($(window).innerWidth(), $(window).innerHeight())
            Units.init(game.units)
            Terrain.init(game.terrain)
            Fields.init(game.fields, game.map.mapId)
            Turn.init(game.turnHistory)
            MiniMap.init(Game.getMapElement())
            Players.init(game.players)
            GamePlayers.init(game.players)
            Timer.init(game.begin, game.turnTimeLimit, game.timeLimit)
            Ruins.init(game.ruins)
            CommonMe.init(game.color, game.gold, game.bSequence)
            Chat.init(game.chatHistory)
            GameRenderer.init('game', GameScene)
            GameScene.initSun(Fields.getMaxY())
            GameRenderer.animate()
            BattleScene.init($(window).innerWidth(), $(window).innerHeight())
            BattleScene.initSun(Fields.getMaxY())
            GameGui.init()
            PickerCommon.init(PickerGame)
        }
        GamePlayers.initOnline(game.online)
        if (Turn.isMy()) {
            CommonMe.turnOn()
            if (!CommonMe.getTurnActive()) {
                WebSocketSendCommon.startMyTurn()
            }
        } else {
            CommonMe.turnOff()
        }
        Players.showFirst(Turn.getColor())

        if (Players.countHumans() > 1) {
            PrivateChat.setType('game')
            PrivateChat.enable()
        } else {
            PrivateChat.disable()
        }

        Sound.play('gamestart')
        $('#loading').css('display', 'none')

        if (typeof window.orientation !== 'undefined') {
            touch = 'ontouchstart' in document.documentElement
        }
    }
}
