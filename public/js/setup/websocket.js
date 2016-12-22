var WebSocketSetup = new function () {
    this.init = function () {
        var ws = new WebSocket(wsURL + ':' + wsPort + '/setup')

        ws.onopen = function () {
            WebSocketSendSetup.setClosed(0)
            WebSocketSendSetup.open()
        }
        ws.onmessage = function (e) {
            WebSocketMessageSetup.switch($.parseJSON(e.data))
        }
        ws.onclose = function () {
            WebSocketSendSetup.setClosed(1)
            setTimeout('WebSocketSetup.init()', 1000)
        }

        WebSocketSendSetup.init(ws)
    }
}
