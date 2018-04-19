"use strict"
$().ready(function () {
    Main.init()

    HelpScene.init()
    HelpRenderer.init()
    HelpModels.init()

    GameScene.init()
    GameRenderer.init()
    GameModels.init()

    Terrain.init(terrain)
})
