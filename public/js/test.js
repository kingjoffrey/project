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
//grassTex.wrapS = THREE.RepeatWrapping;
//grassTex.wrapT = THREE.RepeatWrapping;
var groundMat = new THREE.MeshBasicMaterial({map: grassTex});
var groundGeo = new THREE.PlaneBufferGeometry(436, 624);
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
//loader.load('../models/castle.json', getGeomHandler(0, 100, 20));
loader.load('../models/tower.json', getGeomHandler(0, 0));
//loader.load('../models/tower.json', getGeomHandler(10, 0, 1));
//loader.load('../models/tower.json', getGeomHandler(100, 0, 1));
//loader.load('../models/tower.json', getGeomHandler(150, 0, 1));
//loader.load('../models/tower.json', getGeomHandler(170, 0, 1));
//loader.load('../models/tower.json', getGeomHandler(190, 0, 1));
//loader.load('../models/tower.json', getGeomHandler(200, 0, 1));

function getGeomHandler(x, y) {
    //var scale = 1
    return function (geometry) {
        var obj = new THREE.Mesh(geometry, new THREE.MeshFaceMaterial());
        obj.scale.set(1, 1, 1);
        obj.position.set(x, 0, y);
        scene.add(obj);
    };
}

//var object1 = new PinaCollada('castle', 1);
//scene.add(object1);

//function PinaCollada(modelname, scale) {
//    var loader = new THREE.ColladaLoader();
//    var localObject;
//    loader.options.convertUpAxis = true;
//    loader.load('../models/' + modelname + '.dae', function colladaReady(collada) {
//        localObject = collada.scene;
//        localObject.scale.x = localObject.scale.y = localObject.scale.z = scale;
//        localObject.updateMatrix();
//    });
//    return localObject;
//}

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