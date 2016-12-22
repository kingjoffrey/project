"use strict"
var WebSocketHelp = new function () {
    this.init = function () {
        var ws = new WebSocket(wsURL + ':' + wsPort + '/help')

        ws.onopen = function () {
            WebSocketSendHelp.setClosed(0)
            WebSocketSendHelp.open()
        }
        ws.onmessage = function (e) {
            if (typeof gameId === 'undefined') {
                WebSocketMessageHelp.switch($.parseJSON(e.data))
            }
        }
        ws.onclose = function () {
            WebSocketSendHelp.setClosed(1)
            setTimeout('WebSocketHelp.init()', 1000);
        }

        WebSocketSendHelp.init(ws)
    }
}
