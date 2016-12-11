"use strict"
var WebSocketGame = new function () {
    var port
    this.init = function (p) {
        port = p
        var ws = new WebSocket(wsURL + ':' + port + '/tutorial' + gameId)

        ws.onopen = function () {
            WebSocketSend.setClosed(0)
            WebSocketSend.open()
        }

        ws.onmessage = function (e) {
            WebSocketTutorialMessage.switch($.parseJSON(e.data))
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
var WebSocketExec = new function () {
    var closed = true,
        ws

    this.init = function () {
        ws = new WebSocket(wsURL + ':' + wsPort + '/exec')

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
            setTimeout('WebSocketExec.init()', 1000)
        }
    }
}
