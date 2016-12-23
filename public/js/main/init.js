"use strict"
$().ready(function () {
    Main.init()
    GameScene.init($(window).innerWidth(), $(window).innerHeight())
    GameModels.init()
})
