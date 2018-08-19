"use strict"
var WebSocketChat = new function () {
    var ws = 0
    this.init = function () {
        ws = new WebSocket(wsURL + ':' + wsPort + '/chat')

        ws.onopen = function () {
            WebSocketSendChat.setClosed(0)
            WebSocketSendChat.open()
        }
        ws.onmessage = function (e) {
            WebSocketMessageChat.switch($.parseJSON(e.data))
        }
        ws.onclose = function () {
            WebSocketSendChat.setClosed(1)
            setTimeout('WebSocketChat.init()', 1000);
        }

        WebSocketSendChat.init(ws)
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