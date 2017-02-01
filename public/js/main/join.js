"use strict"
var JoinController = new function () {
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        WebSocketNew.init()
        Join.init()
    }
}