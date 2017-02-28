"use strict"
var HalloffameController = new function () {
    var playerId
    this.index = function (r) {
        $('#content').html(r.data)

        $('.trlink').click(function () {
            playerId = $(this).attr('id')
            WebSocketSendMain.controller('profile', 'show', {'id': playerId})
        })
    }
}