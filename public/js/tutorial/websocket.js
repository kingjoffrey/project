"use strict"
var WebSocketTutorial = new function () {
    var port
    this.init = function (p) {
        port = p
        var ws = new WebSocket(wsURL + ':' + port + '/tutorial' + Game.getGameId())

        ws.onopen = function () {
            WebSocketSendCommon.setClosed(0)
            WebSocketSendCommon.open()
        }

        ws.onmessage = function (e) {
            WebSocketMessageTutorial.switch($.parseJSON(e.data))
        }

        ws.onclose = function () {
            WebSocketSendCommon.setClosed(1)
            setTimeout('WebSocketTutorial.init(WebSocketTutorial.getPort())', 1000)
        }

        WebSocketSendCommon.init(ws)
    }
    this.getPort = function () {
        return port
    }
}
var WebSocketExecTutorial = new function () {
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
                'gameId': Game.getGameId()
            }

            ws.send(JSON.stringify(token))
        }

        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data)
            if (isSet(r.port)) {
                setTimeout(function() {WebSocketTutorial.init(r.port)}, 1000)
            }
        }

        ws.onclose = function () {
            closed = 1
            setTimeout('WebSocketExecTutorial.init()', 1000)
        }
    }
}
