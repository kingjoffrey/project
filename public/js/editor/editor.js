var Editor = new function () {
    this.init = function (r) {
        PickerCommon.reset()
        GameScene.init($(window).innerWidth(), $(window).innerHeight())
        Units.init(r.units)
        Fields.init(r.fields, EditorController.getMapId())
        Ruins.init(r.ruins)
        Players.init(r.players)
        GameScene.setCameraPosition(0, Fields.getMaxY())
        GameRenderer.init('game', GameScene)
        GameScene.initSun(Fields.getMaxY())
        GameRenderer.animate()
        EditorGui.init()
        PickerCommon.init(PickerEditor)

        $('#loading').hide()

        $('#game').show()
        $('.editor').show()
    }
}