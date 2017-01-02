if (!Detector.webgl) {
    Detector.addGetWebGLMessage();
    document.getElementById('container').innerHTML = "";
}

var HelpScene = new function () {
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

    this.initSun = function () {
        sun = new THREE.DirectionalLight(0xdfebff, 0.75)
        sun.position.set(100, 200, 160)
        sun.target.position.set(0, 0, 0)
        if (shadows) {
            HelpRenderer.turnOnShadows()

            sun.castShadow = true

            sun.shadow.mapSize.width = 2048
            sun.shadow.mapSize.height = 2048

            sun.shadow.camera.right = 80
            sun.shadow.camera.left = 0
            sun.shadow.camera.top = 50
            sun.shadow.camera.bottom = -30
            sun.shadow.camera.far = 290

            var helper = new THREE.CameraHelper(sun.shadow.camera)
            scene.add(helper)
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
    }
    this.init = function (w, h) {
        canvasWidth = w
        canvasHeight = h

        scene = new THREE.Scene()

        initCamera()
        // Renderer.setScene(scene)
        // Renderer.setCamera(camera)
        // Renderer.init(canvasWidth, canvasHeight, 'game')
    }
}
