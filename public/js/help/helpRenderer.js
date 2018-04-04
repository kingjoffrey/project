var HelpRenderer = new function () {
    var renderer,
        scene,
        camera,
        timeOut = 1000,
        stop = 1,
        mesh = 0,
        angle = Math.PI / 180,
        render = function () {
            if (mesh) {
                // Animation.rotate(mesh)
                mesh.rotateY(angle)
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
        if (!Main.getEnv()) {
            timeOut = 0
        }
        stop = 0
        $('#graphics').append(renderer.domElement)
        this.animate()
    }
    this.isRunning = function () {
        return !stop
    }
    this.setMesh = function (m) {
        mesh = m
    }
    this.init = function () {
        renderer = Renderer.get()
        if (Page.getShadows()) {
            renderer.shadowMap.enabled = true
            renderer.shadowMapSoft = false
        }
        scene = HelpScene.get()
        camera = HelpScene.getCamera()
    }
}