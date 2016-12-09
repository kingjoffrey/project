"use strict"
var Init = new function () {
    this.init = function (g) {
        Game.init(g)
    }
}
$(document).ready(function () {
    AStar.init()
    WebSocketPCNTL.init()
    PrivateChat.init()
})