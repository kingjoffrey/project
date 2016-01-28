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
            .append($('<input>').attr(name, 'name'))
            .append($('<input>').attr(name, 'name'))
            .append($('<select>').attr(name, 'color'))
            .append($('<select>').attr(name, 'defence'))
            .append($('<submit>').attr('value', 'ok'))
            .append($('<hidden>').attr({name: 'id', value: id}))

        return html.html()
    }
}