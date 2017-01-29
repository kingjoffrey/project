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
            WebSocketSendMain.controller('players', 'index', {'search': $('input#search').val()})
        })
        $('#searchResults a').click(function () {
            WebSocketSendMain.controller('friends', 'add', {'id': $(this).attr('id')})
        })
    }
}