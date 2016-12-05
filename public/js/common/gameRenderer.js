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
        console.log(1)
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

    this.init = function (id, Scene) {
        scene = Scene.get()
        camera = Scene.getCamera()
        width = Scene.getWidth()
        height = Scene.getHeight()
        $('#' + id).append(renderer.domElement)
        // renderer.autoClear = false
    }
}