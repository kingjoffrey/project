var Test = new function () {
    var scene = new THREE.Scene(),
        camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 1000),
        renderer = new THREE.WebGLRenderer()

    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.shadowMap.enabled = true

    camera.position.set(-5, 5, 5);
    camera.rotation.order = 'YXZ';
    camera.rotation.y = -Math.PI / 4;
    camera.rotation.x = Math.atan(-1 / Math.sqrt(2));
    scene.add(camera)

    scene.add(new THREE.AmbientLight(0x222222));

    var light = new THREE.PointLight(0xffffff, 0.7);
    camera.add(light);

    this.init = function () {
        $('body').append(renderer.domElement);
        Test.render();
    }
    this.render = function () {
        requestAnimationFrame(Test.render);
        renderer.render(scene, camera);
    }
    this.getScene = function getScene() {
        return scene
    }
}

var CreateSimpleMesh = new function () {
    var xy = [],
        maxX = 10,
        maxY = 10,
        river = [[0, 5], [0, 4], [1, 3], [2, 2], [3, 2], [4, 1], [5, 1], [6, 0]],
        grassGeometry = new THREE.BufferGeometry(),
        grassVertexPositions = [],
        color = new THREE.Color()

    this.init = function () {
        for (i = 0; i < maxX; i++) {
            for (j = 0; j < maxY; j++) {
                xy.push([i, j])
            }
        }

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

        var grassVertices = new Float32Array(grassVertexPositions.length * 3),
            normals = new Float32Array(grassVertexPositions.length * 3),
            colors = new Float32Array(grassVertexPositions.length * 3),
            uvs = new Float32Array(grassVertexPositions.length * 2)

        for (var i = 0; i < grassVertexPositions.length; i++) {
            var index = 3 * i
            grassVertices[index + 0] = grassVertexPositions[i][0];
            grassVertices[index + 1] = grassVertexPositions[i][1];
            grassVertices[index + 2] = grassVertexPositions[i][2];

            //color.setHSL(i / grassVertexPositions.length, 1.0, 0.5)
            //colors[index] = color.r;
            //colors[index + 1] = color.g;
            //colors[index + 2] = color.b;
            //
            uvs[index] = 0
            uvs[index + 1] = 1
        }

        grassGeometry.addAttribute('position', new THREE.BufferAttribute(grassVertices, 3))
        grassGeometry.addAttribute('normal', new THREE.BufferAttribute(normals, 3))
        grassGeometry.addAttribute('color', new THREE.BufferAttribute(colors, 3))
        grassGeometry.addAttribute('uv', new THREE.BufferAttribute(uvs, 2))

        grassGeometry.computeVertexNormals();

        var textureLoader = new THREE.TextureLoader();
        textureLoader.load('/img/maps/12.png', function (texture) {

            var grassMaterial = new THREE.MeshLambertMaterial({map: texture}),
                grassMesh = new THREE.Mesh(grassGeometry, grassMaterial)

            grassMesh.rotation.x = -Math.PI / 2;
            Test.getScene().add(grassMesh)

            console.log(grassMesh.geometry.attributes)
        });

        //var grassMaterial = new THREE.MeshLambertMaterial({color: 0x00ff00}),
        //    grassMesh = new THREE.Mesh(grassGeometry, grassMaterial)

        //var material = new THREE.MeshPhongMaterial({
        //    color: 0xffffff,
        //    shading: THREE.FlatShading,
        //    vertexColors: THREE.VertexColors,
        //    side: THREE.DoubleSide
        //});
        //grassMesh = new THREE.Mesh(grassGeometry, material)

        //grassMesh.rotation.x = -Math.PI / 2
        //Test.getScene().add(grassMesh);

        //console.log(grassMesh.geometry.attributes)
    }

    //var ground = new THREE.Mesh(new THREE.PlaneBufferGeometry(1, 1), new THREE.MeshLambertMaterial({color: 0xffffff}));
    //console.log(ground.geometry.attributes)
}

$(document).ready(function () {
    Test.init()
    CreateSimpleMesh.init()
});
