"use strict"
var TutorialController = new function () {
    this.index = function (r) {
        var body = $('body')
        Main.setBody(body.html())
        body.html(r.data)
        // WebSocketTutorial.init()
    }
}