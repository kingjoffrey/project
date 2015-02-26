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
    var getField = function () {
        return Fields.get(convertX(), convertY())
    }
    var convertX = function () {
        return parseInt((intersects[0].point.x + 218) / 4)
    }
    var convertY = function () {
        return parseInt((intersects[0].point.z + 312) / 4)
    }
    var onclick = function (button) {
        switch (button) {
            case 0:
                if (Me.getSelectedArmyId()) {
                    Websocket.move()
                } else {
                    var field = getField()
                    console.log(field.getArmies())
                    if (field.hasArmies()) {
                        var armies = field.getArmies()
                        for (var armyId in armies) {
                            if (Me.colorEquals(armies[armyId])) {
                                Me.armyClick(armyId)
                            }
                        }
                    } else if (Me.colorEquals(field.getCastleColor())) {
                        var castleId = field.getCastleId()
                        if (Me.getSelectedCastleId()) {
                            if (Me.getSelectedCastleId() != castleId) {
                                Websocket.production(Me.getSelectedCastleId(), Me.getSelectedUnitId(), castleId)
                            }
                            Me.setSelectedCastleId(null)
                            Me.setSelectedUnitId(null)
                        } else {
                            Message.castle(Me.getCastle(castleId))
                        }
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
        if (AStar.cursorPosition(convertX(), convertY()) && Me.getSelectedArmyId()) {
            AStar.showPath()
        }
    }
};
