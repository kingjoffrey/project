var Three = new function () {
    var scene = new THREE.Scene(),
        camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 1000),
        renderer = new THREE.WebGLRenderer()

    camera.position.set(-5, 5, 5);
    camera.rotation.order = 'YXZ';
    camera.rotation.y = -Math.PI / 4;
    camera.rotation.x = Math.atan(-1 / Math.sqrt(2));

    renderer.setSize(window.innerWidth, window.innerHeight)
    renderer.shadowMap.enabled = true

    var light = new THREE.AmbientLight(0xffffff) //new THREE.DirectionalLight(0xffffff, 1)
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


    var assignFaces = function (geometry) {
        var holes = [],
            triangles

        console.log(geometry.attributes.position.array)
        triangles = THREE.Shape.Utils.triangulateShape (geometry.attributes.position.array, holes)

        for (var i = 0; i < triangles.length; i++) {
            geometry.faces.push(new THREE.Face3(triangles[i][0], triangles[i][1], triangles[i][2]));
        }
    }
    var assignUVs = function (geometry) {

        geometry.faceVertexUvs = [];
        geometry.faceVertexUvs[0] = [];

        geometry.faces.forEach(function (face) {

            var components = ['x', 'y', 'z'].sort(function (a, b) {
                return Math.abs(face.normal[a]) > Math.abs(face.normal[b]);
            });

            var v1 = geometry.vertices[face.a];
            var v2 = geometry.vertices[face.b];
            var v3 = geometry.vertices[face.c];

            geometry.faceVertexUvs[0].push([
                new THREE.Vector2(v1[components[0]], v1[components[1]]),
                new THREE.Vector2(v2[components[0]], v2[components[1]]),
                new THREE.Vector2(v3[components[0]], v3[components[1]])
            ]);

        });

        geometry.uvsNeedUpdate = true;
    }
    var textureLoader = new THREE.TextureLoader();
    textureLoader.load('/img/deska_0.png', function (texture) {
        //assignFaces(waterGeometry)
        //assignUVs(grassGeometry)

        var grassMaterial = new THREE.MeshLambertMaterial({map: texture}),
            waterMaterial = new THREE.MeshBasicMaterial({color: 0x0000ff}),
            grassMesh = new THREE.Mesh(grassGeometry, grassMaterial),
            waterMesh = new THREE.Mesh(waterGeometry, waterMaterial)

        grassMesh.rotation.x = -Math.PI / 2;
        waterMesh.rotation.x = -Math.PI / 2;
        console.log(grassMesh)
        scene.add(grassMesh);
        scene.add(waterMesh);
    });

};

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
        maxX = 1,
        maxY = 1,
        river = [[0, 0]],//[[0, 5], [0, 4], [1, 3], [2, 2], [3, 2], [4, 1], [5, 1], [6, 0]],
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

            color.setHSL(i / grassVertexPositions.length, 1.0, 0.5)
            colors[index] = color.r;
            colors[index + 1] = color.g;
            colors[index + 2] = color.b;

            //uvs[index] = Math.random(); // just something...
            //uvs[index + 1] = Math.random();
        }

        grassGeometry.addAttribute('position', new THREE.BufferAttribute(grassVertices, 3))
        grassGeometry.addAttribute('normal', new THREE.BufferAttribute(normals, 3))
        grassGeometry.addAttribute('color', new THREE.BufferAttribute(colors, 3))
        grassGeometry.addAttribute('uv', new THREE.BufferAttribute(uvs, 2))

        grassGeometry.computeVertexNormals();

        //var textureLoader = new THREE.TextureLoader();
        //textureLoader.load('/img/deska_0.png', function (texture) {
        //
        //    var grassMaterial = new THREE.MeshLambertMaterial({map: texture}),
        //        grassMesh = new THREE.Mesh(grassGeometry, grassMaterial)
        //
        //    grassMesh.rotation.x = -Math.PI / 2;
        //    Test.getScene().add(grassMesh);
        //});

        //var grassMaterial = new THREE.MeshLambertMaterial({color: 0x00ff00}),
        //    grassMesh = new THREE.Mesh(grassGeometry, grassMaterial)

        var material = new THREE.MeshPhongMaterial({
            color: 0xffffff,
            shading: THREE.FlatShading,
            vertexColors: THREE.VertexColors,
            side: THREE.DoubleSide
        });
        grassMesh = new THREE.Mesh(grassGeometry, material)

        grassMesh.rotation.x = -Math.PI / 2
        Test.getScene().add(grassMesh);
    }

    var ground = new THREE.Mesh(new THREE.PlaneBufferGeometry(436, 624), new THREE.MeshLambertMaterial({map: THREE.ImageUtils.loadTexture('/img/maps/1.png')}));
    console.log(ground)
}

$(document).ready(function () {
    Test.init()
    CreateSimpleMesh.init()
});
