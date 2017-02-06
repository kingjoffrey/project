var HelpRenderer = new function () {
    var renderer,
        scene,
        camera,
        timeOut = 1000,
        stop,
        render = function () {
            renderer.render(scene, camera)
        }


    this.animate = function () {
        if (stop) {
            return
        }

        render()

        setTimeout(function () {
            requestAnimationFrame(HelpRenderer.animate)
        }, timeOut)
    }
    this.stop = function () {
        stop = 1
    }
    this.isRunning = function () {
        return !stop
    }
    this.resize = function (w, h) {
        renderer.setSize(w, h)
    }
    this.init = function (s, w, h) {
        stop = 0
        renderer = Renderer.get()
        if (Page.getShadows()) {
            renderer.shadowMap.enabled = true
            renderer.shadowMapSoft = false
        }
        scene = s.get()
        camera = s.getCamera()
        this.resize(w, h)
        $('#graphics').append(renderer.domElement)
        HelpRenderer.animate()
    }
}