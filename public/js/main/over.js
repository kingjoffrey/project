"use strict"
var OverController = new function () {
    this.index = function (r) {
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

        $(window).off('resize')
        $(document).off('keydown')

        $('#content').html(r.data)

        $('.message').remove()

        $('#turnInfo').hide()
        $('#game').hide()
        $('#gameMenu').hide()

        $('#bg').show()
        $('#menuBox').show()
    }
}