"use strict"
var LoadController = new function () {
    this.index = function (r) {
        $('#content').html(r.data)

        for (var i in r.games) {
            var game = r.games[i]

            var players = $('<div>')

            for (var j in game.players) {
                var player = game.players[j]
                players
                    .append(
                        $('<div>').addClass('playersName').html(player.firstName + ' ' + player.lastName)
                    )
                    .append(
                        $('<div>').addClass('playersColors')
                            .append(
                                $('<div>').addClass('colorBox').css('background', player.backgroundColor).html('&nbsp;')
                            )
                            .append(
                                $('<div>').addClass('colorBox').css('background', game.teams[player.team]).html('&nbsp;')
                            )
                    )
            }

            $('#gameList').append(
                $('<tr>').addClass('trlink').attr('id', game.gameId)
                    .append(
                        $('<td>')
                            .append(
                                $('<div>').addClass('padding').html(game.players[game.gameMasterId].firstName + ' ' + game.players[game.gameMasterId].lastName)
                            )
                            .append(
                                $('<div>').addClass('padding').html(game.begin)
                            )
                    )
                    .append($('<td>').html(game.name))
                    .append($('<td>').append(players))
                    .append(
                        $('<td>')
                            .append($('<div>').addClass('padding').html(game.turnNumber))
                            .append($('<div>').addClass('padding').html(game.playerTurn.firstName + ' ' + game.playerTurn.lastName))
                            .append($('<div>').addClass('padding').html(game.end))
                    )
            ).click(function () {
                GameController.index({'gameId': $(this).attr('id')})
            })
        }

        if (notSet(i)) {
            $('#gameList')
                .append(
                    $('<tr>')
                        .append(
                            $('<td colspan="4">').addClass('after').html(translations.Therearenogamestoload)
                        )
                )
        }
    }
}
