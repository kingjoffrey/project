"use strict"
var Main = new function () {
    var active,
        init = 0,
        body = '',
        click = function (controller) {
            return function () {
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
    }
    this.updateMenu = function (controller) {
        $('#menu .button').each(function () {
            $(this).removeClass('active')
        })
        $('#menu .button#' + controller).addClass('active')
    }
    this.setBody = function (b) {
        body = b
    }
    this.getBody = function () {
        return body
    }
}