if (!Detector.webgl) Detector.addGetWebGLMessage();

var Scene = new function () {
    var minHeight = 665,
        minWidth = 950,
        gameWidth,
        gameHeight,
        scene = new THREE.Scene(),
        camera,
        renderer = new THREE.WebGLRenderer({antialias: true}),
        cameraY = 76,
        initCamera = function (gameWidth, gameHeight) {
            var viewAngle = 22,
                near = 1,
                far = 1000

            camera = new THREE.PerspectiveCamera(viewAngle, gameWidth / gameHeight, 1, 1000)
            camera.rotation.order = 'YXZ'
            camera.rotation.y = -Math.PI / 4
            camera.rotation.x = Math.atan(-1 / Math.sqrt(2))
            camera.position.set(0, cameraY, 0)
            camera.scale.addScalar(1)
            scene.add(camera)
            scene.add(new THREE.AmbientLight(0x222222))
            camera.add(new THREE.PointLight(0xffffff, 0.7))

            Scene.render()
            Gui.init()
            Models.init()
        }

    this.getCameraY = function () {
        return cameraY
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
    this.getRenderer = function () {
        return renderer
    }


    this.init = function () {
        gameWidth = window.innerWidth
        gameHeight = window.innerHeight
        if (gameWidth < minWidth) {
            gameWidth = minWidth
        }
        if (gameHeight < minHeight) {
            gameHeight = minHeight
        }

        $('#game')
            .append(renderer.domElement)
            .css({
                    width: gameWidth + 'px',
                    height: gameHeight + 'px'
                }
            )

        initCamera(gameWidth, gameHeight)
        renderer.setSize(gameWidth, gameHeight)
        Picker.init()
    }
    this.resize = function () {
        gameWidth = window.innerWidth
        gameHeight = window.innerHeight
        if (gameWidth < minWidth) {
            gameWidth = minWidth
        }
        if (gameHeight < minHeight) {
            gameHeight = minHeight
        }
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
    this.getWidth = function () {
        return gameWidth
    }
    this.getHeight = function () {
        return gameHeight
    }
    this.setFPS = function (fps) {
        timeOut = parseInt(1000 / fps)
    }
    this.render = function () {
        requestAnimationFrame(Scene.render);
        renderer.render(scene, camera);
    }
}
