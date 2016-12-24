"use strict"
var IndexController = new function () {
    this.index = function (r) {
        var body = Main.getBody()

        if (body) {
            $('body').html(body)
            Main.setBody('')
            Main.updateMenu()
            Main.updateMenuClick()
        }

        var content = $('#content'),
            data = r.data

        content.html(data)
    }
}