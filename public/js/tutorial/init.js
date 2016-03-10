"use strict"
var Init = new function () {
    this.init = function (g) {
        Game.init(g)
        Tutorial.init(g.tutorial)
    }
}