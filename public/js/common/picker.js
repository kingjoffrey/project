var PickerCommon = new function () {
    var raycaster = new THREE.Raycaster(),
        objects = [],
        intersects = [],
        camera,
        container,
        vector

    this.init = function () {
        camera = Scene.getCamera()
        container = Scene.getRenderer().domElement

        container.addEventListener('mousedown', Picker.onContainerMouseDown, false);
        container.addEventListener('mousemove', Picker.onContainerMouseMove, false);
        container.addEventListener('mouseup', Picker.onContainerMouseUp, false);
        container.addEventListener('mouseout', Picker.onContainerMouseOut, false);


        container.addEventListener('touchstart', Picker.onContainerTouchStart, true)
        container.addEventListener('touchmove', Picker.onContainerTouchMove, true)
        container.addEventListener('touchend', Picker.onContainerTouchEnd, true)
        container.addEventListener('touchcancel', Picker.onContainerTouchEnd, true);
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
    this.getField = function () {
        return Fields.get(PickerCommon.convertX(), PickerCommon.convertZ())
    }
    this.intersects = function () {
        return isSet(intersects[0])
    }
}
