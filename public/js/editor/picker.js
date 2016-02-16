var Picker = new function () {
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
        if (isSet(intersects[0])) {
            switch (event.button) {
                case 0:
                    // add item
                    var field = PickerCommon.getField()
                    if (draggedMesh) {
                        if (draggedMesh.itemName == 'eraser') {
                            WebSocketEditor.remove(PickerCommon.convertX(), PickerCommon.convertZ())
                        } else {
                            draggedMeshX = PickerCommon.convertX()
                            draggedMeshZ = PickerCommon.convertZ()
                            WebSocketEditor.add(draggedMesh.itemName, draggedMeshX, draggedMeshZ)
                        }
                    } else {
                        if (castleId = field.getCastleId()) {
                            Message.show('Castle', CastleWindow.form(castleId))
                        } else if (towerId = field.getTowerId()) {
                            Message.show('Tower', towerId)
                        } else if (ruinId = field.getRuinId()) {
                            Message.show('Ruin', ruinId)
                        }

                    }
                    break

                case 1:
                    // middle button
                    break

                case 2:
                    // remove mesh
                    if (draggedMesh) {
                        Scene.remove(draggedMesh)
                        draggedMesh = 0
                    }
                    Message.remove()
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
                }
                if (newZ != oldZ) {
                    oldZ = newZ
                    draggedMesh.position.z = newZ * 2 + draggedMesh.plus
                }
            }
        }
    }
    this.onContainerMouseUp = function (event) {
        event.preventDefault();
    }
}
