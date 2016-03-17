"use strict"
var WebSocketNew = new function () {
    this.init = function () {
        var ws = new WebSocket(wsURL + '/new')

        ws.onopen = function () {
            WebSocketSendNew.setClosed(0)
            WebSocketSendNew.open()
        }
        ws.onmessage = function (e) {
            if (typeof gameId === 'undefined') {
                WebSocketMessageNew.switch($.parseJSON(e.data))
            }
        }
        ws.onclose = function () {
            WebSocketSendNew.setClosed(1)
            setTimeout('WebSocketNew.init()', 1000);
        }

        WebSocketSendNew.init(ws)
    }
}
