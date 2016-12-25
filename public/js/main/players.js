"use strict"
var PlayersController = new function () {
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
        $('form#search').submit(function (e) {
            e.preventDefault()
            var search = $('input#search').val()
            WebSocketSendMain.controller('players', 'index', {'search': search})
        })
        $('#searchResults a').click(function () {
            var friendId = $(this).attr('id')
            WebSocketSendMain.controller('players', 'add', {'friendId': friendId})
        })
    }
}