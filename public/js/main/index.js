"use strict"
var IndexController = new function () {
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        var body = Main.getBody()

        if (body) {
            $('body').html(body)
            Main.setBody('')
        }

        content.html(data)
    }
}