"use strict"
var GameController = new function () {
    this.index = function (r) {
        Game.setGameId(r.gameId)
        WebSocketExecGame.init()
    }
}