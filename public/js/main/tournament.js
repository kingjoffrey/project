"use strict"
var TournamentController = new function () {
    this.index = function (r) {
        $('#content').html(r.data)

        for (var i in r.list) {
            var tournament = r.list[i]


            $('#tournamentList').append(
                $('<tr>').addClass('trlink').attr('id', tournament.tournamentId)
                    .append($('<td>').html(tournament.start))
                    .append($('<td>').html(tournament.start))
                    .append($('<td>').html(tournament.limit))
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
    this.show = function (r) {
        $('#content').html(r.data)

        $('#paypal').click(function () {
            WebSocketSendMain.controller('tournament', 'create', {
                'id': 1,
                'name': 'Tournament',
                'url': window.location.href
            })
        })
    }
    this.create = function (r) {
        window.location.href = r.url
    }
}
