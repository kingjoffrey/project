"use strict"
var FriendsController = new function () {
    var createFriends = function (friends) {
            for (var id in friends) {
                addFriend(friends[id], id)
            }
            if (notSet(id)) {
                addNoFriends()
            }
        },
        addNoFriends = function () {
            $('#friendsList').append($('<tr>')
                .append($('<td>').html(translations.YouDontHaveFriends + ': '))
                .append($('<td>').html($('<span>').attr('id', 'findFriends').html(translations.findSomeFriends)))
                .click(function () {
                    WebSocketSendMain.controller('players', 'index')
                })
            )
        },
        addFriend = function (friend, id) {
            $('#friendsList').append($('<tr>')
                .append($('<td>').append($('<div>').addClass('online')))
                .append($('<td>').html(friend))
                .append($('<td>').html($('<a>').attr('id', id).html(translations.write).click(function () {
                        var playerId = $(this).attr('id')
                        MessagesController.setPlayerId(playerId)
                        WebSocketSendMain.controller('messages', 'thread', {'id': playerId})
                    }))
                )
                .append($('<td>').append($('<div>').addClass('trash').attr('id', id).click(function () {
                        WebSocketSendMain.controller('friends', 'delete', {'id': $(this).attr('id')})
                    }))
                )
            )
        }
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        createFriends(r.friends)
    }
    this.delete = function (r) {
        $('#friendsList #' + r.id).remove()
        if ($('#friendsList td').length == 0) {
            addNoFriends()
        }
    }
}