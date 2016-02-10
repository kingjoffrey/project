var Picker = new function () {
    var raycaster = new THREE.Raycaster(),
        objects = [],
        intersects = [],
        detached = [],
        camera,
        container,
        draggedMesh = 0,
        oldX = 0,
        oldZ = 0,
        draggedMeshX,
        draggedMeshZ

    this.init = function () {
        camera = Scene.getCamera()
        container = Scene.getRenderer().domElement

        container.addEventListener('mousedown', onContainerMouseDown, false);
        container.addEventListener('mousemove', onContainerMouseMove, false);
        container.addEventListener('mouseup', onContainerMouseUp, false);
    }
    this.addDraggedMesh = function (mesh) {
        draggedMesh = mesh
        if (draggedMesh.itemName == 'castle') {
            draggedMesh.plus = 1
        } else {
            draggedMesh.plus = 0.5
        }

    }
    this.attach = function (object) {
        if (object instanceof THREE.Mesh) {
            objects.push(object);
        }
    }
    this.detach = function (object) {
        objects.splice(objects.indexOf(object), 1);
    }
    this.getX = function () {
        return draggedMeshX
    }
    this.getZ = function () {
        return draggedMeshZ
    }

    var intersect = function (event) {
            var x = event.offsetX == undefined ? event.layerX : event.offsetX,
                y = event.offsetY == undefined ? event.layerY : event.offsetY,
                vector = new THREE.Vector3(( x / container.width ) * 2 - 1, -( y / container.height ) * 2 + 1, 1)

            vector.unproject(camera)
            raycaster.set(camera.position, vector.sub(camera.position).normalize())
            intersects = raycaster.intersectObjects(objects, true)
        },
        onContainerMouseDown = function (event) {
            intersect(event)
            if (isSet(intersects[0])) {
                onclick(event.button)
            }
        },
        onContainerMouseMove = function (event) {
            intersect(event)
            if (isSet(intersects[0])) {
                mouseMove()
            }
        },
        onContainerMouseUp = function (event) {
            event.preventDefault();
        },
        getField = function () {
            return Fields.get(convertX(), convertZ())
        },
        convertX = function () {
            draggedMeshX = parseInt(intersects[0].point.x)
            return draggedMeshX
        },
        convertZ = function () {
            draggedMeshZ = parseInt(intersects[0].point.z)
            return draggedMeshZ
        },
        onclick = function (button) {
            switch (button) {
                case 0:
                    // add item
                    var field = getField()
                    if (draggedMesh) {
                        if (draggedMesh.itemName != 'eraser') {
                            WebSocketEditor.add(draggedMesh.itemName, convertX(), convertZ())
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
        },
        mouseMove = function () {
            if (draggedMesh) {
                var newX = convertX(),
                    newZ = convertZ()

                if (newX != oldX) {
                    oldX = newX
                    draggedMesh.position.x = newX + draggedMesh.plus
                }
                if (newZ != oldZ) {
                    oldZ = newZ
                    draggedMesh.position.z = newZ + draggedMesh.plus
                }
            }
        }
}
