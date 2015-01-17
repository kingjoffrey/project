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
    this.loadMountains = function () {
        loader.load('/models/mountain.json', Three.getGeomHandler())
    }

    this.getGeomHandler = function () {
        return function (geometry) {
            var material = new THREE.MeshLambertMaterial({color: '#808080'})
            var i = 0
            for (var y = -70; y < 70; y++) {
                for (var x = -50; x < 50; x++) {
                    i++
                    var model = new THREE.Mesh(geometry, material);
                    model.scale.set(1, 1, 1);
                    model.position.set(x * 4, 0, y * 4);
                    Three.scene.add(model);
                }
            }
            console.log(i)
        };
    }

    this.init = function () {
        $('body').append(Three.renderer.domElement);
        Three.loadMountains()
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
