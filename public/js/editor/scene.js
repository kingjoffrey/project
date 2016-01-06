if (!Detector.webgl) Detector.addGetWebGLMessage();

var Scene = new function () {
    var minHeight = 665,
        minWidth = 950,
        gameWidth,
        gameHeight,
        scene = new THREE.Scene(),
        camera,
        renderer = new THREE.WebGLRenderer({antialias: true}),
        pointLight = new THREE.PointLight(0xdddddd),
        theLight = new THREE.DirectionalLight(0xffffff, 1),
        ambientLight = new THREE.AmbientLight(0xffffff),
        loader = new THREE.JSONLoader(),
        circles = [],
        armyCircles = [],
        showShadows = 0,
        cameraY = 76,
        timeOut = 100,
        createTextMesh = function (text, color) {
            var mesh = new THREE.Mesh(new THREE.TextGeometry(text, {
                size: 0.5,
                height: 0.1
            }), new THREE.MeshPhongMaterial({color: color}))
            mesh.position.set(0, 7, 0.2)
            mesh.rotation.y = -Math.PI / 4
            return mesh
        },
        animate = function () {
            if (TWEEN.update()) {
                requestAnimationFrame(animate)
                renderer.render(scene, camera)
            } else {
                renderer.render(scene, camera)
                setTimeout(function () {
                    requestAnimationFrame(animate)
                }, timeOut)
            }
        }

    if (showShadows) {
        renderer.shadowMapEnabled = true
        renderer.shadowMapSoft = false
    }

    this.getCameraY = function () {
        return cameraY
    }
    this.get = function () {
        return scene
    }
    this.getCamera = function () {
        return camera
    }
    this.getRenderer = function () {
        return renderer
    }
    var initCamera = function (gameWidth, gameHeight) {
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
    }

    this.init = function () {
        gameWidth = $('body').innerWidth()
        gameHeight = $('body').innerHeight()
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
        Picker.init(camera, renderer.domElement)
    }
    this.resize = function () {
        gameWidth = $(window).innerWidth()
        gameHeight = $(window).innerHeight()
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
}
