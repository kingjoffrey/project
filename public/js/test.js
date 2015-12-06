var Three = new function () {
    var scene = new THREE.Scene()

    var camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 1000)
    camera.position.set(-5, 5, 5);
    camera.rotation.order = 'YXZ';
    camera.rotation.y = -Math.PI / 4;
    camera.rotation.x = Math.atan(-1 / Math.sqrt(2));

    var renderer = new THREE.WebGLRenderer()
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.shadowMap.enabled = true

    var light = new THREE.DirectionalLight(0xffffff, 1)
    light.position.set(150, 100, 100)
    scene.add(light);

    this.init = function () {
        $('body').append(renderer.domElement);
        Three.render();
    }
    this.render = function () {
        requestAnimationFrame(Three.render);
        renderer.render(scene, camera);
    }

    var xy = [],
        maxX = 7,
        maxY = 10

    for (i = 0; i < maxX; i++) {
        for (j = 0; j < maxY; j++) {
            xy.push([i, j])
        }
    }
    console.log(xy)

    var river = [
        [0, 5],
        [0, 4],
        [1, 3],
        [2, 2],
        [3, 2],
        [4, 1],
        [5, 1],
        [6, 0]
    ]

    var grassGeometry = new THREE.BufferGeometry(),
        waterGeometry = new THREE.BufferGeometry()

    var grassVertexPositions = [],
        waterVertexPositions = []

    //console.log(xy.length)
    for (var i = 0; i < xy.length; i++) {
        grassVertexPositions.push([xy[i][0], xy[i][1], 0])
        grassVertexPositions.push([xy[i][0] + 1, xy[i][1], 0])
        grassVertexPositions.push([xy[i][0], xy[i][1] + 1, 0])

        grassVertexPositions.push([xy[i][0] + 1, xy[i][1] + 1, 0])
        grassVertexPositions.push([xy[i][0], xy[i][1] + 1, 0])
        grassVertexPositions.push([xy[i][0] + 1, xy[i][1], 0])
    }

    for (var i = 0; i < grassVertexPositions.length; i++) {
        for (var j = 0; j < river.length; j++) {
            if (river[j][0] == grassVertexPositions[i][0] && river[j][1] == grassVertexPositions[i][1]) {
                grassVertexPositions[i][2] = -0.5
            }
        }
    }
    console.log(grassVertexPositions)

    waterVertexPositions.push([0, 0, -0.2])
    waterVertexPositions.push([maxX, 0, -0.2])
    waterVertexPositions.push([0, maxY, -0.2])

    waterVertexPositions.push([maxX, maxY, -0.2])
    waterVertexPositions.push([0, maxY, -0.2])
    waterVertexPositions.push([maxX, 0, -0.2])

    var grassVertices = new Float32Array(grassVertexPositions.length * 3),
        waterVertices = new Float32Array(waterVertexPositions.length * 3)

// components of the position vector for each vertex are stored
// contiguously in the buffer.
    for (var i = 0; i < grassVertexPositions.length; i++) {
        grassVertices[i * 3 + 0] = grassVertexPositions[i][0];
        grassVertices[i * 3 + 1] = grassVertexPositions[i][1];
        grassVertices[i * 3 + 2] = grassVertexPositions[i][2];
    }

    for (var i = 0; i < waterVertexPositions.length; i++) {
        waterVertices[i * 3 + 0] = waterVertexPositions[i][0];
        waterVertices[i * 3 + 1] = waterVertexPositions[i][1];
        waterVertices[i * 3 + 2] = waterVertexPositions[i][2];
    }

// itemSize = 3 because there are 3 values (components) per vertex
    grassGeometry.addAttribute('position', new THREE.BufferAttribute(grassVertices, 3))
    waterGeometry.addAttribute('position', new THREE.BufferAttribute(waterVertices, 3))


    var textureLoader = new THREE.TextureLoader();
    textureLoader.load('/img/deska_0.png', function (texture) {

        var grassMaterial = new THREE.MeshBasicMaterial({map: texture}),
            waterMaterial = new THREE.MeshBasicMaterial({color: 0x0000ff}),
            grassMesh = new THREE.Mesh(grassGeometry, grassMaterial),
            waterMesh = new THREE.Mesh(waterGeometry, waterMaterial)

        grassMesh.rotation.x = -Math.PI / 2;
        waterMesh.rotation.x = -Math.PI / 2;

        scene.add(grassMesh);
        scene.add(waterMesh);
    });

};

$(document).ready(function () {
    Three.init();
});
