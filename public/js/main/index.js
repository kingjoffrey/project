"use strict"
var IndexController = new function () {
    var index = ''
    this.index = function (r) {
        var main = Main.getMain()

        if (main) {
            $('#main').html(main)
            Main.setMain('')
            Main.updateMenuClick()
            Page.init()
        }

        if (!index) {
            index = r.data
        }

        $('#content').html(index)
    }
}