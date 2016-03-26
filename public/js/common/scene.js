if (!Detector.webgl) {
    Detector.addGetWebGLMessage();
    document.getElementById('container').innerHTML = "";
}

var Scene = new function () {
    var canvasWidth,
        canvasHeight,
        scene = new THREE.Scene(),
        camera,
        sun,
        renderer = new THREE.WebGLRenderer({antialias: true}),
        shadows = 1,
        cameraY = 24,
        timeOut = 100,
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
            renderer.shadowMap.enabled = true
            renderer.shadowMapSoft = false

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

        MiniMap.centerOnCameraPosition()
    }
    this.moveCameraLeft = function () {
        camera.position.x += -2
        camera.position.z += -2
        MiniMap.centerOnCameraPosition()
    }
    this.moveCameraRight = function () {
        camera.position.x += 2
        camera.position.z += 2
        MiniMap.centerOnCameraPosition()
    }
    this.moveCameraUp = function () {
        camera.position.x += 2
        camera.position.z += -2
        MiniMap.centerOnCameraPosition()
    }
    this.moveCameraDown = function () {
        camera.position.x += -2
        camera.position.z += 2
        MiniMap.centerOnCameraPosition()
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
    this.getRenderer = function () {
        return renderer
    }
    this.getWidth = function () {
        return canvasWidth
    }
    this.getHeight = function () {
        return canvasHeight
    }
    this.setFPS = function (fps) {
        timeOut = parseInt(1000 / fps)
    }
    this.resize = function () {
        canvasWidth = $(window).innerWidth()
        canvasHeight = $(window).innerHeight()
        $('#game')
            .css({
                    width: canvasWidth + 'px',
                    height: canvasHeight + 'px'
                }
            )
        renderer.setSize(canvasWidth, canvasHeight)
        camera.aspect = canvasWidth / canvasHeight
        camera.updateProjectionMatrix()
    }
    this.render = function () {
        if (TWEEN.update()) {
            requestAnimationFrame(Scene.render)
            renderer.render(scene, camera)
        } else {
            renderer.render(scene, camera)
            setTimeout(function () {
                requestAnimationFrame(Scene.render)
            }, timeOut)
        }
    }
    this.renderSimple = function () {
        renderer.render(scene, camera)
        setTimeout(function () {
            requestAnimationFrame(Scene.renderSimple)
        }, timeOut)
    }
    this.init = function () {
        canvasWidth = $(window).innerWidth()
        canvasHeight = $(window).innerHeight()

        $('#game')
            .append(renderer.domElement)
            .css({
                    width: canvasWidth + 'px',
                    height: canvasHeight + 'px'
                }
            )

        initCamera()
        renderer.setSize(canvasWidth, canvasHeight)
        renderer.domElement.id = 'scene'
    }
    this.initSimple = function () {
        canvasWidth = 300
        canvasHeight = 300
        cameraY = 14

        $('#graphics')
            .append(renderer.domElement)
            .css({
                    width: canvasWidth + 'px',
                    height: canvasHeight + 'px'
                }
            )

        initCamera()
        renderer.setSize(canvasWidth, canvasHeight)
        renderer.domElement.id = 'scene'
    }
}
