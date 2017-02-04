"use strict"
var TutorialController = new function () {
    this.index = function (r) {
        $('#bg').hide()
        $('#loading').show()

        Game.setGameId(r.gameId)
        Tutorial.initSteps(r.steps)

        WebSocketExecTutorial.init()
    }
}