var SplitWindow = new function () {
    this.show = function () {
        var div = $('<div>')
                .addClass('split')
                .append($('<div>')
                    .html($('<input>')
                        .attr({
                            type: 'checkbox'
                        })
                        .change(function () {
                            $('.message .row').each(function () {
                                var input = $(this).find('input')
                                if (input.prop('checked')) {
                                    input.prop('checked', false)
                                    $(this).removeClass('selected')
                                } else {
                                    input.prop('checked', true)
                                    $(this).addClass('selected')
                                }
                            })
                        }))
                    .attr('id', 'selectAll')
            ),
            numberOfUnits = 0,
            walk = Me.getSelectedArmy().getWalkingSoldiers(),
            swim = Me.getSelectedArmy().getSwimmingSoldiers(),
            fly = Me.getSelectedArmy().getFlyingSoldiers(),
            heroes = Me.getSelectedArmy().getHeroes()

        for (var soldierId in walk) {
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Unit.getImage(walk[soldierId].unitId, Me.getColor()),
                            'id': 'unit' + soldierId
                        })
                    ))
                    .append($('<span>').html(translations.movesLeft + ': ' + walk[soldierId].movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').attr({
                        type: 'checkbox',
                        name: 'soldierId',
                        value: soldierId
                    })))
                    .click(function () {
                        var input = $(this).find('input')
                        if (input.prop('checked')) {
                            input.prop('checked', false)
                            $(this).removeClass('selected')
                        } else {
                            input.prop('checked', true)
                            $(this).addClass('selected')
                        }
                    })
            );
        }
        for (var soldierId in swim) {
            var soldier = swim[soldierId]
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Unit.getImage(soldier.unitId, Me.getColor()),
                            'id': 'unit' + soldierId
                        })
                    ))
                    .append($('<span>').html(translations.movesLeft + ': ' + soldier.movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').attr({
                        type: 'checkbox',
                        name: 'soldierId',
                        value: soldierId
                    })))
            );
        }
        for (var soldierId in fly) {
            var soldier = fly[soldierId]
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Unit.getImage(soldier.unitId, Me.getColor()),
                            'id': 'unit' + soldierId
                        })
                    ))
                    .append($('<span>').html(translations.movesLeft + ': ' + soldier.movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').attr({
                        type: 'checkbox',
                        name: 'soldierId',
                        value: soldierId
                    })))
            );
        }
        for (var heroId in heroes) {
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Hero.getImage(Me.getColor()),
                            'id': 'hero' + heroId
                        })
                    ))
                    .append($('<span>').html(translations.movesLeft + ': ' + heroes[heroId].movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').attr({
                        type: 'checkbox',
                        name: 'heroId',
                        value: heroId
                    })))
            );
        }

        var id = Message.show(translations.splitArmy, div);
        Message.cancel(id)
        Message.ok(id, Websocket.split)
    }
}