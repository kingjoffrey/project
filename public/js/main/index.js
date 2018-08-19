"use strict"
var IndexController = new function () {
    this.index = function () {
        if (WebSocketGame.isOpen()) {
            console.log('a1')
            WebSocketGame.close()
            WebSocketExecGame.close()
            GameRenderer.stop()
            Game.resetLoading()
        }
        if (WebSocketTutorial.isOpen()) {
            console.log('a2')
            WebSocketTutorial.close()
            WebSocketExecTutorial.close()
            GameRenderer.stop()
            Game.resetLoading()
        }
        if (WebSocketEditor.isOpen()) {
            console.log('a3')
            WebSocketEditor.close()
            GameRenderer.stop()
        }
        if (WebSocketNew.isOpen()) {
            console.log('a4')
            WebSocketNew.close()
        }
        if (HelpRenderer.isRunning()) {
            console.log('a5')
            HelpRenderer.stop()
        }

        $(window).off('resize')
        // $(document).off('keydown')

        $('#game').hide()
        $('#gameMenu').hide()

        $('#menu .active').removeClass('active')

        $('#content').html(Page.getIndex())

        $('#bg').show()
        $('#menuBox').show()
        $('#menuTop').show()
    }
}