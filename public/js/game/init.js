"use strict"
var GameInit = new function () {
    this.init = function (g) {
        if (Models.getLoading() < 17) {
            setTimeout(function () {
                GameInit.init(g)
            }, 500)
            return
        }
        if (isSet(g.tutorial)) {
            Tutorial.init(g.tutorial)
        }
        Game.start(g)
    }
}