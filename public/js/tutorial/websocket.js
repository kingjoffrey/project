"use strict"
var WebSocketGame = new function () {
    this.init = function () {
        var ws = new WebSocket(wsURL + '/tutorial')

        ws.onopen = function () {
            WebSocketSend.setClosed(0)
            WebSocketSend.open()
        }

        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data);
            WebSocketMessage.switch(r)
        }

        ws.onclose = function () {
            WebSocketSend.setClosed(1)
            setTimeout('WebSocketGame.init()', 1000)
        }

        WebSocketSend.init(ws)
    }
}
