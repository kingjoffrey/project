"use strict"
var IndexController = new function () {
    this.index = function (r) {
        var main = Main.getMain()

        if (main) {
            $('#main').html(main)
            Main.setMain('')
            Main.updateMenuClick()
            Page.init()
        }

        $('#content').html(r.data)
        $('#menuBox').show()
    }
}