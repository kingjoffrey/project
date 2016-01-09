var Picker = new function () {
    var raycaster = new THREE.Raycaster(),
        objects = [],
        intersects = [],
        detached = [],
        camera,
        container,
        draggedMesh = 0,
        xOffset,
        zOffset,
        oldX = 0,
        oldZ = 0

    this.init = function (xO, zO) {
        camera = Scene.getCamera()
        container = Scene.getRenderer().domElement
        xOffset = xO
        zOffset = zO

        container.addEventListener('mousedown', onContainerMouseDown, false);
        container.addEventListener('mousemove', onContainerMouseMove, false);
        container.addEventListener('mouseup', onContainerMouseUp, false);
    }
    this.addDraggedMesh = function (mesh) {
        draggedMesh = mesh
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
                console.log(event.offsetX)
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
            return parseInt(intersects[0].point.x)
        },
        convertZ = function () {
            return parseInt(intersects[0].point.z)
        },
        onclick = function (button) {
            switch (button) {
                case 0:

                    break

                case 1:
                    // middle button
                    break

                case 2:

                    break
            }
        },
        mouseMove = function () {
            if (draggedMesh) {
                var x = convertX(),
                    z = convertZ(),
                    newX = parseInt(x / 4),
                    newZ = parseInt(z / 4)
                console.log(x)
                if (newX != oldX || newZ != oldZ) {
                    oldX = newX
                    oldZ = newZ
                    draggedMesh.position.x = x
                    draggedMesh.position.z = z
                }
            }
        }
}
