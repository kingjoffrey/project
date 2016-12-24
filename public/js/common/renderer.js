var Renderer = new function () {
    var renderer = new THREE.WebGLRenderer({antialias: true})
    this.get = function () {
        return renderer
    }
}