var WebSocketSetup = new function () {
    this.init = function () {
        var ws = new WebSocket(wsURL + ':' + wsPort + '/setup')

        ws.onopen = function () {
            WebSocketSend.setClosed(0)
            WebSocketSend.open()
        }
        ws.onmessage = function (e) {
            WebSocketMessage.switch($.parseJSON(e.data))
        }
        ws.onclose = function () {
            WebSocketSend.setClosed(1)
            setTimeout('WebSocketSetup.init()', 1000)
        }

        WebSocketSend.init(ws)
    }
}
