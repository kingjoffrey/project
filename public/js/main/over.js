"use strict"
var OverController = new function () {
    this.index = function (r) {
        if (WebSocketGame.isOpen()) {
            GameRenderer.stop()
        }
        if (WebSocketTutorial.isOpen()) {
            GameRenderer.stop()
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