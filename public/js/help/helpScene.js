var HelpScene = new function () {
    var scene = new THREE.Scene(),
        camera,
        sun,
        cameraY = 24,
        initCamera = function () {
            var viewAngle = 22,
                near = 1,
                far = 1000

            camera = new THREE.PerspectiveCamera(viewAngle, 1, near, far)
            camera.rotation.order = 'YXZ'
            camera.rotation.y = -Math.PI / 4
            camera.rotation.x = Math.atan(-1 / Math.sqrt(2))
            camera.position.y = cameraY
            camera.scale.addScalar(1)

            scene.add(camera)
            scene.add(new THREE.AmbientLight(0x777777))

            camera.add(new THREE.PointLight(0xffffff, 0.1))
        },
        initSun = function () {
            sun = new THREE.DirectionalLight(0xdfebff, 0.75)
            sun.position.set(100, 200, 160)
            sun.target.position.set(0, 0, 0)
            if (Page.getShadows()) {
                sun.castShadow = true

                // sun.shadow.mapSize.width = 2048
                // sun.shadow.mapSize.height = 2048
                sun.shadow.mapSize.width = 8192
                sun.shadow.mapSize.height = 8192

                sun.shadow.camera.right = 80
                sun.shadow.camera.left = 0
                sun.shadow.camera.top = 50
                sun.shadow.camera.bottom = -30
                sun.shadow.camera.far = 290

                // var helper = new THREE.CameraHelper(sun.shadow.camera)
                // scene.add(helper)
            }
            scene.add(sun)
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
    this.resize = function (w) {
        camera.updateProjectionMatrix()
    }
    this.init = function () {
        initCamera()
        initSun(40)
    }
}
