var Editor = new function () {
    var init = 0

    this.init = function (r) {
        if (!init) {
            init = 1
            GameScene.init($(window).innerWidth(), $(window).innerHeight())
            Units.init(r.units)
            Fields.init(r.fields, EditorController.getMapId())
            MiniMap.init(Game.getMapElement())
            Ruins.init(r.ruins)
            Players.init(r.players)
            GameScene.setCameraPosition(0, Fields.getMaxY())
            GameRenderer.init('editor', GameScene)
            GameScene.initSun(Fields.getMaxY())
            GameRenderer.animate()
            EditorGui.init()
        }
    }
    this.setInit = function (i) {
        init = i
    }
}

