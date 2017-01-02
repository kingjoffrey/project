var HelpRenderer = new function () {
    var renderer,
        scene,
        camera,
        width,
        height,
        timeOut = 100,
        stop,
        render = function () {
            renderer.render(scene, camera)
        }

    this.turnOnShadows = function () {
        renderer.shadowMap.enabled = true
        renderer.shadowMapSoft = false
    }
    this.animate = function () {
        if (stop) {
            return
        }
        setTimeout(function () {
            requestAnimationFrame(HelpRenderer.animate)
        }, timeOut)

        render()
        // stats.update();
    }
    this.stop = function () {
        stop = 1
    }
    this.init = function (s) {
        stop = 0
        renderer = Renderer.get()
        scene = s.get()
        camera = s.getCamera()
        width = s.getWidth()
        height = s.getHeight()
        renderer.setSize(width, height)
        $('#graphics').append(renderer.domElement)
        HelpRenderer.animate()
    }
}