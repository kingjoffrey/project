"use strict"
var Init = new function () {
    this.init = function (g) {
        Game.init(g)
    }
}
$(document).ready(function () {
    AStar.init()
    WebSocketGame.init()
    PrivateChat.init()
})