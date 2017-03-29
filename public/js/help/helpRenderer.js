var HelpRenderer = new function () {
    var renderer,
        scene,
        camera,
        timeOut = 100,
        stop,
        render = function () {
            var mesh = Help.getMesh()

            if (mesh) {
                Animation.rotate(mesh)
            }

            renderer.render(scene, camera)
        }

    this.resize = function (w) {
        renderer.setSize(w, w)
    }
    this.animate = function () {
        if (stop) {
            return
        }

        render()

        if (timeOut) {
            setTimeout(function () {
                requestAnimationFrame(HelpRenderer.animate)
            }, timeOut)
        } else {
            requestAnimationFrame(HelpRenderer.animate)
        }
    }
    this.stop = function () {
        stop = 1
    }
    this.start = function () {
        stop = 0
        $('#graphics').append(renderer.domElement)
        this.animate()
    }
    this.isRunning = function () {
        return !stop
    }
    this.init = function () {
        if (Main.getEnv() != 'development') {
            timeOut = 0
        }
        stop = 0
        renderer = Renderer.get()
        if (Page.getShadows()) {
            renderer.shadowMap.enabled = true
            renderer.shadowMapSoft = false
        }
        scene = HelpScene.get()
        camera = HelpScene.getCamera()
    }
}