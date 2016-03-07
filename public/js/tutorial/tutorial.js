"use strict"
var Game = new function () {
    var loading = 1,
        timeoutId = null,
        game

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
    this.init = function (g) {
        if (Models.getLoading() < 17) {
            setTimeout(function () {
                Game.init(g)
            }, 500)
            return
        }
        game = g
        if (loading) {
            loading = 0

            Units.init(game.units)
            Terrain.init(game.terrain)
            Fields.init(game.fields, game.map.mapId)
            Turn.init(game.turnHistory)
            Gui.init(game.map)
            Players.init(game.players)
            GamePlayers.init(game.players)
            Timer.init(game.begin, game.turnTimeLimit, game.timeLimit)
            Ruins.init(game.ruins)
            Me.init(game.color, game.gold, game.bSequence)
            Chat.init(game.chatHistory)
            Scene.initSun(Fields.getMaxY())
            Scene.render()
        }
        GamePlayers.initOnline(game.online)
        if (Turn.isMy()) {
            Me.turnOn()
            if (!Me.getTurnActive()) {
                WebSocketSend.startMyTurn()
            }
        } else {
            Me.turnOff()
        }
        Players.showFirst(Turn.getColor())

        if (Players.countHumans() > 1) {
            PrivateChat.setType('game')
            PrivateChat.enable()
        } else {
            PrivateChat.disable()
        }

        Sound.play('gamestart')
    }
}

$(document).ready(function () {
    AStar.init()
    Scene.init()
    WebSocketGame.init()
    PrivateChat.init()
})