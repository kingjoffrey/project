"use strict"
var IndexController = new function () {
    this.index = function () {
        $('.message').remove()

        $('#game').hide()
        $('#gameMenu').hide()

        $('#content').html(Page.getIndex())

        $('#bg').show()
        $('#menuBox').show()
    }
}