"use strict"
var PlayController = new function () {
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
        $('#tutorial').click(function () {
            WebSocketSendMain.controller('tutorial', 'index')
        })
        $('#singlePlayer').click(function () {
            WebSocketSendMain.controller('single', 'index')
        })
        $('#joinGame').click(function () {
            WebSocketSendMain.controller('join', 'index')
        })
        $('#createGame').click(function () {
            WebSocketSendMain.controller('create', 'index')
        })
    }
}