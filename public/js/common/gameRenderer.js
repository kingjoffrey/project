var GameRenderer = new function () {
    var renderer,
        scene,
        camera,
        timeOut = 0,
        stop = 1,
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
        // console.log(renderer)
        // console.log(scene)
        // console.log(camera)
        // stop = 1
        // console.log(stop)
        render()
    }
    this.stop = function () {
        stop = 1
    }
    this.start = function () {
        stop = 0
        this.animate()
    }
    this.shadowsOff = function () {
        renderer.shadowMapAutoUpdate = false
        // renderer.clearTarget(light.shadowMap)
    }
    this.shadowsOn = function () {
        renderer.shadowMapAutoUpdate = true
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
        scene = GameScene.get()
        camera = GameScene.getCamera()
        $('#game').append(renderer.domElement)
    }
}