var GameRenderer = new function () {
    var renderer,
        scene,
        camera,
        timeOut = 2000,
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
console.log('render')
        render()

            if (TWEEN.update()) {
                requestAnimationFrame(GameRenderer.animate)
            } else {
                setTimeout(function () {
                    requestAnimationFrame(GameRenderer.animate)
                }, timeOut)
            }
    }
    this.stop = function () {
        stop = 1
    }
    this.start = function () {
        if (Main.getEnv() != 'development') {
            timeOut = 0
        }
        stop = 0
        $('#game').append(renderer.domElement)
        // this.animate()
    }
    this.shadowsOff = function () {
        // renderer.shadowMapAutoUpdate = false
        // console.log(renderer.shadowMapAutoUpdate)
        // renderer.clearTarget(light.shadowMap)
    }
    this.shadowsOn = function () {
        // renderer.shadowMapAutoUpdate = true
        // console.log(renderer.shadowMapAutoUpdate)
    }
    this.shadowsInfo = function () {
        console.log(renderer.shadowMapAutoUpdate)
    }
    this.init = function () {
        renderer = Renderer.get()
        if (Page.getShadows()) {
            renderer.shadowMap.enabled = true
            renderer.shadowMapSoft = false
        }
        scene = GameScene.get()
        camera = GameScene.getCamera()
    }
}