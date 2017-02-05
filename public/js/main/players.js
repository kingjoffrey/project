"use strict"
var PlayersController = new function () {
    var createPlayers = function (players) {
            for (var id in players) {
                addPlayer(players[id], id)
            }
            if (notSet(id)) {
                addNoPlayers()
            }
        },
        addNoPlayers = function () {
            $('#searchResults').after($('<div>').html(translations.Nosearchresults).addClass('after'))
        },
        addPlayer = function (player, id) {
            $('#searchResults').append($('<tr>')
                .append($('<td>').html(player))
                .append($('<td>').html($('<a>').attr('id', id).html(translations.write).click(function () {
                        var playerId = $(this).attr('id')
                        MessagesController.setPlayerId(playerId)
                        WebSocketSendMain.controller('messages', 'thread', {'id': playerId})
                    }))
                )
                .append($('<td>').append($('<div>').attr('id', id).html(translations.Addtofriends).click(function () {
                        WebSocketSendMain.controller('friends', 'add', {'id': $(this).attr('id')})
                    }))
                )
            )
        }
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        createPlayers(r.players)

        $('form#search').submit(function (e) {
            e.preventDefault()
            WebSocketSendMain.controller('players', 'index', {'search': $('input#search').val()})
        })
    }
}