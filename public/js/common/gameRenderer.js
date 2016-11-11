var GameRenderer = new function () {
    var renderer = new THREE.WebGLRenderer({antialias: true}),
        scene,
        camera,
        width,
        height,
        viewports = {},
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
    this.turnOnShadows = function () {
        renderer.shadowMap.enabled = true
        renderer.shadowMapSoft = false
    }
    this.animate = function () {
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

    this.init = function () {
        scene = GameScene.get()
        camera = GameScene.getCamera()
        width = GameScene.getWidth()
        height = GameScene.getHeight()
        $('#game').append(renderer.domElement)
        // renderer.autoClear = false
    }
}