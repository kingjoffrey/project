$(document).ready(function () {
    // Test.init()
    Models.init()
    GameScene.init($(window).innerWidth(), $(window).innerHeight())
    Fields.init(fields, 321)
    GameRenderer.init('game', GameScene)
    GameScene.initSun(Fields.getMaxY())
    GameRenderer.animate()
    GameScene.getCamera().position.x = -50
    GameScene.getCamera().position.y = 80
    GameScene.getCamera().position.z = 120
    GameScene.resize($(window).innerWidth(), $(window).innerHeight())
})
