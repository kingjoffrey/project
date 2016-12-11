"use strict"
var WebSocketHelp = new function () {
    this.init = function () {
        var ws = new WebSocket(wsURL + ':' + wsPort + '/help')

        ws.onopen = function () {
            WebSocketSend.setClosed(0)
            WebSocketSend.open()
        }
        ws.onmessage = function (e) {
            if (typeof gameId === 'undefined') {
                WebSocketMessage.switch($.parseJSON(e.data))
            }
        }
        ws.onclose = function () {
            WebSocketSend.setClosed(1)
            setTimeout('WebSocketHelp.init()', 1000);
        }

        WebSocketSend.init(ws)
    }
}
