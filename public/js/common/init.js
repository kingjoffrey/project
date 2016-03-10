"use strict"
var CommonInit = new function () {
    this.init = function (g) {
        if (Models.getLoading() < 17) {
            setTimeout(function () {
                Init.init(g)
            }, 500)
            return
        }
        Init.init(g)
    }
}