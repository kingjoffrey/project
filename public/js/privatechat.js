var PrivateChat = new function () {
    var inputWidth

    this.init = function () {
        Websocket.init()
        inputWidth = $('#chatBox #msg').width()
        $('#send').click(function () {
            Websocket.chat()
        })
        $('#msg').keypress(function (e) {
            if (e.which == 13) {
                Websocket.chat()
            }
        })
        $('#chatBox .close').click(function () {
            if ($('#chatBox.mini').length) {
                PrivateChat.enable()
            } else {
                PrivateChat.disable()
            }
        })
        $('#chatBox #chatTitle').click(function () {
            if (type == 'default') {
                PrivateChat.disable()
            } else {
                $('#chatBox #friendId').val('')
                $(this).html('')
                $('#chatBox #msg')
                    .css({
                        'padding-left': '5px',
                        width: inputWidth
                    })
            }
        })
        $('#friends span').click(function () {
            if ($('#chatBox.disabled #msg').val() == translations.selectFriendFromFriendsList) {
                $('#chatBox.disabled #msg').val('')
            }
            PrivateChat.prepare($(this).html(), $(this).parent().attr('id'))
        })
        $('#friends #trash').click(function () {
            Websocket.delete($(this).parent().attr('id'))
            $(this).parent().remove()
        })
        this.disable()
    }
    this.message = function (to, chatTitle, msg) {
        switch (to) {
            case 2:
                var prepend = ''
                break
            case 1:
                var prepend = translations.to + ' '
                $('#msg').val('')
                break
            default:
                var prepend = translations.from + ' '
        }
        $('#chatContent').append(prepend + chatTitle + ': ' + msg + '<br/>')
        $('#chatWindow').animate({scrollTop: $('#chatWindow div')[0].scrollHeight}, 100)
    }
    this.enable = function () {
        $('#chatBox #msg').prop('disabled', false)
        $('#chatBox.disabled #msg').val('')
        $('#chatBox').removeClass('mini disabled')
    }
    this.disable = function () {
        if (type == 'default') {
            $('#chatBox').addClass('disabled')
            $('#chatBox #msg')
                .prop('disabled', true)
                .val(translations.selectFriendFromFriendsList)
                .css({
                    'padding-left': '5px',
                    width: inputWidth
                })
        } else {
            $('#chatBox').addClass('mini')
        }
    }
    this.prepare = function (name, friendId) {
        this.enable()
        if (isSet(name)) {
            $('#chatBox #chatTitle').html(name)
            if (isSet(friendId)) {
                $('#chatBox #friendId').val(friendId)
            }
            var padding = $('#chatBox #chatTitle').width() + 10
            $('#chatBox #msg')
                .prop('disabled', false)
                .focus()
                .css({
                    'padding-left': padding,
                    width: inputWidth - padding
                })
        }
    }
}

var Websocket = new function () {
    var closed = true,
        ws

    this.open = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'open',
            playerId: id,
            langId: langId,
            name: playerName,
            accessKey: accessKey
        }

        ws.send(JSON.stringify(token));
    }
    this.close = function () {
        setTimeout('Websocket.init()', 1000)
    }
    this.init = function () {
        ws = new WebSocket(wsURL + '/chat')

        ws.onopen = function () {
            closed = false
            Websocket.open()
        }

        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data);

            console.log(r)
            switch (r.type) {
                case 'notification':
                    if (!parseInt(r.count)) {
                        return
                    }
                    $('#envelope').html($('<span>').html(r.count))
                    break
                case 'chat':
                    PrivateChat.prepare()
                    PrivateChat.message(0, r.name, r.msg)
                    break
                case 'open':
                    $('#friends #' + r.id + ' #online').css({display: 'block'})
                    break
                case 'close':
                    $('#friends #' + r.id + ' #online').css({display: 'none'})
                    break
                case 'friends':
                    for (var i in r.friends) {
                        $('#friends #' + r.friends[i] + ' #online').css({display: 'block'})
                    }
                    break
                default:
                    console.log(r);
            }
        }

        ws.onclose = function () {
            closed = true
            Websocket.close()
        }
    }
    this.chat = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        if ($('#chatBox.disabled').length) {
            return
        }

        var msg = $('#msg').val()

        if (!msg) {
            return
        }

        var friendId = $('#chatBox #friendId').val(),
            chatTitle = $('#chatBox #chatTitle').html()

        if (friendId) {
            PrivateChat.message(1, chatTitle, msg)

            var token = {
                type: 'chat',
                friendId: friendId,
                msg: msg
            }

            ws.send(JSON.stringify(token))
        } else {
            switch (type) {
                case 'new':
                    PrivateChat.message(2, playerName, msg)
                    $('#msg').val('')
                    New.chat(msg)
                    break
                case 'setup':
                    PrivateChat.message(2, playerName, msg)
                    $('#msg').val('')
                    Setup.chat(msg)
                    break
                case 'game':
                    WebSocketGame.chat()
                    break
                default:
                    console.log(msg)
            }
        }
    }
    this.delete = function (playerId) {
        var token = {
            type: 'delete',
            playerId: playerId
        }

        ws.send(JSON.stringify(token))
    }
}
