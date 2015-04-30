var PrivateChat = new function () {
    var inputWidth

    this.init = function () {
        Websocket.init()
        inputWidth = $('#chatBox input').width()
        $('#send').click(function () {
            Websocket.chat()
        })
        $('#msg').keypress(function (e) {
            if (e.which == 13) {
                Websocket.chat()
            }
        })
        $('#friends span').click(function () {
            if ($('#chatBox.mini #msg').val() == translations.selectFriendFromFriendsList) {
                $('#chatBox.mini #msg').val('')
            }
            PrivateChat.prepare($(this).html(), $(this).parent().attr('id'))
        })
        $('#friends #trash').click(function () {
            Websocket.delete($(this).parent().attr('id'))
            $(this).parent().remove()
        })
        $('#threads .trlink').click(function () {
            window.location = '/' + lang + '/messages/thread/id/' + $(this).parent().attr('id')
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
                break
            default:
                var prepend = translations.from + ' '
        }
        $('#chatContent').append(prepend + chatTitle + ': ' + msg + '<br/>')
    }
    this.enable = function () {
        $('#chatBox input').prop('disabled', false)
        $('#chatBox.mini #msg').val('')
    }
    this.disable = function () {
        $('#chatBox.mini #msg').prop('disabled', true).val(translations.selectFriendFromFriendsList)
    }
    this.prepare = function (name, friendId) {
        switch (type) {
            case 'game':
                if (Players.countHumans() > 1) {
                    this.enable()
                }
                break
            case 'default':

                break
            case 'new':
                this.enable()
                break
            case 'setup':
                this.enable()
                break
        }
        $('#chatBox').removeClass('mini')
        PrivateChat.addCloseClick()
        if (isSet(name)) {
            $('#chatBox #chatTitle').html(name)
            if (isSet(friendId)) {
                $('#chatBox #friendId').val(friendId)
            }
            var padding = $('#chatBox #chatTitle').width() + 10
            $('#chatBox #msg')
                .prop('disabled', false)
                .css({
                    'padding-left': padding,
                    width: inputWidth - padding
                })
        }
    }
    this.addCloseClick = function () {
        $('#chatBox .close').click(function () {
            $('#chatBox').addClass('mini')
            PrivateChat.removeCloseClick()
        })
    }
    this.removeCloseClick = function () {
        $('#chatBox input').prop('disabled', true)
        $('#chatBox .close').unbind()
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
                    if (type == 'game') {
                        PrivateChat.message(0, r.name, r.msg)
                    } else {
                        PrivateChat.prepare()
                        PrivateChat.message(0, r.name, r.msg)
                        $('#chatWindow').animate({scrollTop: $('#chatWindow div')[0].scrollHeight}, 1000)
                    }
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

        var msg = $('#msg').val()

        if (!msg) {
            return
        }

        switch (type) {
            case 'new':
                PrivateChat.message(2, playerName, msg)
                $('#chatWindow').animate({scrollTop: $('#chatWindow div')[0].scrollHeight}, 1000)
                $('#msg').val('')
                New.chat(msg)
                break
            case 'setup':
                PrivateChat.message(2, playerName, msg)
                $('#chatWindow').animate({scrollTop: $('#chatWindow div')[0].scrollHeight}, 1000)
                $('#msg').val('')
                Setup.chat(msg)
                break
            default :
                var friendId = $('#chatBox #friendId').val(),
                    chatTitle = $('#chatBox #chatTitle').html()

                if (!friendId) {
                    return
                }

                PrivateChat.message(1, chatTitle, msg)
                $('#chatWindow').animate({scrollTop: $('#chatWindow div')[0].scrollHeight}, 1000)
                $('#msg').val('')

                var token = {
                    type: 'chat',
                    friendId: friendId,
                    msg: msg
                }

                ws.send(JSON.stringify(token))
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
