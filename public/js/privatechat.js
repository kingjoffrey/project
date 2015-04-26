var Chat = new function () {
    var inputWidth

    this.init = function () {
        Websocket.init()

        inputWidth = $('#chatBox input').width()
        $('#chatBox input').prop('disabled', true)
        $('#send').click(function () {
            Websocket.chat()
        })
        $('#friendsBox #friends div').click(function () {
            Chat.prepare($(this).html(), $(this).attr('id'))
        })
        $('#messages .trlink').click(function () {
            var playerId = $(this).attr('id'),
                chatId = $(this).find('.id').attr('id'),
                name = $(this).find('#name').html(),
                read = $(this).hasClass('read'),
                notifications = $('#envelope').find('span').html()

            Websocket.read(chatId, name, read)
            Chat.prepare(name, playerId)
            $(this).removeClass('read')
            notifications--
            if (notifications) {
                $('#envelope').find('span').html(notifications)
            } else {
                $('#envelope').html('')
            }
        })
    }
    this.message = function (to, chatTitle, msg) {
        if (to) {
            var prepend = translations.to
        } else {
            var prepend = translations.from
        }
        $('#chatContent').append(prepend + ' ' + chatTitle + ': ' + msg + '<br/>')
    }
    this.prepare = function (name, friendId) {
        $('#chatBox').removeClass('mini')
        if (isSet(name)) {
            $('#chatBox #chatTitle').html(name)
            if (isSet(friendId)) {
                $('#chatBox #friendId').val(friendId)
            }
            var padding = $('#chatBox #chatTitle').width() + 10
            $('#chatBox input')
                .prop('disabled', false)
                .css({
                    'padding-left': padding,
                    width: inputWidth - padding
                })
        }
        Chat.addCloseClick()
    }
    this.addCloseClick = function () {
        $('#chatBox .close').click(function () {
            $('#chatBox').addClass('mini')
            Chat.removeCloseClick()
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
                    if (!r.count) {
                        return
                    }
                    $('#envelope').html($('<span>').html(r.count))
                    break
                case 'chat':
                    Chat.prepare()
                    Chat.message(0, r.name, r.msg)
                    $('#chatWindow').animate({scrollTop: $('#chatWindow div')[0].scrollHeight}, 1000)
                    break
                case 'read':
                    Chat.message(0, r.name, r.msg)
                    $('#chatWindow').animate({scrollTop: $('#chatWindow div')[0].scrollHeight}, 1000)
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

        var msg = $('#msg').val(),
            friendId = $('#chatBox #friendId').val(),
            chatTitle = $('#chatBox #chatTitle').html()

        if (!msg) {
            return
        }
        if (!friendId) {
            return
        }

        if (msg) {
            Chat.message(1, chatTitle, msg)
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
    this.read = function (id, name, read) {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        var token = {
            type: 'read',
            read: read,
            name: name,
            chatId: id
        }

        ws.send(JSON.stringify(token))
    }
}
