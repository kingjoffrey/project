"use strict"
var TournamentController = new function () {
    this.index = function (r) {
        $('#content').html(r.data)

        for (var i in r.list) {
            var tournament = r.list[i]

            if (tournament.finished) {
                var finished = translations.Yes
            } else {
                var finished = translations.No
            }

            $('#tournamentList').append(
                $('<tr>').addClass('trlink').attr('id', tournament.tournamentId)
                    .append($('<td>').html(tournament.start))
                    .append($('<td>').html(tournament.name))
                    .append($('<td>').html(tournament.limit))
                    .append($('<td>').html(finished))
                    .click(function () {
                        WebSocketSendMain.controller('tournament', 'show', {'id': $(this).attr('id')})
                    })
            )
        }

        if (notSet(i)) {
            $('#tournamentList')
                .append(
                    $('<tr>')
                        .append(
                            $('<td colspan="5">').addClass('after').html(translations.Nothingtoshow)
                        )
                )
        }
    }
    this.paypal = function (r) {
        $('#content').html(r.data)

        $('#paypal').click(function () {
            WebSocketSendMain.controller('paypal', 'create', {
                'id': r.id,
                'name': 'Tournament',
                'url': window.location.href
            })
        })
    }
    this.full = function (r) {
        $('#content').html(r.data)
    }
    this.play = function (r) {
        $('#content').html(r.data)

        $('#playTournament').click(function () {
            GameController.index({'gameId': r.id})
        })
    }
    this.list = function (r) {
        $('#content').html(r.data)

        var stagesList = {}

        for (var i in r.list) {
            var player = r.list[i],
                j = i * 1 + 1

            if (notSet(stagesList[player.stage])) {
                stagesList[player.stage] = 1
                $('#playerList')
                    .append(
                        $('<tr>').attr('id', 'stage' + player.stage)
                            .append($('<th colspan="2">').addClass('stage').html(translations.Stage + ' ' + player.stage))
                    )
                    // .append($('<tr>')
                    //     .append($('<th>'))
                    //     .append($('<th>').html(translations.Playername))
                    // )
            }

            $('#stage' + player.stage).after(
                $('<tr>').addClass('trlink').attr('id', player.id)
                    .append($('<td>').html(j))
                    .append($('<td>').html(player.name))
                    .click(function () {
                        WebSocketSendMain.controller('profile', 'show', {'id': $(this).attr('id')})
                    })
            )
        }

        if (notSet(i)) {
            $('#playerList').append(
                $('<tr>')
                    .append($('<td colspan="2">').addClass('after').html(translations.Nothingtoshow))
            )
        }
    }
    this.create = function (r) {
        window.location.href = r.url
    }
}
