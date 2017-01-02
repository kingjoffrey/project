var GameRenderer = new function () {
    var renderer,
        scene,
        camera,
        width,
        height,
        viewports = {},
        timeOut = 100,
        stop,
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
    this.turnOnShadows = function () {
        renderer.shadowMap.enabled = true
        renderer.shadowMapSoft = false
    }
    this.animate = function () {
        if (stop) {
            return
        }
        // console.log(1)
        if (TWEEN.update()) {
            requestAnimationFrame(GameRenderer.animate)
        } else {
            setTimeout(function () {
                requestAnimationFrame(GameRenderer.animate)
            }, timeOut)
        }

        render()
        // stats.update();
    }
    this.stop = function () {
        stop = 1
    }

    this.init = function (id, Scene) {
        stop = 0
        renderer = Renderer.get()
        scene = Scene.get()
        camera = Scene.getCamera()
        width = Scene.getWidth()
        height = Scene.getHeight()
        $('#' + id).append(renderer.domElement)
        // renderer.autoClear = false
    }
}