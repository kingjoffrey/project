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

        render()

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
    }
    this.stop = function () {
        stop = 1
    }
    this.start = function () {
        if (!Main.getEnv()) {
            timeOut = 0
        }
        stop = 0
        $('#game').append(renderer.domElement)
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