"use strict"
var WebSocketMain = new function () {
    this.init = function () {
        var ws = new WebSocket(wsURL + ':' + wsPort + '/main')

        ws.onopen = function () {
            WebSocketMainSend.setClosed(0)
            WebSocketMainSend.open()
        }
        ws.onmessage = function (e) {
            if (typeof gameId === 'undefined') {
                WebSocketMainMessage.switch($.parseJSON(e.data))
            }
        }
        ws.onclose = function () {
            WebSocketMainSend.setClosed(1)
            setTimeout('WebSocketMain.init()', 1000);
        }

        WebSocketMainSend.init(ws)
    }
}
