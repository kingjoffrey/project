var SimpleScene = function () {
    var canvasWidth,
        canvasHeight,
        scene = new THREE.Scene(),
        camera,
        sun,
        renderer = new THREE.WebGLRenderer(),
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
            camera.position.z = 20
            camera.scale.addScalar(1)
            scene.add(camera)
            scene.add(new THREE.AmbientLight(0x777777))
            //camera.add(new THREE.PointLight(0xffffff, 0.7))
        }

    this.initSun = function (size) {
        sun = new THREE.DirectionalLight(0xdfebff, 0.75)
        sun.position.set(100, 200, 150)
        scene.add(sun)
    }
    this.getSun = function () {
        return sun
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
    this.resize = function (w, h) {
        canvasWidth = w
        canvasHeight = h
        renderer.setSize(canvasWidth, canvasHeight)
        //camera.aspect = canvasWidth / canvasHeight
        camera.left = canvasWidth / -2
        camera.right = canvasWidth / 2
        camera.top = canvasHeight / 2
        camera.bottom = canvasHeight / -2
        camera.updateProjectionMatrix()
    }
    this.render = function () {
        renderer.render(scene, camera)
    }
    this.addId = function (id) {
        renderer.domElement.id = id
    }
    this.init = function (w, h, id) {
        canvasWidth = w
        canvasHeight = h

        $('#' + id).append(renderer.domElement)

        initCamera()
        renderer.setSize(canvasWidth, canvasHeight)
    }
}
