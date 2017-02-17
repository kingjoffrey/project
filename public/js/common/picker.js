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

        container.addEventListener('mousedown', picker.onContainerMouseDown, false)
        container.addEventListener('mousemove', picker.onContainerMouseMove, false)
        container.addEventListener('mouseup', picker.onContainerMouseUp, false)
        container.addEventListener('mouseout', picker.onContainerMouseOut, false)


        container.addEventListener('touchstart', picker.onContainerTouchStart, true)
        container.addEventListener('touchmove', picker.onContainerTouchMove, true)
        container.addEventListener('touchend', picker.onContainerTouchEnd, true)
        container.addEventListener('touchcancel', picker.onContainerTouchEnd, true)
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
