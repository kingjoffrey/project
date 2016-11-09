var HelpRenderer = new function () {
    var renderer = new THREE.WebGLRenderer({antialias: true}),
        scene,
        camera,
        width,
        height,
        timeOut = 100,
        render = function () {
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
    this.animate = function () {
        setTimeout(function () {
            requestAnimationFrame(HelpRenderer.animate)
        }, timeOut)

        render()
        // stats.update();
    }

    this.init = function (s) {
        scene = s.get()
        camera = s.getCamera()
        width = s.getWidth()
        height = s.getHeight()
        renderer.setSize(width, height)
        $('#graphics').append(renderer.domElement)
        HelpRenderer.animate()
    }
}