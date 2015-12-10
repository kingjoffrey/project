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
    var grassGeometry = new THREE.BufferGeometry(),
        grassVertexPositions = [],
        imageArray = [
            [0, 0],
            [1, 0],
            [1, 1],
            [0, 1]
        ]

    this.init = function () {
        // first triangle
        grassVertexPositions.push([0, 0, 0])
        grassVertexPositions.push([1, 0, 0])
        grassVertexPositions.push([0, 1, 0])

        // second triangle
        grassVertexPositions.push([1, 1, 0])
        grassVertexPositions.push([0, 1, 0])
        grassVertexPositions.push([1, 0, 0])

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

        uvs[0] = imageArray[0][0]
        uvs[1] = imageArray[0][1]

        uvs[2] = imageArray[1][0]
        uvs[3] = imageArray[1][1]

        uvs[4] = imageArray[3][0]
        uvs[5] = imageArray[3][1]

        uvs[6] = imageArray[1][0]
        uvs[7] = imageArray[1][1]

        uvs[8] = imageArray[2][0]
        uvs[9] = imageArray[2][1]

        uvs[10] = imageArray[3][0]
        uvs[11] = imageArray[3][1]

        grassGeometry.addAttribute('position', new THREE.BufferAttribute(grassVertices, 3))
        grassGeometry.addAttribute('normal', new THREE.BufferAttribute(normals, 3))
        grassGeometry.addAttribute('color', new THREE.BufferAttribute(colors, 3))
        grassGeometry.addAttribute('uv', new THREE.BufferAttribute(uvs, 2))

        grassGeometry.computeVertexNormals()

        var textureLoader = new THREE.TextureLoader();
        textureLoader.load('/img/maps/12.png', function (texture) {

            var grassMaterial = new THREE.MeshLambertMaterial({map: texture}),
                grassMesh = new THREE.Mesh(grassGeometry, grassMaterial)

            grassMesh.rotation.x = -Math.PI / 2;
            Test.getScene().add(grassMesh)

            var helper = new THREE.WireframeHelper(grassMesh, 0xff00ff); // alternate
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
