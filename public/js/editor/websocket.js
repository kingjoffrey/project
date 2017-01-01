"use strict"
var WebSocketEditor = new function () {
    var ws = 0
    this.init = function () {
        ws = new WebSocket(wsURL + ':' + wsPort + '/editor')

        ws.onopen = function () {
            WebSocketSendEditor.setClosed(0)
            WebSocketSendEditor.open()
        }
        ws.onmessage = function (e) {
            WebSocketMessageEditor.switch($.parseJSON(e.data))
        }
        ws.onclose = function () {
            WebSocketSendEditor.setClosed(1)
            setTimeout('WebSocketEditor.init()', 1000)
        }

        WebSocketSendEditor.init(ws)
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
