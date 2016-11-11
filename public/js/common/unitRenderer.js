var UnitRenderer = function () {
    var renderer = new THREE.WebGLRenderer({antialias: true}),
        scene,
        camera,
        width,
        height
    this.render = function () {
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
    this.init = function (id, s) {
        scene = s.get()
        camera = s.getCamera()
        width = s.getWidth()
        height = s.getHeight()
        $('#' + id).append(renderer.domElement)
        renderer.setSize(width, height)
    }
}