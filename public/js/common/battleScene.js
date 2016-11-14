var BattleScene = new function () {
    var canvasWidth,
        canvasHeight,
        scene = new THREE.Scene(),
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
    this.getShadows = function () {
        return shadows
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

        initCamera()
    }
}
