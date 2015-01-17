var Three = new function () {
    this.scene = new THREE.Scene()

    this.camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 1000)
    this.camera.position.set(-380, 252, 420);
    this.camera.rotation.order = 'YXZ';
    this.camera.rotation.y = -Math.PI / 4;
    this.camera.rotation.x = Math.atan(-1 / Math.sqrt(2));
    this.camera.scale.addScalar(1);

    this.renderer = new THREE.WebGLRenderer()
    this.renderer.setSize(window.innerWidth, window.innerHeight);

    var ground = new THREE.Mesh(
        new THREE.PlaneBufferGeometry(436, 624),
        new THREE.MeshLambertMaterial({color: '#808080'}));
    ground.rotation.x = -Math.PI / 2; //-90 degrees around the x axis

    this.scene.add(ground);

    var light = new THREE.PointLight(0xFFFFDD);
    light.position.set(-1000, 1000, 1000);
    this.scene.add(light);

    var loader = new THREE.JSONLoader();
    this.loadMountain = function (x, y) {
        loader.load('/models/mountain.json', Three.getGeomHandler('#808080', x, y, 1))
    }
    this.loadFields = function () {
        for (var y = -50; y < 50; y++) {
            for (var x = -50; x < 50; x++) {
                Three.loadMountain(x * 4, y * 4)
            }
        }
    }

    this.getGeomHandler = function (color, x, y, scale) {
        return function (geometry) {
            var model = new THREE.Mesh(geometry, new THREE.MeshLambertMaterial({color: color}));
            model.scale.set(scale, scale, scale);
            model.position.set(x, 0, y);
            Three.scene.add(model);
        };
    }

    this.init = function () {
        $('body').append(Three.renderer.domElement);
        Three.loadFields()
        Three.render();
    }
    this.render = function () {
        requestAnimationFrame(Three.render);
        Three.renderer.render(Three.scene, Three.camera);
    };
}

$(document).ready(function () {
    Three.init();
});
