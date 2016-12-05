var Websocket = new function () {
    var ws
    this.init = function () {
        ws = new WebSocket(wsURL + '/test')
        ws.onclose = function () {
            setTimeout('Websocket.init()', 1000)
        }
        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data);

            console.log(r)
        }
    }
    this.test1 = function () {
        var token = {
            type: 'test1'
        }
        ws.send(JSON.stringify(token))
    }
    this.test2 = function () {
        var token = {
            type: 'test2'
        }
        ws.send(JSON.stringify(token))
    }
}
$(document).ready(function () {
    Websocket.init()
})

var startTest = function () {
    Websocket.test2()
    Websocket.test2()
    Websocket.test1()
    Websocket.test2()
    Websocket.test2()
}