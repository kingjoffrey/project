"use strict"
var HalloffameController = new function () {
    var playerId
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('.trlink').click(function () {
            playerId = $(this).attr('id')
            WebSocketSendMain.controller('profile', 'show', {'id': playerId})
        })
    }
}