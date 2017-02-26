var GameScene = new function () {
    var scene,
        camera,
        sun,
        cameraY = 24,
        radiansX = 2 * Math.PI + Math.atan(-1 / Math.sqrt(2)),
        radiansY = 2 * Math.PI - Math.PI / 4,
        degreesX = radiansX * (180 / Math.PI),
        degreesY = radiansY * (180 / Math.PI),
        initCamera = function (w, h) {
            var viewAngle = 22,
                near = 1,
                far = 1000

            camera = new THREE.PerspectiveCamera(viewAngle, w / h, near, far)
            camera.rotation.order = 'YXZ'
            camera.rotation.y = radiansY
            camera.rotation.x = radiansX

            console.log(degreesX)
            console.log(degreesY)

            camera.position.y = cameraY
            camera.scale.addScalar(1)
            scene.add(camera)
            scene.add(new THREE.AmbientLight(0x777777))
        }
    // initCamera = function (w, h) {
    //     var viewAngle = 75,
    //         near = 0.1,
    //         far = 1000
    //
    //     camera = new THREE.PerspectiveCamera(viewAngle, w / h, near, far)
    //
    //     scene.add(camera)
    //     scene.add(new THREE.AmbientLight(0x777777))
    // }

    this.initSun = function (size) {
        sun = new THREE.DirectionalLight(0xdfebff, 0.75)
        sun.position.set(100, 200, 150)
        scene.add(sun)
    }
    this.getSun = function () {
        return sun
    }
    this.setCameraPosition = function (x, z) {
        camera.position.x = parseFloat(x)
        camera.position.z = parseFloat(z)
    }
    this.getCameraY = function () {
        return cameraY
    }
    this.centerOn = function (x, y, func) {
        var yOffset = camera.position.y - cameraY,
            startPosition = {
                x: camera.position.x,
                z: camera.position.z
            },
            endPosition = {
                x: x * 2 - cameraY - yOffset,
                z: y * 2 + cameraY + yOffset
            },
            tween = new TWEEN.Tween(startPosition)
                .to(endPosition, Math.sqrt(Math.pow(endPosition.x - startPosition.x, 2) + Math.pow(startPosition.z - endPosition.z, 2)) * 5)
                .onUpdate(function () {
                    GameScene.setCameraPosition(endPosition.x, endPosition.z)
                })
                .start()

        if (isSet(func)) {
            tween.onComplete(function () {
                func()
            })
        }
    }
    this.moveCamera = function (x, y) {
        x = -x
        y = -y * 2

        var angleX = degreesX,
            angleY = degreesY


        var vector = new THREE.Vector3(x, 0, y),
            mRx = new THREE.Matrix3(),
            mRy = new THREE.Matrix3(),
            cosX = Math.cos(angleX),
            sinX = Math.sin(angleX),
            cosY = Math.cos(angleY),
            sinY = Math.sin(angleY)

        mRx.set(
            1, 0, 0,
            0, cosX, -sinX,
            0, sinX, cosX
        )

        mRy.set(
            cosY, 0, sinY,
            0, 1, 0,
            -sinY, 0, cosY
        )

        vector.applyMatrix3(mRx)
        vector.applyMatrix3(mRy)

        camera.position.x -= vector.x * camera.position.y / 1000
        camera.position.z += vector.z * camera.position.y / 1000
    }
    this.moveCameraLeft = function () {
        camera.position.x += -2
        camera.position.z += -2
    }
    this.moveCameraRight = function () {
        camera.position.x += 2
        camera.position.z += 2
    }
    this.moveCameraUp = function () {
        camera.position.x += 2
        camera.position.z += -2
    }
    this.moveCameraDown = function () {
        camera.position.x += -2
        camera.position.z += 2
    }
    this.moveCameraAway = function () {
        camera.position.y += 2
        camera.position.x -= 2
        camera.position.z += 2
    }
    this.moveCameraClose = function () {
        camera.position.y -= 2
        camera.position.x += 2
        camera.position.z -= 2
    }
    this.get = function () {
        return scene
    }
    this.add = function (object) {
        scene.add(object)
    }
    this.remove = function (object) {
        scene.remove(object)
    }
    this.getCamera = function () {
        return camera
    }
    this.resize = function (w, h) {
        camera.aspect = w / h
        camera.updateProjectionMatrix()
    }
    this.init = function (w, h) {
        scene = new THREE.Scene()
        initCamera(w, h)
    }
}

var Renderer = new function () {
    var renderer = new THREE.WebGLRenderer({antialias: true})
    this.get = function () {
        return renderer
    }
}

var GameRenderer = new function () {
    var renderer,
        scene,
        camera,
        render = function () {
            renderer.render(scene, camera)
        }
    this.setSize = function (w, h) {
        renderer.setSize(w, h)
    }
    this.getDomElement = function () {
        return renderer.domElement
    }
    this.animate = function () {
        requestAnimationFrame(GameRenderer.animate)
        render()
    }
    this.stop = function () {
        stop = 1
    }
    this.start = function () {
        stop = 0
        this.animate()
    }
    this.init = function (id, Scene) {
        renderer = Renderer.get()
        scene = Scene.get()
        camera = Scene.getCamera()
        $('#' + id).append(renderer.domElement)
    }
}

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
}

var PickerGame = new function () {
    var dragStart = 0,
        handleDownStart = function (event) {
            PickerCommon.intersect(event)
            if (PickerCommon.intersects()) {
                dragStart = PickerCommon.getPoint(event)
            }
        },
        handleMove = function (event) {
            PickerCommon.intersect(event)
            if (PickerCommon.intersects()) {
                if (dragStart) {
                    var dragEnd = PickerCommon.getPoint(event)
                    GameScene.moveCamera(dragStart.x - dragEnd.x, dragStart.y - dragEnd.y)
                    dragStart = dragEnd
                }
            }
        }

    this.onContainerMouseDown = function (event) {
        switch (event.button) {
            case 0:
                handleDownStart(event)
                break

            case 1:
                // middle button
                break

            case 2:
                break
        }
    }
    this.onContainerMouseMove = function (event) {
        handleMove(event)
    }
    this.onContainerMouseUp = function (event) {
        event.preventDefault()
        dragStart = 0
    }
    this.onContainerMouseOut = function (event) {
        event.preventDefault()
        dragStart = 0
    }
    /**
     *
     * @returns {{x: Number, y: Number}}
     */
    this.getPoint = function (event) {
        var x = event.offsetX == undefined ? event.layerX : event.offsetX,
            y = event.offsetY == undefined ? event.layerY : event.offsetY
        return {x: x, y: y}
    }
}

function isSet(val) {
    if (typeof val === 'undefined') {
        return false;
    } else {
        return true;
    }
}

$(document).ready(function () {
    $('#bg').hide()

    var size = 32

    GameScene.init($(window).innerWidth(), $(window).innerHeight())
    GameScene.resize($(window).innerWidth(), $(window).innerHeight())
    GameRenderer.init('main', GameScene)
    GameRenderer.setSize($(window).innerWidth(), $(window).innerHeight())
    GameScene.initSun(size)
    GameRenderer.animate()
    PickerCommon.init(PickerGame)

    var mesh = new THREE.Mesh(new THREE.PlaneBufferGeometry(size, size), new THREE.MeshLambertMaterial({
        color: '#65402C',
        side: THREE.DoubleSide
    }))

    mesh.rotation.x = Math.PI / 2
    mesh.position.set(size / 2, 0, size / 2)

    GameScene.add(mesh)
    PickerCommon.attach(mesh)

    GameScene.getCamera().position.x = -50
    GameScene.getCamera().position.y = 50
    GameScene.getCamera().position.z = 50

    $('body').css('margin', 0)
})