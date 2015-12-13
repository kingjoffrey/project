var Test = new function () {
    var scene = new THREE.Scene(),
        camera = new THREE.PerspectiveCamera(10, window.innerWidth / window.innerHeight, 1, 1000),
        renderer = new THREE.WebGLRenderer()

    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.shadowMap.enabled = true

    camera.position.set(-150, 150, 150);
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
        uv = [],
        maxX = 20,
        maxY = 20,
        river = [[0, 5], [0, 4], [1, 3], [2, 2], [3, 2], [4, 1], [5, 1], [6, 0]],
        grassGeometry = new THREE.BufferGeometry(),
        grassVertexPositions = [],
        imageArray = [
            [0, 0],
            [1, 0],
            [0, 1],
            [1, 1]
        ]

    this.init = function () {
        for (var u = 0; u < maxX; u++) {
            uv[u] = []
            for (var v = 0; v < maxY; v++) {
                uv[u][v] = []
                uv[u][v][0] = [u / maxX, v / maxY]
                uv[u][v][1] = [(u + 1) / maxX, v / maxY]
                uv[u][v][2] = [u / maxX, (v + 1) / maxY]
                uv[u][v][3] = [(u + 1) / maxX, (v + 1) / maxY]
            }
        }
        console.log(imageArray)
        console.log(uv)

        for (var i = 0; i < maxX; i++) {
            for (var j = 0; j < maxY; j++) {
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

        var grassVertices = new Float32Array(grassVertexPositions.length * 3),
            normals = new Float32Array(grassVertexPositions.length * 3),
            colors = new Float32Array(grassVertexPositions.length * 3),
            uvs = new Float32Array(grassVertexPositions.length * 2)

        for (var i = 0; i < grassVertexPositions.length; i++) {
            var index = 3 * i
            grassVertices[index + 0] = grassVertexPositions[i][0]
            grassVertices[index + 1] = grassVertexPositions[i][1]
            grassVertices[index + 2] = grassVertexPositions[i][2]
        }

        var k = 0
        for (var u = 0; u < maxX; u++) {
            for (var v = 0; v < maxY; v++) {
                // first triangle
                uvs[0 + k] = uv[u][v][0][0]
                uvs[1 + k] = uv[u][v][0][1]
                uvs[2 + k] = uv[u][v][1][0]
                uvs[3 + k] = uv[u][v][1][1]
                uvs[4 + k] = uv[u][v][2][0]
                uvs[5 + k] = uv[u][v][2][1]
                // second triangle
                uvs[6 + k] = uv[u][v][3][0]
                uvs[7 + k] = uv[u][v][3][1]
                uvs[8 + k] = uv[u][v][2][0]
                uvs[9 + k] = uv[u][v][2][1]
                uvs[10 + k] = uv[u][v][1][0]
                uvs[11 + k] = uv[u][v][1][1]
                k += 12
            }
        }
        console.log(uvs)

        grassGeometry.addAttribute('position', new THREE.BufferAttribute(grassVertices, 3))
        grassGeometry.addAttribute('normal', new THREE.BufferAttribute(normals, 3))
        grassGeometry.addAttribute('color', new THREE.BufferAttribute(colors, 3))
        grassGeometry.addAttribute('uv', new THREE.BufferAttribute(uvs, 2))

        grassGeometry.computeVertexNormals()

        var textureLoader = new THREE.TextureLoader();
        textureLoader.load('/img/testface.png', function (texture) {

            var grassMaterial = new THREE.MeshLambertMaterial({map: texture}),
                grassMesh = new THREE.Mesh(grassGeometry, grassMaterial)

            //grassMesh.rotation.x = -Math.PI / 2;
            Test.getScene().add(grassMesh)

            var helper = new THREE.WireframeHelper(grassMesh, 0xff00ff);
            helper.material.linewidth = 1;
            Test.getScene().add(helper);

            console.log(grassMesh.geometry.attributes)
        });
    }
}

$(document).ready(function () {
    Test.init()
    CreateSimpleMesh.init()
});
