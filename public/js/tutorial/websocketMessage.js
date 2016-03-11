"use strict"
var WebSocketTutorialMessage = new function () {
    this.switch = function (r) {
        if (r.type == 'step') {
            console.log(r)
            Tutorial.changeStep(r.step)
        } else {
            WebSocketMessage.switch(r)
        }
    }
}
