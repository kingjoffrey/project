if (!Detector.webgl) {
    Detector.addGetWebGLMessage();
    document.getElementById('container').innerHTML = "";
}

var Renderer = new function () {
    var renderer = new THREE.WebGLRenderer({antialias: true})

    this.get = function () {
        return renderer
    }
    this.clear = function () {
        while (renderer.domElement.lastChild) {
            renderer.domElement.removeChild(renderer.domElement.lastChild)
        }
    }
}