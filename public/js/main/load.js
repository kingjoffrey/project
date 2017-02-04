"use strict"
var LoadController = new function () {
    this.index = function (r) {
        $('#content').html(r.data)

        $('.trlink').click(function () {
            GameController.index($(this).attr('id'))
        })
    }
}