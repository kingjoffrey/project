"use strict"
var Main = new function () {
    var active,
        init = 0,
        main = '',
        click = function (controller) {
            return function () {
                WebSocketSendMain.controller(controller)
            }
        }
    this.init = function () {
        WebSocketMain.init()
    }
    this.createFriends = function (friends) {
        for (var id in friends) {
            this.addFriend(friends[id], id)
        }
        if (notSet(id)) {
            this.addNoFriends()
        }
        PrivateChat.init()
    }
    this.addNoFriends = function () {
        $('#friends')
            .append(translations.YouDontHaveFriends + ': ')
            .append($('<div>').attr('id', 'findFriends').html(translations.findSomeFriends))
            .click(function () {
                WebSocketSendMain.controller('players', 'index')
            })
    }
    this.addFriend = function (friend, id) {
        $('#friends').append($('<div>').attr('id', id).addClass('friends')
            .append($('<div>').attr('id', 'online'))
            .append($('<div>').attr('id', 'trash').click(function () {
                    Websocket.delete($(this).parent().attr('id'))
                    $(this).parent().remove()

                    if ($('#friends #trash').length == 0) {
                        Main.addNoFriends()
                    }
                })
            )
            .append($('<span>').html(friend).click(function () {
                    if ($('#chatBox.disabled #msg').val() == translations.selectFriendFromFriendsList) {
                        $('#chatBox.disabled #msg').val('')
                    }
                    PrivateChat.prepare($(this).html(), $(this).parent().attr('id'))
                })
            )
        )
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