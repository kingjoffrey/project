if (!Detector.webgl) {
    Detector.addGetWebGLMessage();
    document.getElementById('container').innerHTML = "";
}

var Scene = new function () {
    var gameWidth,
        gameHeight,
        scene = new THREE.Scene(),
        camera,
        sun,
        renderer = new THREE.WebGLRenderer({antialias: true}),
        shadows = 1,
        cameraY = 24,
        timeOut = 100,
        water,
        initCamera = function () {
            var viewAngle = 22,
                near = 1,
                far = 1000

            camera = new THREE.PerspectiveCamera(viewAngle, gameWidth / gameHeight, near, far)
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
        camera.position.set(x, cameraY, parseFloat(z))
    }
    this.getCameraY = function () {
        return cameraY
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
    this.getRenderer = function () {
        return renderer
    }
    this.getWidth = function () {
        return gameWidth
    }
    this.getHeight = function () {
        return gameHeight
    }
    this.setFPS = function (fps) {
        timeOut = parseInt(1000 / fps)
    }
    this.resize = function () {
        gameWidth = $(window).innerWidth()
        gameHeight = $(window).innerHeight()
        $('#game')
            .css({
                    width: gameWidth + 'px',
                    height: gameHeight + 'px'
                }
            )
        renderer.setSize(gameWidth, gameHeight)
        camera.aspect = gameWidth / gameHeight
        camera.updateProjectionMatrix()
    }
    this.init = function () {
        gameWidth = $(window).innerWidth()
        gameHeight = $(window).innerHeight()

        $('#game')
            .append(renderer.domElement)
            .css({
                    width: gameWidth + 'px',
                    height: gameHeight + 'px'
                }
            )

        initCamera()
        renderer.setSize(gameWidth, gameHeight)
        Models.init()
        PickerCommon.init()
    }
    this.setWater = function (w) {
        water = w
    }
    this.render = function () {
        if (TWEEN.update()) {
            requestAnimationFrame(Scene.render)
            renderer.render(scene, camera)
        } else {
            renderer.render(scene, camera)
            setTimeout(function () {
                //water.material.uniforms.time.value += 1.0 / 60.0
                //water.render()
                requestAnimationFrame(Scene.render)
            }, timeOut)
        }
    }
}
