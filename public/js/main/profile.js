"use strict"
var ProfileController = new function () {
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
    }
    this.show = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
        $('.trlink').click(function () {
            var id = $(this).attr('id')
            WebSocketSendMain.controller('over', 'index', {'id': id})
        })
    }
}