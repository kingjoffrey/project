"use strict"
var Main = new function () {
    var active,
        init = 0,
        main = '',
        click = function (controller) {
            return function () {
                Sound.play('click')
                WebSocketSendMain.controller(controller)
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
                    .attr('id', controller)
                    .html(menu[controller])
                    .click(click(controller))
            )
        }
        Page.adjust()
    }
    this.updateMenuClick = function () {
        $('#menu a').each(function () {
            $(this).click(click($(this).attr('id')))
        })
    }
    this.setMain = function (b) {
        main = b
    }
    this.getMain = function () {
        return main
    }
}