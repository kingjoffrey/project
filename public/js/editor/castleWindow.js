var CastleWindow = new function () {
    this.form = function (id) {
        var selectColor = $('<select>').attr('name', 'color'),
            selectDefence = $('<select>').attr('name', 'defence'),
            selectProductionUnit1 = $('<select>').attr('name', 'unitId0').append($('<option>').attr('value', 0)),
            selectProductionUnit2 = $('<select>').attr('name', 'unitId1').append($('<option>').attr('value', 0)),
            selectProductionUnit3 = $('<select>').attr('name', 'unitId2').append($('<option>').attr('value', 0)),
            selectProductionUnit4 = $('<select>').attr('name', 'unitId3').append($('<option>').attr('value', 0)),
            selectProductionTime1 = $('<select>').attr('name', 'time0').append($('<option>').attr('value', 0)),
            selectProductionTime2 = $('<select>').attr('name', 'time1').append($('<option>').attr('value', 0)),
            selectProductionTime3 = $('<select>').attr('name', 'time2').append($('<option>').attr('value', 0)),
            selectProductionTime4 = $('<select>').attr('name', 'time3').append($('<option>').attr('value', 0))

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

                console.log(castle.getProduction())

                for (var unitId in Units.toArray()) {
                    var unit = Units.get(unitId)

                    selectProductionUnit1.append($('<option>').attr('value', unitId).html(unit.name_lang))
                    selectProductionUnit2.append($('<option>').attr('value', unitId).html(unit.name_lang))
                    selectProductionUnit3.append($('<option>').attr('value', unitId).html(unit.name_lang))
                    selectProductionUnit4.append($('<option>').attr('value', unitId).html(unit.name_lang))
                }

                for (var i = 1; i <= 20; i++) {
                    selectProductionTime1.append($('<option>').attr('value', i).html(i))
                    selectProductionTime2.append($('<option>').attr('value', i).html(i))
                    selectProductionTime3.append($('<option>').attr('value', i).html(i))
                    selectProductionTime4.append($('<option>').attr('value', i).html(i))
                }
            } else {
                selectColor.append($('<option>').attr('value', color).html(color))
            }
        }

        var html = $('<div>')
            .append($('<div>').append('Name:').append($('<input>').attr({
                'name': 'name',
                'value': castle.getName()
            })))
            .append($('<div>').append('Income:').append($('<input>').attr({
                'name': 'income',
                'value': castle.getIncome()
            })))
            .append($('<div>').append('Color:').append(selectColor))
            .append($('<div>').append('Defence:').append(selectDefence))
            .append($('<div>').append('Capital:').append($('<input>').attr({
                'name': 'capital',
                'type': 'checkbox'
            }).prop('checked', castle.getCapital())))
            .append($('<div>').append('Production unit 1:').append(selectProductionUnit1).append('Production time 1:').append(selectProductionTime1))
            .append($('<div>').append('Production unit 2:').append(selectProductionUnit2).append('Production time 2:').append(selectProductionTime2))
            .append($('<div>').append('Production unit 3:').append(selectProductionUnit3).append('Production time 3:').append(selectProductionTime3))
            .append($('<div>').append('Production unit 4:').append(selectProductionUnit4).append('Production time 4:').append(selectProductionTime4))
            .append($('<div>').append($('<input>').attr({'value': 'Ok', 'type': 'submit'}).click(function () {
                WebSocketEditor.edit(id)
            })))

        return html
    }
}
