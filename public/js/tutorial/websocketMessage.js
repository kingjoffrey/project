"use strict"
var WebSocketTutorialMessage = new function () {
    this.switch = function (r) {
        if (r.type == 'step') {
            Tutorial.changeStep(r.step)
        } else {
            WebSocketMessage.switch(r)
        }
    }
}
