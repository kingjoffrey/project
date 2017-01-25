if (!Detector.webgl) {
    Detector.addGetWebGLMessage();
    document.getElementById('container').innerHTML = "";
}

var GameScene = new function () {
    var canvasWidth,
        canvasHeight,
        scene,
        camera,
        sun,
        shadows = 1,
        cameraY = 24,
        initCamera = function () {
            var viewAngle = 22,
                near = 1,
                far = 1000

            camera = new THREE.PerspectiveCamera(viewAngle, canvasWidth / canvasHeight, near, far)
            camera.rotation.order = 'YXZ'
            camera.rotation.y = -Math.PI / 4
            camera.rotation.x = Math.atan(-1 / Math.sqrt(2))
            camera.position.y = cameraY
            camera.scale.addScalar(1)
            scene.add(camera)
            scene.add(new THREE.AmbientLight(0x777777))
            //camera.add(new THREE.PointLight(0xffffff, 0.7))
        }

    this.initSun = function (size) {
        sun = new THREE.DirectionalLight(0xdfebff, 0.75)
        sun.position.set(100, 200, 150)
        if (shadows) {
            GameRenderer.turnOnShadows()

            sun.castShadow = true

            sun.shadow.mapSize.width = 2048
            sun.shadow.mapSize.height = 2048

            var d = 2.1 * size

            sun.shadow.camera.left = -d / 1.93
            sun.shadow.camera.right = d / 1.29
            sun.shadow.camera.top = 0
            sun.shadow.camera.bottom = -d
            sun.shadow.camera.far = 300

            //var helper = new THREE.CameraHelper(sun.shadow.camera)
            //scene.add(helper)
        }
        scene.add(sun)
    }
    this.getSun = function () {
        return sun
    }
    this.getShadows = function () {
        return shadows
    }
    this.setCameraPosition = function (x, z) {
        camera.position.set(parseFloat(x), cameraY, parseFloat(z))
    }
    this.getCameraY = function () {
        return cameraY
    }
    this.centerOn = function (x, y, func) {
        var yOffset = camera.position.y - GameScene.getCameraY(),
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
        var xSign,
            zSign

        if (y >= -x && y >= x) {
            xSign = -1
            zSign = 1
        } else if (y > -x && y < x) {
            xSign = 1
            zSign = 1
        } else if (y <= -x && y <= x) {
            xSign = 1
            zSign = -1
        } else if (y < -x && y > x) {
            xSign = -1
            zSign = -1
        }

        camera.position.x += xSign * (Math.abs(x) + Math.abs(y)) / 50
        camera.position.z += zSign * (Math.abs(x) + Math.abs(y)) / 50
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
    this.getWidth = function () {
        return canvasWidth
    }
    this.getHeight = function () {
        return canvasHeight
    }
    this.resize = function (w, h) {
        canvasWidth = w
        canvasHeight = h
        camera.aspect = canvasWidth / canvasHeight
        camera.updateProjectionMatrix()
        GameRenderer.setSize(w, h)
    }
    this.init = function (w, h) {
        canvasWidth = w
        canvasHeight = h

        scene = new THREE.Scene()

        initCamera()
    }
}
