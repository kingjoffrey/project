var WebSocketSendChat = new function () {
    var closed = true,
        ws

    this.setClosed = function (param) {
        closed = param
    }
    this.open = function () {
        var token = {
            'type': 'open',
            'playerId': id,
            'langId': langId,
            'name': playerName,
            'accessKey': accessKey
        }

        ws.send(JSON.stringify(token));
    }
    this.send = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        var msg = $('#msg').val()

        if (!msg) {
            return
        }

        var friendId = $('#chatBox #friendId').val(),
            chatTitle = $('#chatBox #chatTitle').html()

        if (friendId) {
            PrivateChat.message(1, chatTitle, friendId, msg)

            var token = {
                type: 'chat',
                friendId: friendId,
                msg: msg
            }

            ws.send(JSON.stringify(token))
        }
    }
    this.init = function (param) {
        ws = param
    }
}
