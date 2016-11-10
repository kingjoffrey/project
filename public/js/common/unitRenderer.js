var UnitRenderer = new function () {
    var renderer = new THREE.WebGLRenderer({antialias: true}),
        scene,
        camera,
        width,
        height,
        viewports = {},
        timeOut = 100,
        i = 0,
        render = function () {
            // renderer.clear()

            // renderer.setViewport(0, 0, width, height)
            renderer.render(scene, camera)

            // for (var i in viewports) {
            //     var viewport = viewports[i]
            //     renderer.clearDepth()
            // renderer.setViewport(viewport.x, viewport.y, viewport.width, viewport.height);
            // renderer.render(viewport.scene, viewport.camera)
            // }
        }

    this.addViewport = function (scene, camera, x, y, width, height) {
        viewports[i] = {
            'scene': scene,
            'camera': camera,
            'x': x,
            'y': y,
            'width': width,
            'height': height
        }
        i++
    }
    this.removeViewport = function () {
        viewports = {}
    }
    this.setSize = function (w, h) {
        width = w
        height = h
        renderer.setSize(w, h)
    }
    this.getDomElement = function () {
        return renderer.domElement
    }
    this.animate = function () {
        if (TWEEN.update()) {
            requestAnimationFrame(UnitRenderer.animate)
        } else {
            setTimeout(function () {
                requestAnimationFrame(UnitRenderer.animate)
            }, timeOut)
        }

        render()
        // stats.update();
    }

    this.init = function (id, s) {
        scene = s.get()
        camera = s.getCamera()
        width = s.getWidth()
        height = s.getHeight()
        $('#' + id).append(renderer.domElement)
        renderer.setSize(width, height)
        // renderer.autoClear = false
    }
}