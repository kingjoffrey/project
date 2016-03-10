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
            walk = CommonMe.getSelectedArmy().getWalkingSoldiers(),
            swim = CommonMe.getSelectedArmy().getSwimmingSoldiers(),
            fly = CommonMe.getSelectedArmy().getFlyingSoldiers(),
            heroes = CommonMe.getSelectedArmy().getHeroes()

        for (var soldierId in walk) {
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Unit.getImage(walk[soldierId].unitId, CommonMe.getColor()),
                            'id': 'unit' + soldierId
                        })
                    ))
                    .append($('<span>').html(translations.movesLeft + ': ' + walk[soldierId].movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').css('display','none').attr({
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
                            'src': Unit.getImage(soldier.unitId, CommonMe.getColor()),
                            'id': 'unit' + soldierId
                        })
                    ))
                    .append($('<span>').html(translations.movesLeft + ': ' + soldier.movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').css('display','none').attr({
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
        for (var soldierId in fly) {
            var soldier = fly[soldierId]
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Unit.getImage(soldier.unitId, CommonMe.getColor()),
                            'id': 'unit' + soldierId
                        })
                    ))
                    .append($('<span>').html(translations.movesLeft + ': ' + soldier.movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').css('display','none').attr({
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
        for (var heroId in heroes) {
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Hero.getImage(CommonMe.getColor()),
                            'id': 'hero' + heroId
                        })
                    ))
                    .append($('<span>').html(translations.movesLeft + ': ' + heroes[heroId].movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').css('display','none').attr({
                        type: 'checkbox',
                        name: 'heroId',
                        value: heroId
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

        var id = Message.show(translations.splitArmy, div);
        Message.cancel(id)
        Message.ok(id, WebSocketSend.split)
        Message.setOverflowHeight(id)
    }
}