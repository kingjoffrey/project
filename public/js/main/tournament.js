"use strict"
var TournamentController = new function () {
    this.index = function (r) {
        $('#content').html(r.data)

        for (var i in r.tournaments) {
            var tournament = r.tournaments[i]


            $('#tournamentList').append(
                $('<tr>').addClass('trlink').attr('id', tournament.tournamentId)
                    .append($('<div>').html(tournament.begin))
                    .append($('<td>').html(tournament.name))
                    .append($('<div>').html(tournament.end))
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

    }
}
