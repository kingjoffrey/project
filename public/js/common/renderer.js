var Renderer = new function () {
    var renderer = new THREE.WebGLRenderer({antialias: true}),
        timeOut = 100,
        scene,
        camera,
        width,
        height

    this.setFPS = function (fps) {
        timeOut = parseInt(1000 / fps)
    }
    this.setScene = function (s) {
        scene = s
    }
    this.setCamera = function (c) {
        camera = c
    }
    this.turnOnShadows = function () {
        renderer.shadowMap.enabled = true
        renderer.shadowMapSoft = false
    }
    this.get = function () {
        return renderer
    }
    this.setSize = function (w, h) {
        width = w
        height = h
        renderer.setSize(w, h)
    }
    this.render = function () {
        renderer.render(scene, camera)
        setTimeout(function () {
            requestAnimationFrame(Renderer.render)
        }, timeOut)
    }
    this.init = function (w, h, id) {
        $('#' + id).append(renderer.domElement)
        this.setSize(w, h)
    }
}