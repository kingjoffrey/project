var PickerEditor = new function () {
    var draggedMesh = 0,
        oldX = 0,
        oldZ = 0,
        draggedMeshX,
        draggedMeshZ,
        dragStart = 0,
        move = 0,
        leftClick = 0,
        scaling = false,
        dist = 0,
        handleDownStart = function (event) {
            leftClick = 1
            move = 0
            dragStart = PickerCommon.getPoint(event)
            PickerCommon.intersect(event)
        },
        handleUp = function (event) {
            dragStart = 0
            if (!leftClick) {
                return
            }
            if (!move && PickerCommon.intersects()) {
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
                    var id = 0
                    if (id = field.getCastleId()) {
                        Message.show('Castle', EditorCastleWindow.form(id))
                    } else if (id = field.getRuinId()) {
                        Message.show('Ruin', EditorRuinWindow.form(id))
                    }
                }
            }
        },
        handleMove = function (event) {
            PickerCommon.intersect(event)
            if (dragStart) {
                var dragEnd = PickerCommon.getPoint(event)
                if (!PickerCommon.checkOffset(dragStart, dragEnd)) {
// drag
                    move = 1
                    GameScene.moveCamera(dragStart.x - dragEnd.x, dragStart.y - dragEnd.y)
                    dragStart = dragEnd
                    PickerCommon.cursor('move')
                }
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
        if (isTouchDevice()) {
            return
        }
        switch (event.button) {
            case 0:
                // add item
                handleDownStart(event)
                break

            case 1:
                // middle button
                break

            case 2:
                // remove mesh
                leftClick = 0
                if (draggedMesh) {
                    GameScene.remove(draggedMesh)
                    draggedMesh = 0
                }
                Message.remove()
                break
        }
    }
    this.onContainerTouchStart = function (event) {
        if (event.touches.length === 2) {
            scaling = true
            dist = 0
            dragStart = 0
        } else {
            event.offsetX = event.changedTouches[0].clientX
            event.offsetY = event.changedTouches[0].clientY
            handleDownStart(event)
        }
    }
    this.onContainerMouseMove = function (event) {
        if (isTouchDevice()) {
            return
        }
        handleMove(event)
    }
    this.onContainerTouchMove = function (event) {
        if (scaling) {
            var tmp = Math.hypot(
                event.touches[0].pageX - event.touches[1].pageX,
                event.touches[0].pageY - event.touches[1].pageY
            )

            if (tmp < dist) {
                GameScene.moveCameraAway()
            } else {
                GameScene.moveCameraClose()
            }

            dist = tmp
        } else {
            event.offsetX = event.changedTouches[0].clientX
            event.offsetY = event.changedTouches[0].clientY

            handleMove(event)
        }
    }
    this.onContainerMouseUp = function (event) {
        if (isTouchDevice()) {
            return
        }
        //console.log('up')
        event.preventDefault()
        handleUp(event)
    }
    this.onContainerMouseOut = function (event) {
        event.preventDefault()
        // handleUp(event)
    }
    this.onContainerTouchEnd = function (event) {
        //console.log('touchEnd')
        if (scaling) {
            scaling = false
        } else {
            handleUp(event)
        }
    }
}
