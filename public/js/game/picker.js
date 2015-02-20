var Picker = new function () {

    var raycaster = new THREE.Raycaster(),
        objects = [],
        intersects = [],
        detached = [],
        camera,
        container

    this.init = function (c, domElement) {
        camera = c
        container = domElement
        container.addEventListener('mousedown', onContainerMouseDown, false);
        container.addEventListener('mousemove', onContainerMouseMove, false);
        container.addEventListener('mouseup', onContainerMouseUp, false);
    }
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

        var vector = new THREE.Vector3(( x / container.width ) * 2 - 1, -( y / container.height ) * 2 + 1, 1);
        vector.unproject(camera);
        raycaster.set(camera.position, vector.sub(camera.position).normalize());
        intersects = raycaster.intersectObjects(objects, true)
    }
    var onContainerMouseDown = function (event) {
        intersect(event)
        if (isSet(intersects[0])) {
            onclick(event.button)
        }
    }
    var onContainerMouseMove = function (event) {
        intersect(event)
        if (isSet(intersects[0])) {
            mouseMove()
        }
    }
    var onContainerMouseUp = function (event) {
        event.preventDefault();
    }

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
                            Websocket.move()
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
        AStar.cursorPosition(intersects[0].point)
        if (Me.getSelectedArmyId()) {
            AStar.showPath(Me.getSelectedArmy())
        }
    }
};
