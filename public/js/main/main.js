"use strict"
var Main = new function () {
    var init = 0,
        development = 0,
        click = function (controller) {
            return function () {
                Sound.play('click')
                WebSocketSendMain.controller(controller)
            }
        }
    this.setEnv = function (val) {
        if (val == 'development') {
            development = 1
        }
    }
    this.getEnv = function () {
        return development
    }
    this.createMenu = function (menu) {
        if (init) {
            return
        }

        init = 1

        for (var controller in menu) {
            $('#menu').append(
                $('<div>').addClass('iconButton buttonColors')
                    .click(click(controller))
                    .append(
                        $('<div>').html(menu[controller]).addClass('varchar')
                    )
                    .attr('id', controller)
            )
        }

        {
            $('#menu').append(
                $('<div>').addClass('iconButton buttonColors')
                    .click(click('messages'))
                    .append($('<div>').html('&nbsp;&nbsp;&nbsp;').addClass('varchar'))
                    .attr({
                        id: 'messages',
                        title: 'Messages'
                    })
            )
        }
    }
    this.updateMenuClick = function () {
        $('#menu a').each(function () {
            $(this).click(click($(this).attr('id')))
        })
    }
    this.init = function () {
        WebSocketMain.init()
    }
}