"use strict"
var WebSocketTutorial = new function () {
    var port,
        ws = 0
    this.init = function (p) {
        port = p
        ws = new WebSocket(wsURL + ':' + port + '/tutorial' + Game.getGameId())

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
    this.close = function () {
        ws.onclose = 0
        ws.close()
        ws = 0
    }
    this.isOpen = function () {
        if (ws) {
            return 1
        }
    }
}
var WebSocketExecTutorial = new function () {
    var ws

    this.init = function () {
        ws = new WebSocket(wsURL + ':' + wsPort + '/exec')

        ws.onopen = function () {
            var token = {
                'gameId': Game.getGameId()
            }

            ws.send(JSON.stringify(token))
        }
        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data)
            if (isSet(r.port)) {
                setTimeout(function () {
                    WebSocketTutorial.init(r.port)
                }, 1000)
            }
        }
        ws.onclose = function () {
            setTimeout('WebSocketExecTutorial.init()', 1000)
        }
    }
    this.close = function () {
        ws.onclose = 0
        ws.close()
    }
}
