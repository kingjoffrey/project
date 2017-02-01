"use strict"
var OverController = new function () {
    this.index = function (r) {
        if ($('#game').length) {
            var main = Main.getMain()

            if (main) {
                $('#main').html(main)
                Main.setMain('')
                Main.updateMenuClick()
                Page.init()
            }
        }
        var content = $('#content'),
            data = r.data

        content.html(data)
    }
}