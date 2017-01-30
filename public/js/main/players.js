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
            $('#searchResults').append($('<tr>').append($('<td>').html(translations.Nosearchresults))
            )
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

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
        $('form#search').submit(function (e) {
            e.preventDefault()
            WebSocketSendMain.controller('players', 'index', {'search': $('input#search').val()})
        })
    }
}