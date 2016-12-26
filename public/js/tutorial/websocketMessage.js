"use strict"
var WebSocketMessageTutorial = new function () {
    this.switch = function (r) {
        // console.log(r)
        if (r.type == 'step') {
            Tutorial.changeStep(r.step)
        } else {
            WebSocketMessageCommon.switch(r)
        }
    }
}
