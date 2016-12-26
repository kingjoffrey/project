"use strict"
var GameController = new function () {
    this.index = function (r) {
        var main = $('#main')
        Main.setMain(main.html())
        main.html(r.data)

        Game.setGameId(r.gameId)

        WebSocketExecGame.init()
    }
}