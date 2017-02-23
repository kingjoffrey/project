var PickerCommon = new function () {
    var raycaster = new THREE.Raycaster(),
        objects = [],
        intersects = [],
        camera,
        container,
        vector

    this.init = function (picker) {
        camera = GameScene.getCamera()
        container = GameRenderer.getDomElement()

        $('canvas').off()

        $('canvas')
            .mousewheel(function (event) {
                if (event.deltaY > 0) {
                    if (GameScene.getCamera().position.y > 12) {
                        GameScene.moveCameraClose()
                    }
                } else {
                    if (GameScene.getCamera().position.y < 230) {
                        GameScene.moveCameraAway()
                    }
                }
            })
            .on('mousedown', picker.onContainerMouseDown)
            .on('mousemove', picker.onContainerMouseMove)
            .on('mouseup', picker.onContainerMouseUp)
            .on('mouseout', picker.onContainerMouseOut)
            .on('touchstart', picker.onContainerTouchStart)
            .on('touchmove', picker.onContainerTouchMove)
            .on('touchend', picker.onContainerTouchEnd)
            .on('touchcancel', picker.onContainerTouchEnd)
            .on('contextmenu', function () {
                return false
            })
            .on('dragstart', function () {
                return false
            })
    }
    this.intersect = function (event) {
        var x = event.offsetX == undefined ? event.layerX : event.offsetX,
            y = event.offsetY == undefined ? event.layerY : event.offsetY

        vector = new THREE.Vector3(( x / container.width ) * 2 - 1, -( y / container.height ) * 2 + 1, 1)
        vector.unproject(camera)
        raycaster.set(camera.position, vector.sub(camera.position).normalize())
        intersects = raycaster.intersectObjects(objects, false)
    }
    this.convertX = function () {
        return Math.floor(parseInt(intersects[0].point.x) / 2)
    }
    this.convertZ = function () {
        return Math.floor(parseInt(intersects[0].point.z) / 2)
    }
    this.attach = function (object) {
        if (object instanceof THREE.Mesh) {
            objects.push(object);
        }
    }
    this.detach = function (object) {
        objects.splice(objects.indexOf(object), 1);
    }
    /**
     *
     * @returns {Field}
     */
    this.getField = function () {
        return Fields.get(PickerCommon.convertX(), PickerCommon.convertZ())
    }
    this.intersects = function () {
        return isSet(intersects[0])
    }
    this.cursor = function (type) {
        if (type) {
            $('body #main #game canvas').css('cursor', 'url(/img/game/cursors/' + type + '.png), auto')
        } else {
            $('body #main #game canvas').css('cursor', 'default')
        }
    }
}
