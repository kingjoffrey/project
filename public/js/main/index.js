"use strict"
var IndexController = new function () {
    this.index = function () {
        if (WebSocketGame.isOpen()) {
            WebSocketGame.close()
            WebSocketExecGame.close()
            GameRenderer.stop()
            Game.resetLoading()
        }
        if (WebSocketTutorial.isOpen()) {
            WebSocketTutorial.close()
            WebSocketExecTutorial.close()
            GameRenderer.stop()
            Game.resetLoading()
        }
        if (WebSocketEditor.isOpen()) {
            WebSocketEditor.close()
            GameRenderer.stop()
        }
        if (WebSocketNew.isOpen()) {
            WebSocketNew.close()
        }
        if (HelpRenderer.isRunning()) {
            HelpRenderer.stop()
        }

        $(window).off('resize')

        $('.message').remove()

        $('#game').hide()
        $('#gameMenu').hide()

        $('#content').html(Page.getIndex())

        $('#bg').show()
        $('#menuBox').show()
    }
}