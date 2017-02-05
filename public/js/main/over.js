"use strict"
var OverController = new function () {
    this.index = function (r) {
        $('#content').html(r.data)

        $('.message').remove()

        $('#game').hide()
        $('#gameMenu').hide()

        $('#bg').show()
        $('#menuBox').show()
    }
}