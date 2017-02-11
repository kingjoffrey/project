var GameRenderer = new function () {
    var renderer,
        scene,
        camera,
        viewports = {},
        timeOut = 100,
        stop,
        render = function () {
            renderer.render(scene, camera)
        }
    this.setSize = function (w, h) {
        renderer.setSize(w, h)
    }
    this.getDomElement = function () {
        return renderer.domElement
    }
    this.animate = function () {
        if (stop) {
            return
        }

        if (timeOut) {
            if (TWEEN.update()) {
                requestAnimationFrame(GameRenderer.animate)
            } else {
                setTimeout(function () {
                    requestAnimationFrame(GameRenderer.animate)
                }, timeOut)
            }
        } else {
            if (TWEEN.update()) {
                requestAnimationFrame(GameRenderer.animate)
            } else {
                requestAnimationFrame(GameRenderer.animate)
            }
        }

        render()
    }
    this.stop = function () {
        stop = 1
    }
    this.start = function () {
        stop = 0
        this.animate()
    }
    this.init = function (id, Scene) {
        if (Main.getEnv() != 'development') {
            timeOut = 0
        }
        stop = 0
        renderer = Renderer.get()
        if (Page.getShadows()) {
            renderer.shadowMap.enabled = true
            renderer.shadowMapSoft = false
        }
        scene = Scene.get()
        camera = Scene.getCamera()
        $('#' + id).append(renderer.domElement)
    }
}