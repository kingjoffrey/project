"use strict"
var WebSocketMessage = new function () {
    this.switch = function (r) {
        console.log(r)
        switch (r.type) {
            case 'init':
                Editor.init(r)
                break
            case 'castleId':
                Players.get('neutral').getCastles().add(r.value, {
                    x: Picker.getX(),
                    y: Picker.getZ(),
                    name: 'Unknown',
                    defense: 1,
                    production: [null, null, null, null]
                })
                break
            case 'towerId':
                Players.get('neutral').getTowers().add(r.value, {x: Picker.getX(), y: Picker.getZ()})
                break
            case 'ruinId':
                Ruins.add(r.value, new Ruin({x: Picker.getX(), y: Picker.getZ(), empty: 0}))
                break
            case 'edit':
                Message.remove()
                for (var color in Players.toArray()) {
                    if (Players.get(color).getCastles().has(r.castleId)) {
                        var castle = Players.get(color).getCastles().get(r.castleId)
                        Players.get(color).getCastles().clear(r.castleId)
                        break
                    }
                }
                castle.token.x = castle.getX()
                castle.token.y = castle.getY()
                Players.get(r.color).getCastles().add(r.castleId, castle.token)
                break
            case 'remove':
                var field = Fields.get(r.x, r.y)
                if (field.getCastleId()) {
                    Players.get(field.getCastleColor()).getCastles().destroy(field.getCastleId())
                    Fields.initCastle(r.x, r.y, null, null)
                } else if (field.getTowerId()) {
                    Players.get(field.getTowerColor()).getTowers().destroy(field.getTowerId())
                    field.setTower(null, null)
                } else if (field.getRuinId()) {
                    Ruins.destroy(field.getRuinId())
                    field.setRuinId(null)
                }
                break
            case 'grass':
                Fields.get(r.x, r.y).setType('g')
                var children = Scene.get().children
                for (var i in children) {
                    if (children[i].position.x - 0.5 == r.x && children[i].position.z - 0.5 == r.y) {
                        Scene.remove(children[i])
                        break
                    }
                }
                break
            case 's':
                Fields.get(r.x, r.y).setType(r.type)
                Models.addSwamp(r.x, r.y)
                break
            case 'f':
                Fields.get(r.x, r.y).setType(r.type)
                Models.addTree(r.x, r.y)
                break
            case 'r':
                Fields.get(r.x, r.y).setType(r.type)
                Models.addRoad(r.x, r.y)
                break
            case 'b':
                Fields.get(r.x, r.y).setType(r.type)
                Models.addRoad(r.x, r.y)
                break
            case 'g':
                Fields.get(r.x, r.y).setType(r.type)
                Ground.change(r.x, r.y, r.type)
                break
            case 'h':
                Fields.get(r.x, r.y).setType(r.type)
                Ground.change(r.x, r.y, r.type)
                break
            case 'm':
                Fields.get(r.x, r.y).setType(r.type)
                Ground.change(r.x, r.y, r.type)
                break
            case 'w':
                Fields.get(r.x, r.y).setType(r.type)
                Ground.change(r.x, r.y, r.type)
                break
        }
    }
}
