var EditorCastleWindow = new function () {
    this.form = function (id) {

        var selectColor = $('<select>').attr('name', 'color'),
            selectDefence = $('<select>').attr('name', 'defence'),
            selectProductionUnit0 = $('<select>').attr('name', 'unitId0').append($('<option>').attr('value', 0)),
            selectProductionUnit1 = $('<select>').attr('name', 'unitId1').append($('<option>').attr('value', 0)),
            selectProductionUnit2 = $('<select>').attr('name', 'unitId2').append($('<option>').attr('value', 0)),
            selectProductionTime0 = $('<select>').attr('name', 'time0').append($('<option>').attr('value', 0)),
            selectProductionTime1 = $('<select>').attr('name', 'time1').append($('<option>').attr('value', 0)),
            selectProductionTime2 = $('<select>').attr('name', 'time2').append($('<option>').attr('value', 0)),
            hasCapital = false,
            playerColor

        for (var color in Players.toArray()) {
            if (Players.get(color).getCastles().has(id)) {
                playerColor = color

                selectColor.append($('<option>').attr({'value': color, 'selected': 'selected'}).html(color))
                var castle = Players.get(color).getCastles().get(id)

                for (var i = 1; i <= 4; i++) {
                    if (castle.getDefense() == i) {
                        selectDefence.append($('<option>').attr({'value': i, 'selected': 'selected'}).html(i))
                    } else {
                        selectDefence.append($('<option>').attr('value', i).html(i))
                    }
                }

                for (var unitId in Units.toArray()) {
                    var unit = Units.get(unitId)
                    if (unit.special) {
                        continue
                    }

                    if (castle.getProduction()[0] && castle.getProduction()[0]['unitId'] == unitId) {
                        selectProductionUnit0.append($('<option>').attr({
                            'value': unitId,
                            'selected': 'selected'
                        }).html(unit.name_lang))
                    } else {
                        selectProductionUnit0.append($('<option>').attr('value', unitId).html(unit.name_lang))
                    }
                    if (castle.getProduction()[1] && castle.getProduction()[1]['unitId'] == unitId) {
                        selectProductionUnit1.append($('<option>').attr({
                            'value': unitId,
                            'selected': 'selected'
                        }).html(unit.name_lang))
                    } else {
                        selectProductionUnit1.append($('<option>').attr('value', unitId).html(unit.name_lang))

                    }
                    if (castle.getProduction()[2] && castle.getProduction()[2]['unitId'] == unitId) {
                        selectProductionUnit2.append($('<option>').attr({
                            'value': unitId,
                            'selected': 'selected'
                        }).html(unit.name_lang))
                    } else {
                        selectProductionUnit2.append($('<option>').attr('value', unitId).html(unit.name_lang))
                    }
                }

                for (var time = 1; time <= 20; time++) {
                    if (castle.getProduction()[0] && castle.getProduction()[0]['time'] == time) {
                        selectProductionTime0.append($('<option>').attr({
                            'value': time,
                            'selected': 'selected'
                        }).html(time))
                    } else {
                        selectProductionTime0.append($('<option>').attr('value', time).html(time))
                    }
                    if (castle.getProduction()[1] && castle.getProduction()[1]['time'] == time) {
                        selectProductionTime1.append($('<option>').attr({
                            'value': time,
                            'selected': 'selected'
                        }).html(time))
                    } else {
                        selectProductionTime1.append($('<option>').attr('value', time).html(time))
                    }
                    if (castle.getProduction()[2] && castle.getProduction()[2]['time'] == time) {
                        selectProductionTime2.append($('<option>').attr({
                            'value': time,
                            'selected': 'selected'
                        }).html(time))
                    } else {
                        selectProductionTime2.append($('<option>').attr('value', time).html(time))
                    }
                }
                if (!castle.getDefense()) {
                    castle.setDefense(1)
                }
                var income = castle.getIncome()
                if (notSet(income)) {
                    income = 0
                }
            } else {
                selectColor.append($('<option>').attr('value', color).html(color))
            }
        }

        if (playerColor == 'neutral')
            var capitalCheckbox = $('<input>').attr({
                'name': 'capital',
                'type': 'checkbox',
                'disabled': true
            }).prop('checked', 0)
        else if (Players.get(playerColor).getCapitalId() && !Players.get(playerColor).isCapital(id)) {
            var capitalCheckbox = $('<input>').attr({
                'name': 'capital',
                'type': 'checkbox',
                'disabled': true
            }).prop('checked', 0)
        } else {
            if (Players.get(playerColor).isCapital(id)) {
                var isCapital = true
            } else {
                var isCapital = false
            }
            var capitalCheckbox = $('<input>').attr({
                'name': 'capital',
                'type': 'checkbox'
            }).prop('checked', isCapital)
        }

        var html = $('<div>').addClass('editorCastleWindow')
            .append($('<div>').append('Name: ' + castle.getName()))
            .append($('<div>').append('Income: ' + income))
            .append($('<div>').append('Color: ').append(selectColor).change(function () {
                var capital = Boolean($('input[name=capital]').is(':checked')),
                    color = $('select[name=color]').val(),
                    hasCapital = false

                if (color == 'neutral') {
                    $('input[name=capital]').prop('checked', 0).attr('disabled', true)
                } else {
                    for (var castleId in Players.get(color).getCastles().toArray()) {
                        if (id == castleId) {
                            continue
                        }

                        if (Players.get(color).getCastles().get(castleId).getCapital()) {
                            hasCapital = true
                            $('input[name=capital]').prop('checked', 0).attr('disabled', true)
                            break
                        }
                    }

                    if (!hasCapital) {
                        $('input[name=capital]').attr('disabled', false)
                    }
                }
            }))
            .append($('<div>').append('Defence: ').append(selectDefence))
            .append($('<div>').append('Capital: ').append(capitalCheckbox))
            .append($('<div>').append('Unit 1: ').append(selectProductionUnit0).append(' Time 1: ').append(selectProductionTime0))
            .append($('<div>').append('Unit 2: ').append(selectProductionUnit1).append(' Time 2: ').append(selectProductionTime1))
            .append($('<div>').append('Unit 3: ').append(selectProductionUnit2).append(' Time 3: ').append(selectProductionTime2))
            .append($('<div>').append('Enclave number: ').append($('<input>').attr({
                'name': 'enclaveNumber',
                'value': castle.getEnclaveNumber()
            })))

        var msgId = Message.show(translations.castle, html)
        Message.addButton(msgId, 'Save', function () {
            WebSocketSendEditor.editCastle(id)
        })
        Message.addButton(msgId, 'close')
    }
}
