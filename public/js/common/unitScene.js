var UnitScene = function () {
    var canvasWidth = 400,
        canvasHeight = 40,
        scene = new THREE.Scene(),
        camera,
        sun,
        shadows = 0,
        meshes = [],
        initCamera = function () {
            var near = 1,
                far = 1000

            camera = new THREE.OrthographicCamera(canvasWidth / -2, canvasWidth / 2, canvasHeight / 2, canvasHeight / -2, near, far)
            camera.position = {x: 0, y: 20, z: 0}
            camera.scale.addScalar(1)
            scene.add(camera)
            scene.add(new THREE.AmbientLight(0x777777))
        },
        initSun = function () {
            sun = new THREE.DirectionalLight(0xdfebff, 0.75)
            sun.position.set(100, 200, 150)
            scene.add(sun)
        }

    this.get = function () {
        return scene
    }
    this.add = function (object) {
        meshes.push(object)
        scene.add(object)
    }
    this.removeMeshes = function () {
        for (var i in meshes) {
            scene.remove(meshes[i])
        }
        meshes = []
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
    this.getShadows = function () {
        return shadows
    }
    this.init = function () {
        initCamera()
        initSun()
    }
}
