"use strict"
var WebSocketGame = new function () {
    var port
    this.init = function (p) {
        port = p
        var ws = new WebSocket(wsURL1 + ':' + port + '/game' + gameId)

        ws.onopen = function () {
            WebSocketSend.setClosed(0)
            WebSocketSend.open()
        }

        ws.onmessage = function (e) {
            WebSocketMessage.switch($.parseJSON(e.data))
        }

        ws.onclose = function () {
            WebSocketSend.setClosed(1)
            setTimeout('WebSocketGame.init(WebSocketGame.getPort())', 1000)
        }

        WebSocketSend.init(ws)
    }
    this.getPort = function () {
        return port
    }
}
var WebSocketPCNTL = new function () {
    var closed = true,
        ws

    this.init = function () {
        ws = new WebSocket(wsURL1 + ':' + wsURL2 + '/pcntl')

        ws.onopen = function () {
            closed = 0

            if (closed) {
                Message.error(translations.sorryServerIsDisconnected)
                return;
            }

            var token = {
                gameId: gameId
            }

            ws.send(JSON.stringify(token))
        }

        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data)
            console.log(r)
            if (isSet(r.port)) {
                WebSocketGame.init(r.port)
            }
        }

        ws.onclose = function () {
            closed = 1
            setTimeout('WebSocketPCNTL.init()', 1000)
        }
    }
}