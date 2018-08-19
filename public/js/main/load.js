"use strict"
var LoadController = new function () {
    var gameListHeader = ''

    this.index = function (r) {
        $('#content').html(r.data)

        gameListHeader = $('#gameList').html()

        for (var id in r.menu) {
            $('#loadMenu')
                .append($('<div>')
                    .attr('id', id)
                    .html(translations[r.menu[id]])
                    .addClass('button buttonColors')
                    .click(function () {
                        Sound.play('click')

                        var id = $(this).attr('id')

                        $('#loadMenu div').each(function () {
                            $(this).removeClass('active')
                        })

                        $('#loadMenu div#' + id).addClass('active')

                        WebSocketSendMain.controller('load', 'content', {'m': id})
                    })
                )
        }

        WebSocketSendMain.controller('load', 'content', {'m': 3})

        $('#loadMenu div#3').addClass('active')

    }

    this.content = function (r) {
        $('#gameList').html(gameListHeader)

        var teams = {
            1: 'A',
            2: 'B'
        }

        for (var i in r.games) {
            var game = r.games[i]

            var players = $('<div>')

            for (var j in game.players) {
                var player = game.players[j]
                players
                    .append(
                        $('<div>')
                            .append(
                                $('<div>').addClass('playersName').html(player.name)
                            )
                            .append(
                                $('<div>').addClass('playersColors')
                                    .append(
                                        $('<div>').addClass('colorBox').css('background', player.backgroundColor).html('&nbsp;')
                                    )
                                    .append(
                                        $('<div>').addClass('colorBox').html(teams[player.teamId])
                                    )
                            )
                    )
            }

            $('#gameList').append(
                $('<tr>').addClass('trlink').attr('id', game.gameId)
                    .append(
                        $('<td>')
                            .append(game.name)
                            .append($('<div>').addClass('padding').html(game.end))
                    )
                    .append($('<td>').append(players))
                    .append(
                        $('<td>')
                            .append($('<div>').addClass('padding').html(game.turnNumber))
                            .append($('<div>').addClass('padding').html(game.playerTurn.name))
                    )
                    .append(
                        $('<td>')
                            .append($('<div>').addClass('iconButton buttonColors').html($('<div>').addClass('trash'))
                                .click(function (e) {
                                    e.preventDefault()
                                    e.stopPropagation()
                                    WebSocketSendMain.controller('load', 'delete', {'id': $(this).parent().parent().attr('id')})
                                })
                            )
                    )
                    .click(function () {
                        GameController.index({'gameId': $(this).attr('id')})
                    })
            )
        }

        if (notSet(i)) {
            $('#gameList')
                .append(
                    $('<tr>')
                        .append(
                            $('<td colspan="5">').addClass('after').html(translations.Therearenogamestoload)
                        )
                )
        }
    }

    this.delete = function (r) {
        WebSocketSendMain.controller('over', 'index', {'id': r.id})
    }
}
