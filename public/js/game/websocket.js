"use strict"
var WebSocketGame = new function () {
    this.init = function () {
        var ws = new WebSocket(wsURL + '/game')

        ws.onopen = function () {
            WebSocketSend.setClosed(0)
            WebSocketSend.open()
        }

        ws.onmessage = function (e) {
            WebSocketMessage.switch($.parseJSON(e.data))
        }

        ws.onclose = function () {
            WebSocketSend.setClosed(1)
            setTimeout('WebSocketGame.init()', 1000)
        }

        WebSocketSend.init(ws)
    }
}
