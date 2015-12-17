var Castle = new function () {
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
}