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
                    break;
                case 'chat':
                    Chat.prepare()
                    Chat.message(0, r.name, r.msg)
                    $('#chatWindow').animate({scrollTop: $('#chatWindow div')[0].scrollHeight}, 1000)
                    break;
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
        if (Websocket.isClosed()) {
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
}
