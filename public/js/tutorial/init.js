"use strict"
var Init = new function () {
    this.init = function (g) {
        Tutorial.init(g.tutorial)
        Game.init(g)
    }
}