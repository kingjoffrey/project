"use strict"
$().ready(function () {
    Page.init()
    Main.init()

    Models.init()

    HelpScene.init()
    HelpRenderer.init()
    HelpModels.init()

    GameScene.init()
    GameRenderer.init()

    Terrain.init(terrain)

    WebSocketChat.init()
    WebSocketMapgenerator.init()
})
$(window).resize(function () {
    if ($(window).innerWidth() < $(window).innerHeight()) {
        $('body').addClass('vertical')
    } else {
        $('body').removeClass('vertical')
    }
})