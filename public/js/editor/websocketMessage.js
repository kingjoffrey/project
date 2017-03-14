"use strict"
var WebSocketMessageEditor = new function () {
    this.switch = function (r) {
        console.log(r)
        switch (r.type) {
            case 'open':
                Editor.init(r)
                break
            case 'castleId':
                Players.get('neutral').getCastles().add(r.value, {
                    x: PickerEditor.getX(),
                    y: PickerEditor.getZ(),
                    name: 'Unknown',
                    defense: 1,
                    production: [null, null, null, null]
                })
                break
            case 'towerId':
                Players.get('neutral').getTowers().add(r.value, {x: PickerEditor.getX(), y: PickerEditor.getZ()})
                break
            case 'ruinId':
                Ruins.add(r.value, new Ruin({x: PickerEditor.getX(), y: PickerEditor.getZ(), empty: 0}))
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
                    Players.get(field.getCastleColor()).getCastles().clear(field.getCastleId())
                    Fields.initCastle(r.x, r.y, null, null)
                } else if (field.getTowerId()) {
                    Players.get(field.getTowerColor()).getTowers().clear(field.getTowerId())
                    field.setTower(null, null)
                } else if (field.getRuinId()) {
                    Ruins.clear(field.getRuinId())
                    field.setRuinId(null)
                }
                break
            case 'grass':
                EditorGround.change(r.x, r.y, 'g')
                break
            case 'water':
                EditorGround.change(r.x, r.y, 'w')
                break
            case 's':
                EditorGround.change(r.x, r.y, r.type)
                break
            case 'f':
                EditorGround.change(r.x, r.y, r.type)
                break
            case 'r':
                EditorGround.change(r.x, r.y, r.type)
                break
            case 'b':
                EditorGround.change(r.x, r.y, r.type)
                break
            case 'g':
                EditorGround.change(r.x, r.y, r.type)
                break
            case 'h':
                EditorGround.change(r.x, r.y, r.type)
                break
            case 'm':
                EditorGround.change(r.x, r.y, r.type)
                break
            case 'w':
                EditorGround.change(r.x, r.y, r.type)
                break
        }
    }
}
