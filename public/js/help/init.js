"use strict"
$().ready(function () {
    Help.init()
    Scene.initSimple()
    Scene.getCamera().position.x = -86
    Scene.getCamera().position.y = 84
    Scene.getCamera().position.z = 90
    Scene.initSun(30)
    Scene.renderSimple()
    Models.init()
})
