"use strict"
var LoadController = new function () {
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
        $('.trlink').click(function () {
            WebSocketSendMain.controller('game', 'index', {'gameId': $(this).attr('id')})
        })
    }
}