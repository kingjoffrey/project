var UnitScene = function () {
    var canvasWidth,
        canvasHeight,
        scene = new THREE.Scene(),
        camera,
        sun,
        shadows = 0,
        meshes = [],
        initCamera = function () {
            var near = 1,
                far = 1000,
                frustumSize = 100,
                aspect = canvasWidth / canvasHeight

            // camera = new THREE.OrthographicCamera(canvasWidth / -2, canvasWidth / 2, canvasHeight / 2, canvasHeight / -2, near, far)

            camera = new THREE.OrthographicCamera(frustumSize * aspect / -2, frustumSize * aspect / 2, frustumSize / 2, frustumSize / -2, near, far);

            // var cameraOrthoHelper = new THREE.CameraHelper(camera)
            // scene.add(cameraOrthoHelper);

            // camera.position = {x: 10, y: 200, z: -10}
            // camera.scale.addScalar(1)

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
    this.init = function (w, h) {
        if (notSet(w) || notSet(h)) {
            throw 'Not set'
        }

        canvasWidth = w
        canvasHeight = h
        initCamera()
        initSun()
    }
}
