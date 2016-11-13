var UnitRenderer = function () {
    var renderer = new THREE.WebGLRenderer({antialias: true}),
        scene,
        camera,
        width,
        height

    this.render = function () {
        // var r = Date.now() * 0.0005,
        //     mesh = scene.children[3]
        //
        // if (isSet(mesh)) {
        //     mesh.position.x = 700 * Math.cos(r)
        //     mesh.position.z = 700 * Math.sin(r)
        //     mesh.position.y = 700 * Math.sin(r)
        //     camera.far = mesh.position.length()
        //     camera.updateProjectionMatrix()
        // }

        var timer = Date.now() * 0.0005

        camera.position.x = Math.cos(timer) * 200
        camera.position.z = Math.sin(timer) * 200
        camera.lookAt(scene.position)
        camera.updateProjectionMatrix()

        renderer.render(scene, camera)
    }
    this.setSize = function (w, h) {
        width = w
        height = h
        renderer.setSize(w, h)
    }
    this.getDomElement = function () {
        return renderer.domElement
    }
    this.clear = function () {
        renderer.forceContextLoss()
        renderer.context = null
        renderer.domElement = null
        renderer = null
    }
    this.init = function (id, s) {
        scene = s.get()
        camera = s.getCamera()
        width = s.getWidth()
        height = s.getHeight()
        $('#' + id).append(renderer.domElement)
        renderer.setSize(width, height)
    }
}