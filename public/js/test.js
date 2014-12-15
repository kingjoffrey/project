var scene = new THREE.Scene();
var camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);

var renderer = new THREE.WebGLRenderer();
renderer.setSize(window.innerWidth, window.innerHeight);

var geometry = new THREE.BoxGeometry(1, 1, 1);
var material = new THREE.MeshBasicMaterial({color: 0x00ff00});
var cube = new THREE.Mesh(geometry, material);
scene.add(cube);

camera.position.z = 5;

var grassTex = THREE.ImageUtils.loadTexture('../img/maps/1.png');
grassTex.wrapS = THREE.RepeatWrapping;
grassTex.wrapT = THREE.RepeatWrapping;
var groundMat = new THREE.MeshBasicMaterial({map:grassTex});
var groundGeo = new THREE.PlaneGeometry(400,400);
var ground = new THREE.Mesh(groundGeo,groundMat);

ground.position.y = -1.9; //lower it
ground.rotation.x = -Math.PI/2; //-90 degrees around the xaxis
//IMPORTANT, draw on both sides
ground.doubleSided = true;
scene.add(ground);

var render = function () {
    requestAnimationFrame(render);

    cube.rotation.x += 0.1;
    cube.rotation.y += 0.1;

    renderer.render(scene, camera);
};

$(document).ready(function () {
    document.body.appendChild(renderer.domElement);

    render();
})