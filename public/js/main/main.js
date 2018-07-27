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

        $('#menu').append(
            $('<div>').addClass('iconButton buttonColors')
                .click(click('messages'))
                .append($('<div>').html('&nbsp;&nbsp;&nbsp;').addClass('varchar'))
                .attr({
                    id: 'messages',
                    title: 'Messages'
                })
        ).append(
            $('<div>').addClass('iconButton buttonColors')
                .click(function () {
                    Sound.play('click')
                    Page.fullScreen()
                })
                .append($('<div>').html('&nbsp;&nbsp;&nbsp;').addClass('varchar'))
                .attr({
                    id: 'fullScreen',
                    title: 'Full screen'
                })
        )
    }
    this.createContent = function (r) {
        var className = capitalizeFirstLetter(r.type + 'Controller')
        if (typeof window[className] !== "undefined") {
            var methodName = r.action
            if (typeof window[className][methodName] === "function") {
                window[className][methodName](r)

                if ($('#back').length) {
                    return
                } else {
                    $('#menuBox').hide()
                    if (r.type == 'help') {
                        $('#content')
                            .append($('<div>')
                                .append(
                                    $('<div>').attr('id', 'back').addClass('button buttonColors').html(translations.Mainmenu).click(function () {
                                        Sound.play('click')
                                        IndexController.index()
                                    })
                                ).css({
                                    'text-align': 'right'
                                })
                            )
                    } else {
                        $('#content')
                            .prepend($('<div>')
                                .append(
                                    $('<div>').attr('id', 'back').addClass('button buttonColors').html(translations.Mainmenu).click(function () {
                                        Sound.play('click')
                                        IndexController.index()
                                    })
                                ).css({
                                    'text-align': 'right'
                                })
                            )
                            .append($('<div>')
                                .append(
                                    $('<div>').attr('id', 'back').addClass('button buttonColors').html(translations.Mainmenu).click(function () {
                                        Sound.play('click')
                                        IndexController.index()
                                    })
                                ).css({
                                    'text-align': 'right'
                                })
                            )
                    }
                }
            } else {
                console.log('Method ' + methodName + ' in class ' + className + ' !exists')
            }
        } else {
            console.log('Class ' + className + ' !exists')
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