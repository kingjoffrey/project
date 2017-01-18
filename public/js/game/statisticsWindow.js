var StatisticsWindow = new function () {
    this.show = function (r) {
        var table = $('<table>')
            .addClass('statistics')
            .append($('<tr>')
                .append($('<th>').html(translations.castlesHeld))
                .append($('<th>').html(translations.castlesConquered))
                .append($('<th>').html(translations.castlesLost))
                .append($('<th>').html(translations.castlesRazed))
                .append($('<th>').html(translations.unitsCreated))
                .append($('<th>').html(translations.unitsKilled))
                .append($('<th>').html(translations.unitsLost))
                .append($('<th>').html(translations.heroesKilled))
                .append($('<th>').html(translations.heroesLost))
            )

        for (var color in Players.toArray()) {
            var tr = $('<tr>'),
                player = Players.get(color),
                numberOfCastlesHeld = player.getCastles().count(),
                backgroundColor = player.getBackgroundColor()


            var td = $('<td>').addClass('shortName');

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })

            if (numberOfCastlesHeld > 0) {
                tr.append(td.html(numberOfCastlesHeld))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })
            if (isSet(r.castlesConquered.winners[color])) {
                tr.append(td.html(r.castlesConquered.winners[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })
            if (isSet(r.castlesConquered.losers[color])) {
                tr.append(td.html(r.castlesConquered.losers[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })
            if (isSet(r.castlesDestroyed[color])) {
                tr.append(td.html(r.castlesConquered[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })
            if (isSet(r.soldiersCreated[color])) {
                tr.append(td.html(r.soldiersCreated[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })
            if (isSet(r.soldiersKilled.winners[color])) {
                tr.append(td.html(r.soldiersKilled.winners[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })
            if (isSet(r.soldiersKilled.losers[color])) {
                tr.append(td.html(r.soldiersKilled.losers[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })
            if (isSet(r.heroesKilled.winners[color])) {
                tr.append(td.html(r.heroesKilled.winners[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })
            if (isSet(r.heroesKilled.losers[color])) {
                tr.append(td.html(r.heroesKilled.losers[color]))
            } else {
                tr.append(td.html('0'))
            }

            table.append(tr)
        }

        Message.simple(translations.statistics, $('<div>').append(table))
    }
}