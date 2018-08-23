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
    this.send = function (msg) {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        var token = {
            type: 'chat',
            friendId: $('input:hidden[name=id]').val(),
            msg: msg
        }

        ws.send(JSON.stringify(token))
    }
    this.threads = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        var token = {
            type: 'threads',
            page: 1
        }

        ws.send(JSON.stringify(token))
    }
    this.conversation = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        var token = {
            type: 'conversation',
            id: $('input:hidden[name=id]').val(),
            page: 1
        }

        ws.send(JSON.stringify(token))
    }
    this.init = function (param) {
        ws = param
    }
}
