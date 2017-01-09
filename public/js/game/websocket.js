"use strict"
var WebSocketGame = new function () {
    var port,
        ws = 0
    this.init = function (p) {
        port = p
        ws = new WebSocket(wsURL + ':' + port + '/game' + Game.getGameId())

        ws.onopen = function () {
            WebSocketSendCommon.setClosed(0)
            WebSocketSendCommon.open()
        }

        ws.onmessage = function (e) {
            WebSocketMessageCommon.switch($.parseJSON(e.data))
        }

        ws.onclose = function () {
            WebSocketSendCommon.setClosed(1)
            setTimeout('WebSocketGame.init(WebSocketGame.getPort())', 1000)
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
var WebSocketExecGame = new function () {
    var ws

    this.init = function () {
        ws = new WebSocket(wsURL + ':' + wsPort + '/exec')

        ws.onopen = function () {
            var token = {
                'gameId': Game.getGameId(),
                'playerId': id,
                'accessKey': accessKey
            }

            ws.send(JSON.stringify(token))
        }
        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data)
            if (isSet(r.port)) {
                setTimeout(function () {
                    WebSocketGame.init(r.port)
                }, 1000)
            }
        }
        ws.onclose = function () {
            setTimeout('WebSocketExecGame.init()', 1000)
        }
    }
    this.close = function () {
        ws.onclose = 0
        ws.close()
    }
}