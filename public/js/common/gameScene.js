var GameScene = new function () {
    var scene = new THREE.Scene(),
        camera,
        sun,
        mapSize = [1024, 2048, 4096, 8192],
        cameraY = 40,
        radiansX = 2 * Math.PI + Math.atan(-1 / Math.sqrt(2)),
        // radiansX = 2 * Math.PI - (Math.PI / 4 - Math.PI / 32),
        // radiansX = 2 * Math.PI - Math.PI / 2,
        radiansY = 2 * Math.PI - Math.PI / 4,
        degreesX = radiansX * (180 / Math.PI),
        degreesY = radiansY * (180 / Math.PI),
        initCamera = function (w, h) {
            var fov = 20,
                near = 0.1,
                far = 1000

            camera = new THREE.PerspectiveCamera(fov, w / h, near, far)
            camera.rotation.order = 'YXZ'
            camera.rotation.y = radiansY
            camera.rotation.x = radiansX
            camera.position.y = cameraY
            camera.scale.addScalar(1)
            scene.add(camera)

            scene.add(new THREE.AmbientLight(0xefefff, 0.2))
            //camera.add(new THREE.PointLight(0xffffff, 0.7))

            sun = new THREE.DirectionalLight(0xefefff, 1.5)
            sun.position.set(1, 1, 2).normalize()
            scene.add(sun)

            // var helper = new THREE.DirectionalLightHelper(sun);
            // scene.add(helper)

            // var light = new THREE.DirectionalLight(0xffefef, 1.5)
            // light.position.set(-1, -1, -1).normalize()
            // scene.add(light)
        }

    this.initSun = function (size) {
        if (Page.getShadows()) {
            sun.castShadow = true

            sun.shadow.mapSize.width = mapSize[0]
            sun.shadow.mapSize.height = mapSize[0]

            sun.shadow.camera.right = size
            sun.shadow.camera.top = size
            sun.shadow.camera.bottom = -size
            sun.shadow.camera.left = -size

            sun.shadow.camera.near = -200
            sun.shadow.camera.far = 0

            // var helper = new THREE.CameraHelper(sun.shadow.camera)
            // scene.add(helper)
        }
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

        var vector = new THREE.Vector3(x, 0, y),
            mRx = new THREE.Matrix3(),
            mRy = new THREE.Matrix3(),
            cosX = Math.cos(degreesX),
            sinX = Math.sin(degreesX),
            cosY = Math.cos(degreesY),
            sinY = Math.sin(degreesY)

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
    this.clear = function () {
        for (var i = scene.children.length - 1; i >= 0; i--) {
            var object = scene.children[i]
            if (object instanceof THREE.Mesh) {
                object.material.dispose()
                object.geometry.dispose()
                scene.remove(object)
            }
        }
    }
    this.init = function () {
        initCamera(1, 1)
    }
}
