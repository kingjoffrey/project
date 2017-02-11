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
            $('#friendsList')
                .append(
                    $('<tr>')
                        .append(
                            $('<td colspan="4">').addClass('after')
                                .append(translations.YouDontHaveFriends + ': ')
                                .append($('<span>').attr('id', 'findFriends').html(translations.findSomeFriends))
                        )
                )
                .click(function () {
                    WebSocketSendMain.controller('players', 'index')
                })
        },
        addFriend = function (friend, id) {
            $('#friendsList').append($('<tr>').attr('id', id)
                .append($('<td>').append($('<div>').addClass('online')))
                .append($('<td>').html(friend))
                .append($('<td>').html($('<a>').html(translations.write)
                    .click(function () {
                        var playerId = $(this).parent().parent().attr('id')
                        MessagesController.setPlayerId(playerId)
                        WebSocketSendMain.controller('messages', 'thread', {'id': playerId})
                    }))
                )
                .append(
                    $('<td>')
                        .html(
                            $('<div>').addClass('iconButton buttonColors')
                                .click(function () {
                                    WebSocketSendMain.controller('friends', 'delete', {'id': $(this).parent().parent().attr('id')})
                                })
                                .html($('<div>').addClass('trash'))
                        )
                )
            )
        }
    this.index = function (r) {
        $('#content').html(r.data)

        createFriends(r.friends)
    }
    this.delete = function (r) {
        $('#friendsList #' + r.id).remove()
        if ($('#friendsList td').length == 0) {
            addNoFriends()
        }
    }
}