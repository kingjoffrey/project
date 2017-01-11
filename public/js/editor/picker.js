var PickerEditor = new function () {
    var draggedMesh = 0,
        oldX = 0,
        oldZ = 0,
        draggedMeshX,
        draggedMeshZ

    this.addDraggedMesh = function (mesh) {
        draggedMesh = mesh
        if (draggedMesh.itemName == 'castle') {
            draggedMesh.plus = 2
        } else {
            draggedMesh.plus = 1
        }

    }
    this.getDraggedMesh = function () {
        return draggedMesh
    }
    this.getX = function () {
        return draggedMeshX
    }
    this.getZ = function () {
        return draggedMeshZ
    }

    this.onContainerMouseDown = function (event) {
        PickerCommon.intersect(event)
        if (PickerCommon.intersects()) {
            switch (event.button) {
                case 0:
                    // add item
                    var field = PickerCommon.getField()
                    if (draggedMesh) {
                        switch (draggedMesh.itemName) {
                            case 'eraser':
                                WebSocketSendEditor.remove(PickerCommon.convertX(), PickerCommon.convertZ())
                                break
                            case 'up':
                                WebSocketSendEditor.up(PickerCommon.convertX(), PickerCommon.convertZ())
                                break
                            case 'down':
                                WebSocketSendEditor.down(PickerCommon.convertX(), PickerCommon.convertZ())
                                break
                            default:
                                draggedMeshX = PickerCommon.convertX()
                                draggedMeshZ = PickerCommon.convertZ()
                                WebSocketSendEditor.add(draggedMesh.itemName, draggedMeshX, draggedMeshZ)
                        }
                    } else {
                        if (castleId = field.getCastleId()) {
                            EditorMessage.show('Castle', EditorCastleWindow.form(castleId))
                        } else if (towerId = field.getTowerId()) {
                            EditorMessage.show('Tower', towerId)
                        } else if (ruinId = field.getRuinId()) {
                            EditorMessage.show('Ruin', ruinId)
                        }

                    }
                    break

                case 1:
                    // middle button
                    break

                case 2:
                    // remove mesh
                    if (draggedMesh) {
                        GameScene.remove(draggedMesh)
                        draggedMesh = 0
                    }
                    EditorMessage.remove()
                    break
            }
        }
    }
    this.onContainerMouseMove = function (event) {
        PickerCommon.intersect(event)
        if (PickerCommon.intersects()) {
            if (draggedMesh) {
                var newX = PickerCommon.convertX(),
                    newZ = PickerCommon.convertZ()

                if (newX != oldX) {
                    oldX = newX
                    draggedMesh.position.x = newX * 2 + draggedMesh.plus
                    //console.log(oldX + '-' + oldZ)
                }
                if (newZ != oldZ) {
                    oldZ = newZ
                    draggedMesh.position.z = newZ * 2 + draggedMesh.plus
                    //console.log(oldX + '-' + oldZ)
                }
            }
        }
    }
    this.onContainerMouseUp = function (event) {
        event.preventDefault();
    }
}
