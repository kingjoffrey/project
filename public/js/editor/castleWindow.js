var CastleWindow = new function () {
    this.create = function (X, Y, x, y) {
        var img = new Image()
        img.src = '/img/game/castles/neutral.png'
        img.onload = function () {
            var castle = new Kinetic.Image({
                x: X,
                y: Y,
                image: img
            })

            castle.on('mouseup touchend', function (e) {
                if (e.which == 1) {
                    var x = castle.getPosition().x / 40
                    var y = castle.getPosition().y / 40
                    WebSocketEditor.castleRemove(x, y)
                    castle.remove()
                    Editor.group.draw()
                }
            })

            Editor.group.add(castle)
            Editor.group.draw()

            if (typeof x != 'undefined' && typeof y != 'undefined') {
                WebSocketEditor.castleAdd(x, y)
            }
        }
    }
    this.form = function (id) {



        var html = $('<div>')
            .append($('<div>').append('Name:').append($('<input>').attr(name, 'name')))
            .append($('<div>').append('Color:').append($('<select>').attr(name, 'color').append($('<option>').attr('value', 1).html(1)).append($('<option>').attr('value', 2).html(2))))
            .append($('<div>').append('Defence:').append($('<select>').attr(name, 'defence').append($('<option>').attr('value', 1).html(1)).append($('<option>').attr('value', 2).html(2)).append($('<option>').attr('value', 3).html(3)).append($('<option>').attr('value', 4).html(4))))
            .append($('<div>').append($('<input>').attr({'value': 'Ok', type: 'submit'})))
            .append($('<hidden>').attr({name: 'id', value: id}))

        return html.html()
    }
}