"use strict"
$().ready(function () {
    Help.init()
    Scene.initSimple()
    Scene.setCameraPosition(-12, 12)
    Scene.initSun(30)
    Scene.renderSimple()
    Models.init()
})
