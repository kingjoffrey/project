"use strict"
$().ready(function () {
    Help.init()
    Scene.initSimple()
    Scene.setCameraPosition(-8, 16)
    Scene.initSun(30)
    Scene.renderSimple()
    Models.init()
})
