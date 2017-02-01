"use strict"
var IndexController = new function () {
    this.index = function (r) {
        var main = Main.getMain()

        if (main) {
            $('#main').html(main)
            Main.setMain('')
            Main.updateMenu()
            Main.updateMenuClick()
            Page.init()
        }

        var content = $('#content'),
            data = r.data

        content.html(data)

        Page.adjust()
    }
}