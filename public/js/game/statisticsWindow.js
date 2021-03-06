var StatisticsWindow = new function () {
    this.show = function (r) {
        var table = $('<table>')
            .addClass('statistics')
            .append($('<tr>')
                .append($('<th colspan="4">').html(translations.Castles))
                .append($('<th colspan="3">').html(translations.Units))
                .append($('<th colspan="2">').html(translations.Heroes))
            )
            .append($('<tr>')
                .append($('<th>').html(translations.Held).addClass('second'))
                .append($('<th>').html(translations.Conquered).addClass('second'))
                .append($('<th>').html(translations.Lost1).addClass('second'))
                .append($('<th>').html(translations.Razed).addClass('second'))
                .append($('<th>').html(translations.Created).addClass('second'))
                .append($('<th>').html(translations.Killed1).addClass('second'))
                .append($('<th>').html(translations.Lost1).addClass('second'))
                .append($('<th>').html(translations.Killed2).addClass('second'))
                .append($('<th>').html(translations.Lost2).addClass('second'))
            )

        for (var color in Players.toArray()) {
            var tr = $('<tr>'),
                player = Players.get(color),
                numberOfCastlesHeld = player.getCastles().count(),
                backgroundColor = player.getBackgroundColor()


            var td = $('<td>').addClass('shortName');

            var td = $('<td>').css({
                'border-color': backgroundColor
            })

            if (numberOfCastlesHeld > 0) {
                tr.append(td.html(numberOfCastlesHeld))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                'border-color': backgroundColor
            })
            if (isSet(r.castlesConquered.winners[color])) {
                tr.append(td.html(r.castlesConquered.winners[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                'border-color': backgroundColor
            })
            if (isSet(r.castlesConquered.losers[color])) {
                tr.append(td.html(r.castlesConquered.losers[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                'border-color': backgroundColor
            })
            if (isSet(r.castlesDestroyed[color])) {
                tr.append(td.html(r.castlesDestroyed[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                'border-color': backgroundColor
            })
            if (isSet(r.soldiersCreated[color])) {
                tr.append(td.html(r.soldiersCreated[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                'border-color': backgroundColor
            })
            if (isSet(r.soldiersKilled.winners[color])) {
                tr.append(td.html(r.soldiersKilled.winners[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                'border-color': backgroundColor
            })
            if (isSet(r.soldiersKilled.losers[color])) {
                tr.append(td.html(r.soldiersKilled.losers[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                'border-color': backgroundColor
            })
            if (isSet(r.heroesKilled.winners[color])) {
                tr.append(td.html(r.heroesKilled.winners[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                'border-color': backgroundColor
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