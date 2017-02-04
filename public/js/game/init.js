"use strict"
var GameInit = new function () {
    this.init = function (g) {
        if (isSet(g.tutorial)) {
            Tutorial.init(g.tutorial)
        }
        Game.start(g)
    }
}