var Ground = new function () {
    var mountainLevel = 1.5,
        hillLevel = 0.5,
        bottomLevel = 0.2,
        waterLevel = 0.11
    createWater = function (maxX, maxY) {
        var waterVertexPositions = [],
            waterVertices = new Float32Array(18),
            waterGeometry = new THREE.BufferGeometry(),
            waterMaterial = new THREE.MeshBasicMaterial({color: 0x0000ff, side: THREE.DoubleSide})

        waterVertexPositions.push([0, 0, waterLevel])
        waterVertexPositions.push([maxX, 0, waterLevel])
        waterVertexPositions.push([0, maxY, waterLevel])

        waterVertexPositions.push([maxX, maxY, waterLevel])
        waterVertexPositions.push([0, maxY, waterLevel])
        waterVertexPositions.push([maxX, 0, waterLevel])

        for (var i = 0; i < 6; i++) {
            var index = i * 3
            waterVertices[index + 0] = waterVertexPositions[i][0]
            waterVertices[index + 1] = waterVertexPositions[i][1]
            waterVertices[index + 2] = waterVertexPositions[i][2]
        }

        waterGeometry.addAttribute('position', new THREE.BufferAttribute(waterVertices, 3))
        var waterMesh = new THREE.Mesh(waterGeometry, waterMaterial)
        waterMesh.rotation.x = Math.PI / 2
        Scene.add(waterMesh)
    },
        createGround = function (maxX, maxY, textureName) {
            var xy = [],
                uv = [],
                grassGeometry = new THREE.BufferGeometry(),
                grassVertexPositions = []

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
                grassVertexPositions.push([xy[i][0], xy[i][1], 0])           //
                grassVertexPositions.push([xy[i][0] + 1, xy[i][1], 0])       //  FIRST TRIANGLE
                grassVertexPositions.push([xy[i][0], xy[i][1] + 1, 0])       //

                grassVertexPositions.push([xy[i][0] + 1, xy[i][1] + 1, 0])   //
                grassVertexPositions.push([xy[i][0], xy[i][1] + 1, 0])       //  SECOND TRIANGLE
                grassVertexPositions.push([xy[i][0] + 1, xy[i][1], 0])       //
            }
            for (var i = 0; i < grassVertexPositions.length; i++) {
                if (grassVertexPositions[i][0] == 0) {
                    grassVertexPositions[i][2] = 0.1
                    continue
                }
                if (grassVertexPositions[i][0] == maxX) {
                    grassVertexPositions[i][2] = 0.1
                    continue
                }
                if (grassVertexPositions[i][1] == 0) {
                    grassVertexPositions[i][2] = 0.1
                    continue
                }
                if (grassVertexPositions[i][1] == maxY) {
                    grassVertexPositions[i][2] = 0.1
                    continue
                }
                if (grassVertexPositions[i][0] % 2 == 0 && grassVertexPositions[i][1] % 2 == 0) {
                    var type = Fields.get(grassVertexPositions[i][0] / 2, grassVertexPositions[i][1] / 2).getType()
                    switch (type) {
                        case 'w':
                            grassVertexPositions[i][2] = bottomLevel
                            //grassVertexPositions[i+3][2] = bottomLevel
                            console.log(grassVertexPositions[i])
                            console.log(grassVertexPositions[i+1])
                            console.log(grassVertexPositions[i+2])
                            console.log(grassVertexPositions[i+3])
                            console.log(grassVertexPositions[i+4])
                            console.log(grassVertexPositions[i+5])
                            //console.log(grassVertexPositions[i+12])
                            return
                            //if (grassVertexPositions[i][0] < maxX && grassVertexPositions[i][1] < maxY) {
                            //    if (Fields.get(grassVertexPositions[i + 12][0] / 2, grassVertexPositions[i + 12][1] / 2).getType() == 'w') {
                            //        grassVertexPositions[i + 3][2] = -bottomLevel*4
                            //    }
                            //}
                            break
                        case 'b':
                            grassVertexPositions[i][2] = bottomLevel
                            break
                        case 'm':
                            grassVertexPositions[i][2] = -mountainLevel
                            //if (grassVertexPositions[i][0] < maxX && grassVertexPositions[i][1] < maxY) {
                            //    if (Fields.get(grassVertexPositions[i + 11][0] / 2, grassVertexPositions[i + 11][1] / 2).getType() == 'm') {
                            //        grassVertexPositions[i + 3][2] = -mountainLevel
                            //    }
                            //}
                            break
                        case 'h':
                            grassVertexPositions[i][2] = -hillLevel
                            break
                    }
                }
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
            //grassGeometry.addAttribute('color', new THREE.BufferAttribute(colors, 3))
            grassGeometry.addAttribute('uv', new THREE.BufferAttribute(uvs, 2))

            grassGeometry.computeVertexNormals()

            var textureLoader = new THREE.TextureLoader()
            textureLoader.load(textureName, function (texture) {

                var grassMaterial = new THREE.MeshLambertMaterial({map: texture, side: THREE.DoubleSide}),
                    grassMesh = new THREE.Mesh(grassGeometry, grassMaterial)

                grassMesh.rotation.x = Math.PI / 2
                Scene.add(grassMesh)
                Picker.attach(grassMesh)

                var helper = new THREE.WireframeHelper(grassMesh, 0xff00ff)
                helper.material.linewidth = 1
                Scene.add(helper)
            })
        }
    this.init = function (maxX, maxY, textureName) {
        createGround(maxX * 2, maxY * 2, textureName)
        //createWater(maxX * 2, maxY * 2)
    }
    this.getMountainLevel = function () {
        return mountainLevel
    }
    this.getHillLevel = function () {
        return hillLevel
    }
}
