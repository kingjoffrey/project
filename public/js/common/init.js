"use strict"
var CommonInit = new function () {
    this.init = function (g) {
        if (Models.getLoading() < 17) {
            setTimeout(function () {
                CommonInit.init(g)
            }, 500)
            return
        }
        if (isSet(g.tutorial)) {
            Tutorial.init(g.tutorial)
        }
        Game.init(g)
    }
}