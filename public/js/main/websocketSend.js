"use strict"
var WebSocketSendMain = new function () {
    var closed = true,
        ws

    this.setClosed = function (param) {
        closed = param
    }
    this.isClosed = function () {
        return closed
    }
    this.controller = function (controller, action, params) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        if (notSet(action)) {
            action = 'index'
        }

        var token = {
            type: controller,
            action: action
        }
        if (isSet(params)) {
            token.params = params
        }
        ws.send(JSON.stringify(token))
    }
    this.open = function () {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'open',
            playerId: id,
            langId: langId,
            accessKey: accessKey
        }
        ws.send(JSON.stringify(token))
    }
    this.init = function (param) {
        ws = param
    }
}
