"use strict"
var LoadController = new function () {
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)
    }
}