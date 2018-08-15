"use strict"
$().ready(function () {
    Page.init()
    Main.init()

    HelpScene.init()
    HelpRenderer.init()
    HelpModels.init()

    GameScene.init()
    GameRenderer.init()
    GameModels.init()

    Terrain.init(terrain)
})
$(window).resize(function () {
    if ($(window).innerWidth() < $(window).innerHeight()) {
        $('body').addClass('vertical')
    } else {
        $('body').removeClass('vertical')
    }
})