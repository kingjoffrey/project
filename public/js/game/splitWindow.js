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
            walk = Me.getSelectedArmy().getWalkingSoldiers(),
            swim = Me.getSelectedArmy().getSwimmingSoldiers(),
            fly = Me.getSelectedArmy().getFlyingSoldiers(),
            heroes = Me.getSelectedArmy().getHeroes()

        for (var soldierId in walk) {
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(Units.get(walk[soldierId].unitId).name_lang))
                    .append($('<span>').html(' : ' + walk[soldierId].movesLeft + '/' + Units.get(walk[soldierId].unitId).moves))
                    .append($('<div>').addClass('right').html($('<input>').css('display', 'none').attr({
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
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(Units.get(swim[soldierId].unitId).name_lang))
                    .append($('<span>').html(' : ' + swim[soldierId].movesLeft + '/' + Units.get(swim[soldierId].unitId).moves))
                    .append($('<div>').addClass('right').html($('<input>').css('display', 'none').attr({
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
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(Units.get(fly[soldierId].unitId).name_lang))
                    .append($('<span>').html(' : ' + fly[soldierId].movesLeft + '/' + Units.get(fly[soldierId].unitId).moves))
                    .append($('<div>').addClass('right').html($('<input>').css('display', 'none').attr({
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
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(translations.hero))
                    .append($('<span>').html(' : ' + heroes[heroId].movesLeft + '/' + heroes[heroId].moves))
                    .append($('<div>').addClass('right').html($('<input>').css('display', 'none').attr({
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
        Message.ok(id, WebSocketSendGame.split)
    }
}