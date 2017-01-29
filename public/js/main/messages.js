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
            data = r.data,
            messages = r.messages

        content.html(data)

        for (var i in messages) {
            $('#messages').append(
                $('<tr>').addClass('trlink')
                    .append($('<td>').addClass('date').html(messages[i].date + '<br>' + messages[i].name))
                    .append($('<td>').addClass('msg').html(messages[i].message))
            )
        }

        $('.table')
            .append($('<textarea>'))
            .append($('<div>').addClass('button').html(translations.send).click(function () {
                WebSocketSendChat.send()
            }))

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
    }
}