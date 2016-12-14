"use strict"
var Main = new function () {
    var active,
        click = function (controller) {
            return function () {
                WebSocketMainSend.controller(controller)
            }
        }
    this.init = function () {
        WebSocketMain.init()
    }
    this.createMenu = function (menu) {
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
    this.controller = function (r) {
        console.log(r)
    }
}