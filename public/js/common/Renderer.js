var Renderer = new function () {
    var renderer = new THREE.WebGLRenderer()

    this.getRenderer = function () {
        return renderer
    }
    this.init = function (w, h, id) {
        $('#' + id).append(renderer.domElement)
        renderer.setSize(w, h)
    }
}
