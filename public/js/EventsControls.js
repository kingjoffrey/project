EventsControls = function (camera, domElement) {

    var raycaster = new THREE.Raycaster(),
        objects = [],
        intersects = [],
        detached = []

    this.attach = function (object) {
        if (object instanceof THREE.Mesh) {
            objects.push(object);
        }
    }
    this.detach = function (object) {
        objects.splice(objects.indexOf(object), 1);
    }
    this.aaa = function (meshId) {
        for (var i = objects.length - 1; i > 0; i--) {
            if (objects[i].id == meshId) {
                continue
            }
            detached.push(objects[i].id)
            this.detach(objects[i])
        }
    }
    this.bbb = function () {
        for (var i in detached) {
            this.attach(Three.getScene().getObjectById(detached[i]))
        }
        detached = []
    }

    var intersect = function (event) {
        var x = event.offsetX == undefined ? event.layerX : event.offsetX;
        var y = event.offsetY == undefined ? event.layerY : event.offsetY;

        var vector = new THREE.Vector3(( x / domElement.width ) * 2 - 1, -( y / domElement.height ) * 2 + 1, 1);
        vector.unproject(camera);
        raycaster.set(camera.position, vector.sub(camera.position).normalize());
        intersects = raycaster.intersectObjects(objects, true)
    }
    var onContainerMouseDown = function (event) {
        intersect(event)
        onclick(event.button)
    }
    var onContainerMouseMove = function (event) {
        intersect(event)
        mouseMove()
    }
    var onContainerMouseUp = function (event) {
        event.preventDefault();
    }

    domElement.addEventListener('mousedown', onContainerMouseDown, false);
    domElement.addEventListener('mousemove', onContainerMouseMove, false);
    domElement.addEventListener('mouseup', onContainerMouseUp, false);

    var onclick = function (button) {
        switch (button) {
            case 0:
                var focused = intersects[0].object
                switch (focused.name) {
                    case 'castle':
                        if (Me.getSelectedCastleId()) {
                            if (Me.getSelectedCastleId() != focused.identification) {
                                Websocket.production(Me.getSelectedCastleId(), Me.getSelectedUnitId(), focused.identification)
                            }
                            Me.setSelectedCastleId(null)
                            Me.setSelectedUnitId(null)
                        } else {
                            Message.castle(Me.getCastle(focused.identification))
                        }
                        break
                    case 'army':
                        Me.armyClick(focused.identification)
                        break
                    default:
                        if (Me.getSelectedArmyId()) {
                            Websocket.move(parseInt((intersects[0].point.x + 218) / 4), parseInt((intersects[0].point.z + 312) / 4))
                        }

                }
                break

            case 1:
                // middle button
                break

            case 2:
                Me.deselectArmy()
                break
        }
    }
    var mouseMove = function () {
        if (Me.getSelectedArmyId()) {
            AStar.cursorPosition(parseInt((intersects[0].point.x + 218) / 4), parseInt((intersects[0].point.z + 312) / 4))
        }
    }
};
