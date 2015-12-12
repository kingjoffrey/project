var Test = new function () {
    var scene = new THREE.Scene(),
        camera = new THREE.PerspectiveCamera(10, window.innerWidth / window.innerHeight, 1, 1000),
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
        uv = [],
        maxX = 2,
        maxY = 2,
        river = [[0, 5], [0, 4], [1, 3], [2, 2], [3, 2], [4, 1], [5, 1], [6, 0]],
        grassGeometry = new THREE.BufferGeometry(),
        grassVertexPositions = [],
        imageArray = [
            [0, 0],
            [1, 0],
            [0, 1],
            [1, 1]
        ],
        image = new Image()

    this.init = function () {
        for (var i = 0; i < maxX; i++) {
            for (var j = 0; j < maxY; j++) {
                xy.push([i, j])
            }
        }
        //console.log(xy)
        for (var i = 0; i < xy.length; i++) {
            grassVertexPositions.push([xy[i][0], xy[i][1], 0])
            grassVertexPositions.push([xy[i][0] + 1, xy[i][1], 0])
            grassVertexPositions.push([xy[i][0], xy[i][1] + 1, 0])

            grassVertexPositions.push([xy[i][0] + 1, xy[i][1] + 1, 0])
            grassVertexPositions.push([xy[i][0], xy[i][1] + 1, 0])
            grassVertexPositions.push([xy[i][0] + 1, xy[i][1], 0])
        }
        //console.log(grassVertexPositions)
        //for (var i = 0; i < grassVertexPositions.length; i++) {
        //    for (var j = 0; j < river.length; j++) {
        //        if (river[j][0] == grassVertexPositions[i][0] && river[j][1] == grassVertexPositions[i][1]) {
        //            grassVertexPositions[i][2] = -0.5
        //        }
        //    }
        //}

        var grassVertices = new Float32Array(grassVertexPositions.length * 3),
            normals = new Float32Array(grassVertexPositions.length * 3),
            colors = new Float32Array(grassVertexPositions.length * 3),
            uvs = new Float32Array(grassVertexPositions.length * 2)

        for (var k = 0; k < maxX * maxY; k++) {
            uv[k] = []
            for (var i = 0; i <= maxY; i++) {
                for (var j = 0; j <= maxX; j++) {
                    uv[k].push([j / maxX, i / maxY])
                }
            }
        }
        console.log(imageArray)
        console.log(uv)
        var j = 0,
            k = 0

        for (var i = 0; i < grassVertexPositions.length; i++) {
            var index = 3 * i
            if (j > 5) {
                j = 0
                k += 12
            }
            grassVertices[index + 0] = grassVertexPositions[i][0]
            grassVertices[index + 1] = grassVertexPositions[i][1]
            grassVertices[index + 2] = grassVertexPositions[i][2]
            switch (j) {
                // first triangle
                case 0:
                    uvs[0 + k] = imageArray[0][0] / maxX
                    uvs[1 + k] = imageArray[0][1] / maxY
                    break
                case 1:
                    uvs[2 + k] = imageArray[1][0] / maxX
                    uvs[3 + k] = imageArray[1][1] / maxY
                    break
                case 2:
                    uvs[4 + k] = imageArray[2][0] / maxX
                    uvs[5 + k] = imageArray[2][1] / maxY
                    break
                // second triangle
                case 3:
                    uvs[6 + k] = imageArray[3][0] / maxX
                    uvs[7 + k] = imageArray[3][1] / maxY
                    break
                case 4:
                    uvs[8 + k] = imageArray[2][0] / maxX
                    uvs[9 + k] = imageArray[2][1] / maxY
                    break
                case 5:
                    uvs[10 + k] = imageArray[1][0] / maxX
                    uvs[11 + k] = imageArray[1][1] / maxY
                    break
            }
            j++
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

            grassMesh.rotation.x = -Math.PI / 2;
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
