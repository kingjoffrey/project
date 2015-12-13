var Ground = new function (maxX, maxY, textureName) {
    var xy = [],
        uv = [],
        grassGeometry = new THREE.BufferGeometry(),
        grassVertexPositions = []

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

        grassGeometry.addAttribute('position', new THREE.BufferAttribute(grassVertices, 3))
        grassGeometry.addAttribute('normal', new THREE.BufferAttribute(normals, 3))
        grassGeometry.addAttribute('color', new THREE.BufferAttribute(colors, 3))
        grassGeometry.addAttribute('uv', new THREE.BufferAttribute(uvs, 2))

        grassGeometry.computeVertexNormals()

        var textureLoader = new THREE.TextureLoader();
        textureLoader.load(textureName, function (texture) {

            var grassMaterial = new THREE.MeshLambertMaterial({map: texture}),
                grassMesh = new THREE.Mesh(grassGeometry, grassMaterial)

            grassMesh.rotation.x = -Math.PI / 2;
            Test.getScene().add(grassMesh)

            //var helper = new THREE.WireframeHelper(grassMesh, 0xff00ff);
            //helper.material.linewidth = 1;
            //Test.getScene().add(helper);
            //
            //console.log(grassMesh.geometry.attributes)
        });
    }
}
