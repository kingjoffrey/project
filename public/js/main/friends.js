"use strict"
var FriendsController = new function () {
    var friendId = 0,
        createFriends = function (friends) {
            for (var id in friends) {
                addFriend(friends[id], id)
            }
            if (notSet(id)) {
                addNoFriends()
            }
        },
        addNoFriends = function () {
            $('#friendsList').append($('<tr>').attr('id', id).addClass('friends')
                .append(translations.YouDontHaveFriends + ': ')
                .append($('<div>').attr('id', 'findFriends').html(translations.findSomeFriends))
                .click(function () {
                    WebSocketSendMain.controller('players', 'index')
                })
            )
        },
        addFriend = function (friend, id) {
            $('#friendsList').append($('<tr>').attr('id', id).addClass('friends')
                .append($('<td>').append($('<span>').attr('id', 'online')))
                .append($('<td>').html(friend))
                .append($('<td>').attr('id', 'trash').click(function () {
                        WebSocketSendMain.controller('friends', 'delete', {'id': $(this).parent().attr('id')})
                    })
                )
            )
        }
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        createFriends(r.friends)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
    }


}