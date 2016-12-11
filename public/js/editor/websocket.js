"use strict"
var WebSocketEditor = new function () {
    this.init = function () {
        var ws = new WebSocket(wsURL + ':' + wsPort + '/editor')

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
            setTimeout('WebSocketEditor.init()', 1000)
        }

        WebSocketSend.init(ws)
    }
}
