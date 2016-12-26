"use strict"
var MessagesController = new function () {
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
        $('#threads .trlink').click(function () {
            WebSocketSendMain.controller('messages', 'thread', {'id': $(this).attr('id')})
        })
    }
    this.thread = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
    }
}