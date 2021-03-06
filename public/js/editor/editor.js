var Editor = new function () {
    this.init = function (r) {

        // PickerCommon.reset()
        GameScene.clear()
        Renderer.clear()

        Units.init(r.units)

        Fields.init(r.fields)
        Fields.createEditorTextures()
        var groundMesh = EditorGround.init(Fields.getMaxX(), Fields.getMaxY(), Fields.getTextureCanvas())

        Ruins.init(r.ruins)
        Players.init(r.players)

        EditorGui.init()
        GameScene.initSun(Fields.getMaxY())
        GameScene.setCameraPosition(0, Fields.getMaxY() * 2)
        GameRenderer.start()

        PickerCommon.init(PickerEditor)
        PickerCommon.attach(groundMesh)

        $('#loading').hide()
        $('#tutorial').hide()
        $('.game').hide()
        $('#game').show()
        $('.editor').show()

        GameRenderer.animate()

        GameScene.centerOn(0, 0)
    }

    this.mapIsReady = function () {
        var numberOfPlayers = 0,
            numberOfCapitals = 0

        for (var color in Players.toArray()) {
            if (color == 'neutral') {
                continue
            }
            numberOfPlayers++
            for (var castleId in Players.get(color).getCastles().toArray()) {
                if (Players.get(color).getCastles().get(castleId).getCapital()) {
                    numberOfCapitals++
                }
            }
        }

        if (numberOfPlayers == numberOfCapitals) {
            return true
        } else {
            return false
        }
    }
}