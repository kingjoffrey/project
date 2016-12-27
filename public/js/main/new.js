"use strict"
var NewController = new function () {
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
        $('form').submit(function (e) {
            e.preventDefault()
            WebSocketSendMain.controller('new', 'setup', {
                'mapId': $('select#mapId').val(),
                'numberOfPlayers': $('input#numberOfPlayers').val(),
                'timeLimit': $('select#timeLimit').val(),
                'turnsLimit': $('input#turnsLimit').val(),
                'turnTimeLimit': $('select#turnTimeLimit').val()
            })
        })

        New.init()
    }
    this.setup = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
    }
}