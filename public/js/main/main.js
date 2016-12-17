"use strict"
var Main = new function () {
    var active,
        init = 0,
        click = function (controller) {
            return function () {
                WebSocketMainSend.controller(controller)
            }
        }
    this.init = function () {
        WebSocketMain.init()
    }
    this.createMenu = function (menu) {
        if (init) {
            return
        }
        init = 1
        var menuDiv = $('#menu')
        for (var controller in menu) {
            if (active == controller) {
                var id = 'active'
            } else {
                var id = ''
            }
            menuDiv.append(
                $('<a>')
                    .addClass('button')
                    .attr('id', id)
                    .html(menu[controller])
                    .click(click(controller))
            )
        }
    }
}