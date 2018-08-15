"use strict"
var WebSocketMessageEditor = new function () {
    this.switch = function (r) {
        console.log(r)
        switch (r.type) {
            case 'open':
                Editor.init(r)
                break

            case 'castle':
                PickerEditor.clearDraggedMesh()
                Players.get('neutral').getCastles().add(r.value.id, r.value)
                EditorGui.unlock()
                break

            case 'towerId':
                PickerEditor.clearDraggedMesh()
                Players.get('neutral').getTowers().add(r.value, {x: PickerEditor.getX(), y: PickerEditor.getZ()})
                EditorGui.unlock()
                break

            case 'ruinAdd':
                PickerEditor.clearDraggedMesh()
                Ruins.add(r.id, new Ruin({x: PickerEditor.getX(), y: PickerEditor.getZ(), empty: 0, type: 4}))
                EditorGui.unlock()
                break

            case 'editRuin':
                EditorGui.unlock()
                var ruin = Ruins.get(r.id)
                if (ruin) {
                    ruin.setType(r.ruinId)
                }
                break

            case 'editCastle':
                var castleId = r.castle.id
                Message.remove()
                for (var color in Players.toArray()) {
                    if (Players.get(color).getCastles().has(castleId)) {
                        Players.get(color).getCastles().clear(castleId)
                        break
                    }
                }

                Players.get(r.color).getCastles().add(castleId, r.castle)
                EditorGui.unlock()
                break

            case 'remove':
                EditorGui.unlock()
                var field = Fields.get(r.x, r.y)
                if (field.getCastleId()) {
                    Players.get(field.getCastleColor()).getCastles().raze(field.getCastleId())
                } else if (field.getTowerId()) {
                    Players.get(field.getTowerColor()).getTowers().clear(field.getTowerId())
                    field.setTower(null, null)
                } else if (field.getRuinId()) {
                    Ruins.clear(field.getRuinId())
                    field.setRuinId(null)
                }
                break
            case 'grass':
                EditorGui.unlock()
                EditorGround.change(r.x, r.y, 'g')
                break
            case 'water':
                EditorGui.unlock()
                EditorGround.change(r.x, r.y, 'w')
                break
            case 's':
                EditorGui.unlock()
                EditorGround.change(r.x, r.y, r.type)
                break
            case 'f':
                EditorGui.unlock()
                EditorGround.change(r.x, r.y, r.type)
                break
            case 'r':
                EditorGui.unlock()
                EditorGround.change(r.x, r.y, r.type)
                break
            case 'b':
                EditorGui.unlock()
                EditorGround.change(r.x, r.y, r.type)
                break
            case 'g':
                EditorGui.unlock()
                EditorGround.change(r.x, r.y, r.type)
                break
            case 'h':
                EditorGui.unlock()
                EditorGround.change(r.x, r.y, r.type)
                break
            case 'm':
                EditorGui.unlock()
                EditorGround.change(r.x, r.y, r.type)
                break
            case 'w':
                EditorGui.unlock()
                EditorGround.change(r.x, r.y, r.type)
                break
        }
    }
}
