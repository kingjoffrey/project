"use strict"
var GameController = new function () {
    this.index = function (r) {
        $('#bg').hide()
        $('.editor').hide()
        $('#loading').show()
        Game.setGameId(r.gameId)
        WebSocketExecGame.init()
    }
}