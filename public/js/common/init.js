"use strict"
var CommonInit = new function () {
    this.init = function (g) {
        if (Models.getLoading() < 17) {
            setTimeout(function () {
                CommonInit.init(g)
            }, 500)
            return
        }
        Init.init(g)
    }
}
$(document).ready(function () {
    Scene.init()
    Models.init()
    PickerCommon.init()
})