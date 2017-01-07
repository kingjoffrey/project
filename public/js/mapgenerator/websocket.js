"use strict"
var WebSocketMapgenerator = new function () {
    var ws = 0
    this.init = function (mapSize) {
        ws = new WebSocket(wsURL + ':' + wsPort + '/generator')

        ws.onopen = function () {
            WebSocketSendMapgenerator.open()
        }
        ws.onmessage = function (e) {

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
