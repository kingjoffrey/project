"use strict"
var LoadController = new function () {
    var gameId
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
        $('.trlink').click(function () {
            gameId = $(this).attr('id')
            WebSocketSendMain.controller('game', 'index')
        })
    }
    this.getGameId = function () {
        return gameId
    }
}