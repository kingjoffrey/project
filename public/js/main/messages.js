"use strict"
var MessagesController = new function () {
    var playerId = 0
    this.addThread = function (id, thread) {
        $('table').append(
            $('<tr>').attr('id', id).addClass('trlink')
                .append($('<td>').html(thread.name))
                .click(function () {
                    playerId = $(this).attr('id')
                    WebSocketSendMain.controller('messages', 'thread', {'id': playerId})
                })
        )
    }
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        console.log(r.threads)

        for (var id in r.threads) {
            this.addThread(id, r.threads[id])
        }
    }
    this.thread = function (r) {
        var content = $('#content'),
            data = r.data,
            messages = r.messages

        content.html(data)

        for (var i in messages) {
            $('.messages').append(
                $('<tr>').addClass('trlink')
                    .append($('<td>').addClass('date').html(messages[i].date + '<br>' + messages[i].name))
                    .append($('<td>').addClass('msg').html(messages[i].message))
            )
        }

        $('.table').append($('<div>').addClass('chat')
            .append($('<input>'))
            .append($('<div>').addClass('button buttonColors').html(translations.send).click(function () {
                var message = $('.chat input').val()
                if (message) {
                    $('.messages').append(
                        $('<tr>').addClass('trlink')
                            .append($('<td>').addClass('date').html(playerName))
                            .append($('<td>').addClass('msg').html(message))
                    )
                    $('.chat input').val('')
                    WebSocketSendChat.send(message, MessagesController.getPlayerId())
                }
            }))
        )
    }
    this.getPlayerId = function () {
        return playerId
    }
    this.setPlayerId = function (id) {
        playerId = id
    }
}