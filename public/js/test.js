var scene = new THREE.Scene();

var aspect = window.innerWidth / window.innerHeight;
var d = 20;
//camera = new THREE.OrthographicCamera(-d * aspect, d * aspect, d, -d, 1, 1000);
camera = new THREE.PerspectiveCamera(45, aspect, 1, 1000)
camera.position.set(20, 32, 20);
camera.rotation.order = 'YXZ';
camera.rotation.y = -Math.PI / 4;
camera.rotation.x = Math.atan(-1 / Math.sqrt(2));
camera.scale.addScalar(1);

var renderer = new THREE.WebGLRenderer();
renderer.setSize(window.innerWidth, window.innerHeight);

var grassTex = THREE.ImageUtils.loadTexture('../img/maps/1.png');
grassTex.wrapS = THREE.RepeatWrapping;
grassTex.wrapT = THREE.RepeatWrapping;
var groundMat = new THREE.MeshBasicMaterial({map: grassTex});
var groundGeo = new THREE.PlaneBufferGeometry(400, 400);
var ground = new THREE.Mesh(groundGeo, groundMat);
ground.position.y = -1.9; //lower it
ground.rotation.x = -Math.PI / 2; //-90 degrees around the xaxis
//IMPORTANT, draw on both sides
ground.doubleSided = true;
scene.add(ground);

var ambientLight = new THREE.AmbientLight(0x111111);
scene.add(ambientLight);

var light = new THREE.PointLight(0xFFFFDD);
light.position.set(-15, 10, 15);
scene.add(light);

var loader = new THREE.JSONLoader();
loader.load('../models/castle.json', modelToScene);
loader.load('../models/tower.json', modelToScene);

function modelToScene(geometry, materials) {
    var material = new THREE.MeshFaceMaterial(materials);
    var obj = new THREE.Mesh(geometry, material);
    obj.scale.set(2, 2, 2);
    obj.position.set(25, 0, -15);
    scene.add(obj);
}

var render = function () {
    requestAnimationFrame(render);
    renderer.render(scene, camera);
};

$(document).ready(function () {
    document.body.appendChild(renderer.domElement);

    render();
})

var Gui = {
    doKey: function (event) {
        if ($(event.target).attr('id') == 'msg') {
            return;
        }
        var key = event.keyCode || event.charCode;
        switch (key) {
            case 37://left
                camera.position.x += -0.5
                camera.position.z += -0.5
                break;

            case 38://up
                camera.position.x += 0.5
                camera.position.z += -0.5
                break;

            case 39://right
                camera.position.x += 0.5
                camera.position.z += 0.5
                break;

            case 40://down
                camera.position.x += -0.5
                camera.position.z += 0.5
                break;

            default :
                console.log(key)
        }
    }
}