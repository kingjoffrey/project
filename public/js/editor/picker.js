var PickerEditor = new function () {
    var draggedMesh = 0,
        oldX = 0,
        oldZ = 0,
        draggedMeshX,
        draggedMeshZ,
        dragStart = 0

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
                            Message.show('Castle', EditorCastleWindow.form(castleId))
                        } else {
                            dragStart = PickerCommon.getPoint(event)
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
                    Message.remove()
                    break
            }
        }
    }
    this.onContainerMouseMove = function (event) {
        PickerCommon.intersect(event)
        if (dragStart) {
            var dragEnd = PickerCommon.getPoint(event)
            GameScene.moveCamera(dragStart.x - dragEnd.x, dragStart.y - dragEnd.y)
            dragStart = dragEnd
            PickerCommon.cursor('move')
        } else {
            if (PickerCommon.intersects()) {
                AStar.cursorPosition(PickerCommon.convertX(), PickerCommon.convertZ())
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
                } else {
                    PickerCommon.cursor('grab')
                }
            } else {
                PickerCommon.cursor('grab')
            }
        }
    }
    this.onContainerMouseUp = function (event) {
        event.preventDefault()
        dragStart = 0
    }
}
