var Editor = new function () {
    this.init = function (r) {

        PickerCommon.reset()
        GameScene.clear()
        GameRenderer.clear()

        Units.init(r.units)

        Fields.init(r.fields, EditorController.getMapId())
        Fields.createEditorTextures()
        EditorGround.init(Fields.getMaxX(), Fields.getMaxY(), Fields.getTextureCanvas())


        Ruins.init(r.ruins)
        Players.init(r.players)

        EditorGui.init()
        GameScene.initSun(Fields.getMaxY())
        GameScene.setCameraPosition(0, Fields.getMaxY())
        GameRenderer.start()

        PickerCommon.init(PickerEditor)

        $('#loading').hide()
        $('#game').show()
        $('.editor').show()
    }
}