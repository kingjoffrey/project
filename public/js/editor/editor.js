var Editor = new function () {
    var init = 0

    this.init = function (r) {
        if (!init) {
            init = 1
            Units.init(r.units)
            Fields.init(r.fields, mapId)
            Ruins.init(r.ruins)
            Players.init(r.players)
            Gui.init()
            GameScene.setCameraPosition(0, Fields.getMaxY())
            GameScene.initSun(Fields.getMaxY())
            GameRenderer.init('game', GameScene)
            GameRenderer.animate()
        }
        //console.log(r)
    }
}

$(document).ready(function () {
    GameScene.init($(window).innerWidth(), $(window).innerHeight())
    GameModels.init()
    PickerCommon.init()
    WebSocketEditor.init()
})
