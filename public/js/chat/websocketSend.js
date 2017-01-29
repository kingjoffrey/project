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
    this.send = function (msg, friendId) {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        if (!msg) {
            return
        }

        if (!friendId) {
            return
        }

        var token = {
            type: 'chat',
            friendId: friendId,
            msg: msg
        }

        ws.send(JSON.stringify(token))
    }
    this.init = function (param) {
        ws = param
    }
}
