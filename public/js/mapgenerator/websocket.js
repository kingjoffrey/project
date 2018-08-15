"use strict"
var WebSocketMapgenerator = new function () {
    var ws = 0
    this.init = function () {
        ws = new WebSocket(wsURL + ':' + wsPort + '/generator')

        ws.onopen = function () {

        }
        ws.onmessage = function (e) {
            WebSocketMessageMapgenerator.switch($.parseJSON(e.data))
        }
        ws.onclose = function () {

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
