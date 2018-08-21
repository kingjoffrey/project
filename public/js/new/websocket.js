"use strict"
var WebSocketNew = new function () {
    var ws = 0
    this.init = function () {
        var port = wsPort * 1 + 3
        ws = new WebSocket(wsURL + ':' + port + '/games')

        ws.onopen = function () {
            WebSocketSendNew.setClosed(0)
            WebSocketSendNew.open()
        }
        ws.onmessage = function (e) {
            WebSocketMessageNew.switch($.parseJSON(e.data))
        }
        ws.onclose = function () {
            WebSocketSendNew.setClosed(1)
            setTimeout('WebSocketNew.init()', 1000);
        }

        WebSocketSendNew.init(ws)
    }
    this.close = function () {
        ws.onclose = 0
        ws.close()
        ws = 0
    }
    this.isOpen = function () {
        if (ws) {
            return 1
        } else {
            return 0
        }
    }
}
