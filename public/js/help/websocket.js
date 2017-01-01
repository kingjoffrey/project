"use strict"
var WebSocketHelp = new function () {
    var ws = 0
    this.init = function () {
        ws = new WebSocket(wsURL + ':' + wsPort + '/help')

        ws.onopen = function () {
            WebSocketSendHelp.setClosed(0)
            WebSocketSendHelp.open()
        }
        ws.onmessage = function (e) {
            WebSocketMessageHelp.switch($.parseJSON(e.data))
        }
        ws.onclose = function () {
            WebSocketSendHelp.setClosed(1)
            setTimeout('WebSocketHelp.init()', 1000);
        }

        WebSocketSendHelp.init(ws)
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
