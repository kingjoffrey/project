"use strict"
var WebSocketEditor = new function () {
    this.init = function () {
        var ws = new WebSocket(wsURL + ':' + wsPort + '/editor')

        ws.onopen = function () {
            WebSocketSendEditor.setClosed(0)
            WebSocketSendEditor.open()
        }

        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data);
            WebSocketMessageEditor.switch(r)
        }

        ws.onclose = function () {
            WebSocketSendEditor.setClosed(1)
            setTimeout('WebSocketEditor.init()', 1000)
        }

        WebSocketSendEditor.init(ws)
    }
}
