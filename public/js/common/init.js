"use strict"
var CommonInit = new function () {
    this.init = function (g) {
        if (Models.getLoading() < 17) {
            setTimeout(function () {
                CommonInit.init(g)
            }, 500)
            return
        }
    }
}
$(document).ready(function () {
    // GameScene.init($(window).innerWidth(), $(window).innerHeight())
    // GameModels.init()
    // PickerCommon.init()
})