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
            dupa.blada()
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
    this.setMain = function (b) {
        main = b
    }
    this.getMain = function () {
        return main
    }
}