var CastleWindow = new function () {
    this.form = function (id) {
        var selectColor = $('<select>').attr('name', 'color'),
            selectDefence = $('<select>').attr('name', 'defence')

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
            .append($('<div>').append($('<input>').attr({'value': 'Ok', 'type': 'submit'}).click(function () {
                WebSocketEditor.edit(id)
            })))

        return html
    }
}
