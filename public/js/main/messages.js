"use strict"
var MessagesController = new function () {
    this.index = function (r) {
        $('#content').html(r.data)

        WebSocketSendChat.threads()
    }
    this.thread = function (r) {
        $('#content').html(r.data)

        WebSocketSendChat.conversation()
    }
}