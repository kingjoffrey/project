"use strict"
var Game = new function () {
    var loading = 1,
        game,
        gameId

    this.setGameId = function (id) {
        gameId = id
    }
    this.getGameId = function () {
        return gameId
    }
    this.getTurnsLimit = function () {
        return game.turnsLimit
    }
    this.getFirstUnitId = function () {
        return game.firstUnitId
    }
    this.getLoading = function () {
        return loading
    }
    this.resetLoading = function () {
        loading = 1
    }
    this.start = function (g) {
        game = g
        if (loading) {
            loading = 0

            GameGui.init()

            if (isSet(g.tutorial)) {
                $('#tutorial').show()
            } else {
                $('#tutorial').hide()
            }

            // PickerCommon.reset()
            GameScene.clear()
            Renderer.clear()

            Units.init(game.units)

            Fields.init(game.fields)
            Fields.createTextures()
            var waterMesh = Ground.init(Fields.getMaxX(), Fields.getMaxY(), Fields.getTextureCanvas(), Fields.getWaterTextureCanvas())
            GameScene.add(waterMesh)

            Players.init(game.players)
            Me.init(game.color, game.gold, game.bSequence)
            Ruins.init(game.ruins)

            GameScene.initSun(Fields.getMaxY())
            GameRenderer.start()

            PickerCommon.init(PickerGame)
            PickerCommon.attach(waterMesh)

            GameModels.addCursor()

            Turn.change(game.turnColor, game.turnNumber)

            $('#loading').hide()
        }

        if (Turn.isMy() && Me.getTurnActive()) {
            Turn.start(game.color)
        } else {
            GameGui.lock()
        }

        Sound.play('gamestart')
        GameRenderer.animate()
    }
}
