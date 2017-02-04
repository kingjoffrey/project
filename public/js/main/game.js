"use strict"
var GameController = new function () {
    this.index = function (gameId) {
        $('#bg').hide()
        $('#loading').show()

        Game.setGameId(gameId)
        WebSocketExecGame.init()
    }
}