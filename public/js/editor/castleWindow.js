var EditorCastleWindow = new function () {
    this.form = function (id) {

        var selectColor = $('<select>').attr('name', 'color'),
            selectDefence = $('<select>').attr('name', 'defence'),
            selectProductionUnit0 = $('<select>').attr('name', 'unitId0').append($('<option>').attr('value', 0)),
            selectProductionUnit1 = $('<select>').attr('name', 'unitId1').append($('<option>').attr('value', 0)),
            selectProductionUnit2 = $('<select>').attr('name', 'unitId2').append($('<option>').attr('value', 0)),
            selectProductionTime0 = $('<select>').attr('name', 'time0').append($('<option>').attr('value', 0)),
            selectProductionTime1 = $('<select>').attr('name', 'time1').append($('<option>').attr('value', 0)),
            selectProductionTime2 = $('<select>').attr('name', 'time2').append($('<option>').attr('value', 0))

        for (var color in Players.toArray()) {
            if (Players.get(color).getCastles().has(id)) {
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

        var html = $('<div>').addClass('editorCastleWindow')
            .append($('<div>').append('Name: ' + castle.getName()))
            .append($('<div>').append('Income: ' + income))
            .append($('<div>').append('Color: ').append(selectColor))
            .append($('<div>').append('Defence: ').append(selectDefence))
            .append($('<div>').append('Capital: ').append($('<input>').attr({
                'name': 'capital',
                'type': 'checkbox'
            }).prop('checked', castle.getCapital())))
            .append($('<div>').append('Production unit 1: ').append(selectProductionUnit0).append(' Production time 1: ').append(selectProductionTime0))
            .append($('<div>').append('Production unit 2: ').append(selectProductionUnit1).append(' Production time 2: ').append(selectProductionTime1))
            .append($('<div>').append('Production unit 3: ').append(selectProductionUnit2).append(' Production time 3: ').append(selectProductionTime2))
            .append($('<div>').append('Enclave number:').append($('<input>').attr({
                'name': 'enclaveNumber',
                'value': castle.getEnclaveNumber()
            })))
            .append($('<div>').append($('<input>').attr({'value': 'Ok', 'type': 'submit'}).click(function () {
                WebSocketSendEditor.editCastle(id)
            })))

        return html
    }
}
