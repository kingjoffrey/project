var Three = new function () {
    this.scene = new THREE.Scene()

    var aspect = window.innerWidth / window.innerHeight
    this.camera = new THREE.PerspectiveCamera(45, aspect, 1, 1000)
    this.camera.position.set(20, 52, 20);
    this.camera.rotation.order = 'YXZ';
    this.camera.rotation.y = -Math.PI / 4;
    this.camera.rotation.x = Math.atan(-1 / Math.sqrt(2));
    this.camera.scale.addScalar(1);

    this.renderer = new THREE.WebGLRenderer()
    this.renderer.setSize(window.innerWidth, window.innerHeight);

    var ground = new THREE.Mesh(new THREE.PlaneBufferGeometry(436, 624), new THREE.MeshBasicMaterial({map: THREE.ImageUtils.loadTexture('/img/maps/1.png')}));
    ground.rotation.x = -Math.PI / 2; //-90 degrees around the x axis
//IMPORTANT, draw on both sides
//    ground.doubleSided = true;
    this.scene.add(ground);

    var light = new THREE.PointLight(0xFFFFDD);
    light.position.set(-1000, 1000, 1000);
    this.scene.add(light);

    var loader = new THREE.JSONLoader();
    this.loadCastle = function (color, x, y) {
        loader.load('/models/castle.json', getGeomHandler(color, x * 4 - 214, y * 4 - 309, 0.5));
    }
    this.loadTower = function (color, x, y) {
        loader.load('/models/tower.json', getGeomHandler(color, x * 4 - 216, y * 4 - 311, 0.5));
    }
    this.loadRuin = function (x, y) {
        loader.load('/models/ruin.json', getGeomHandler('#808080', x * 4 - 216, y * 4 - 311, 0.5));
    }
    this.loadArmy = function (x, y, img) {
        var hero = new THREE.Mesh(new THREE.PlaneBufferGeometry(2.2, 2.8), new THREE.MeshBasicMaterial({map: THREE.ImageUtils.loadTexture(img)}));
        hero.rotation.y = -Math.PI / 4;
        hero.position.set(x * 4 - 216, 1.4, y * 4 - 311);
        this.scene.add(hero);
    }
    this.init = function () {
        $('#game').append(Three.renderer.domElement);
        Three.render();
    }
    this.render = function () {
        requestAnimationFrame(Three.render);
        Three.renderer.render(Three.scene, Three.camera);
    };

}


function getGeomHandler(color, x, y, scale) {
    console.log(color)
    return function (geometry) {
        var obj = new THREE.Mesh(geometry, new THREE.MeshLambertMaterial({color: color}));
        obj.scale.set(scale, scale, scale);
        obj.position.set(x, 0, y);
        Three.scene.add(obj);
    };
}

