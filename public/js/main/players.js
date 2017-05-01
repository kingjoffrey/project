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
            $('#searchResults').append($('<tr>').append($('<td colspan="3">').html(translations.Nosearchresults).addClass('after')))
        },
        addPlayer = function (player, id) {
            $('#searchResults').append($('<tr>').attr('id', id)
                .append($('<td>').html(player))
                .append($('<td>').html(
                    $('<a>').addClass('write').html(translations.write)
                        .click(function () {
                            var playerId = $(this).parent().parent().attr('id')
                            MessagesController.setPlayerId(playerId)
                            WebSocketSendMain.controller('messages', 'thread', {'id': playerId})
                        })
                    )
                )
                .append($('<td>').html(
                    $('<a>').addClass('add').html(translations.Addtofriends)
                        .click(function () {
                            WebSocketSendMain.controller('friends', 'add', {'id': $(this).parent().parent().attr('id')})
                        })
                    )
                )
            )
        }
    this.index = function (r) {
        $('#content').html(r.data)

        createPlayers(r.players)

        $('form#search').submit(function (e) {
            e.preventDefault()
            WebSocketSendMain.controller('players', 'index', {'search': $('input#search').val()})
        })
    }
}