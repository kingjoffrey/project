"use strict"
var WebSocketMapgenerator = new function () {
    var ws = 0
    this.init = function () {
        var port = wsPort * 1 + 1
        ws = new WebSocket(wsURL + ':' + port + '/generator')

        ws.onopen = function () {
            WebSocketSendMapgenerator.setClosed(0)
            WebSocketSendMapgenerator.open()
        }
        ws.onmessage = function (e) {
            WebSocketMessageMapgenerator.switch($.parseJSON(e.data))
        }
        ws.onclose = function () {
            WebSocketSendMapgenerator.setClosed(1)
            setTimeout('WebSocketMapgenerator.init()', 1000)
        }

        WebSocketSendMapgenerator.init(ws)
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
