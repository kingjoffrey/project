var Editor = new function () {
    this.init = function (r) {

        PickerCommon.reset()
        GameScene.clear()
        Renderer.clear()

        Units.init(r.units)

        Fields.init(r.fields)
        Fields.createEditorTextures()
        EditorGround.init(Fields.getMaxX(), Fields.getMaxY(), Fields.getTextureCanvas())

        Ruins.init(r.ruins)
        Players.init(r.players)

        EditorGui.init()
        GameScene.initSun(Fields.getMaxY())
        GameScene.setCameraPosition(0, Fields.getMaxY() * 2)
        GameRenderer.start()

        PickerCommon.init(PickerEditor)

        $('#bg').hide()
        $('#loading').hide()
        $('#tutorial').hide()
        $('.game').hide()
        $('#game').show()
        $('.editor').show()

        GameRenderer.animate()
    }
}