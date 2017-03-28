if (!Detector.webgl) {
    Detector.addGetWebGLMessage();
    document.getElementById('container').innerHTML = "";
}

var Renderer = new function () {
    var renderer = new THREE.WebGLRenderer({antialias: true})
    this.get = function () {
        return renderer
    }
}