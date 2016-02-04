var CastleWindow = new function () {
    this.form = function (id) {
        var selectColor = $('<select>').attr('name', 'color'),
            selectDefence = $('<select>').attr('name', 'defence'),
            castleColor

        for (color in Players.toArray()) {
            if (Players.get(color).getCastles().has(id)) {
                selectColor.append($('<option>').attr({'value': color, 'selected': 'selected'}).html(color))
                var castle = Players.get(color).getCastles().get(id),
                    castleColor = color
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
            .append($('<div>').append('Name:').append($('<input>').attr('name', castle.getName())))
            .append($('<div>').append('Color:').append(selectColor))
            .append($('<div>').append('Defence:').append(selectDefence))
            .append($('<div>').append($('<input>').attr({'value': 'Ok', 'type': 'submit'}).click(function () {
                console.log('bbb')
                var castle = Players.get(color).getCastles().get(id)
                WebSocketEditor.edit(id, castle.getName(), castleColor, castle.getDefense())
            })))

        return html.html()
    }
}
