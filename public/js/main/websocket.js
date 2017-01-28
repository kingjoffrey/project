"use strict"
var WebSocketMain = new function () {
    this.init = function () {
        var ws = new WebSocket(wsURL + ':' + wsPort + '/main')

        ws.onopen = function () {
            WebSocketSendMain.setClosed(0)
            WebSocketSendMain.open()
        }
        ws.onmessage = function (e) {
            WebSocketMessageMain.switch($.parseJSON(e.data))
        }
        ws.onclose = function () {
            WebSocketSendMain.setClosed(1)
            setTimeout('WebSocketMain.init()', 1000);
        }

        WebSocketSendMain.init(ws)
    }
}
