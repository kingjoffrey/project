var UnitScene = function () {
    var canvasWidth = 40,
        canvasHeight = 40,
        scene = new THREE.Scene(),
        camera,
        sun,
        shadows = 0,
        cameraY = 14,
        meshes = [],
        initCamera = function () {
            var viewAngle = 22,
                near = 1,
                far = 1000

            //camera = new THREE.PerspectiveCamera(viewAngle, canvasWidth / canvasHeight, near, far)
            camera = new THREE.OrthographicCamera(canvasWidth / -2, canvasWidth / 2, canvasHeight / 2, canvasHeight / -2, near, far)
            //camera.rotation.order = 'YXZ'
            //camera.rotation.y = -Math.PI / 4
            //camera.rotation.x = Math.atan(-1 / Math.sqrt(2))
            camera.position = {x: -8, y: 20, z: 16}
            camera.scale.addScalar(1)
            scene.add(camera)
            scene.add(new THREE.AmbientLight(0x777777))
            //camera.add(new THREE.PointLight(0xffffff, 0.7))
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
    this.init = function () {
        initCamera()
        initSun()
    }
}
