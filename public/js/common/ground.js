var Ground = new function () {
    var mountainLevel = 1.5,
        hillLevel = 0.5,
        bottomLevel = 0.2,
        waterLevel = 0.11,
        cloudsLevel=100,
        grassVertexPositions = [],
        createClouds = function (maxX, maxY) {
            var cloudsVertexPositions = [],
                cloudsVertices = new Float32Array(18),
                cloudsGeometry = new THREE.BufferGeometry(),
                cloudsMaterial = new THREE.MeshBasicMaterial({
                    color: 0x0000ff,
                    side: THREE.DoubleSide
                })

            cloudsVertexPositions.push([0, 0, cloudsLevel])
            cloudsVertexPositions.push([maxX, 0, cloudsLevel])
            cloudsVertexPositions.push([0, maxY, cloudsLevel])

            cloudsVertexPositions.push([maxX, maxY, cloudsLevel])
            cloudsVertexPositions.push([0, maxY, cloudsLevel])
            cloudsVertexPositions.push([maxX, 0, cloudsLevel])

            for (var i = 0; i < 6; i++) {
                var index = i * 3
                cloudsVertices[index + 0] = cloudsVertexPositions[i][0]
                cloudsVertices[index + 1] = cloudsVertexPositions[i][1]
                cloudsVertices[index + 2] = cloudsVertexPositions[i][2]
            }

            cloudsGeometry.addAttribute('position', new THREE.BufferAttribute(cloudsVertices, 3))
            var waterMesh = new THREE.Mesh(cloudsGeometry, cloudsMaterial)
            waterMesh.rotation.x = Math.PI / 2
            Scene.add(waterMesh)
        },
        createWater = function (maxX, maxY) {
            var waterVertexPositions = [],
                waterVertices = new Float32Array(18),
                waterGeometry = new THREE.BufferGeometry(),
                waterMaterial = new THREE.MeshBasicMaterial({
                    color: 0x0000ff,
                    side: THREE.DoubleSide,
                    transparent: true,
                    opacity: 0.3
                })

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
                grassVertexPositions = [],
                maxI = maxX * maxY * 6

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
            //console.log(grassVertexPositions);
            //return
            for (var i = 0; i < grassVertexPositions.length; i++) {
                if (grassVertexPositions[i][0] == 0) {
                    grassVertexPositions[i][2] = 0.1
                    //continue
                }
                if (grassVertexPositions[i][0] == maxX) {
                    grassVertexPositions[i][2] = 0.1
                    //continue
                }
                if (grassVertexPositions[i][1] == 0) {
                    grassVertexPositions[i][2] = 0.1
                    //continue
                }
                if (grassVertexPositions[i][1] == maxY) {
                    grassVertexPositions[i][2] = 0.1
                    //continue
                }
                // every field?
                if (grassVertexPositions[i][0] % 2 == 0 && grassVertexPositions[i][1] % 2 == 0) {
                    var type = Fields.get(grassVertexPositions[i][0] / 2, grassVertexPositions[i][1] / 2).getType()
                    switch (type) {
                        case 'w':
                            grassVertexPositions = changeGroundLevel(grassVertexPositions, maxX, maxY, maxI, i, bottomLevel, type)
                            break
                        case 'b':
                            grassVertexPositions = changeGroundLevel(grassVertexPositions, maxX, maxY, maxI, i, bottomLevel, type)
                            break
                        case 'm':
                            grassVertexPositions = changeGroundLevel(grassVertexPositions, maxX, maxY, maxI, i, -mountainLevel, type)
                            break
                        case 'h':
                            grassVertexPositions = changeGroundLevel(grassVertexPositions, maxX, maxY, maxI, i, -hillLevel, type)
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
                PickerCommon.attach(grassMesh)

                //var helper = new THREE.WireframeHelper(grassMesh, 0xff00ff)
                //helper.material.linewidth = 1
                //Scene.add(helper)
            })
        },
        changeGroundLevel = function (grassVertexPositions, maxX, maxY, maxI, i, level, type) {
            if (i % 12 == 0) {
                grassVertexPositions[i + 3][2] = level                //
                grassVertexPositions[i + 7][2] = level                //
                grassVertexPositions[i + 11][2] = level               //
                var between = maxY * 6 + 6                            //
                if (i + between < maxI) {                             // center vertex of the field
                    grassVertexPositions[i + between][2] = level      //
                    grassVertexPositions[i + between - 2][2] = level  //
                    grassVertexPositions[i + between - 4][2] = level  //
                }

                if ((i + 12) % (maxY * 6) != 0 && Fields.get(grassVertexPositions[i + 12][0] / 2, grassVertexPositions[i + 12][1] / 2).getType() == type) {
                    grassVertexPositions[i + 9][2] = level                //
                    grassVertexPositions[i + 13][2] = level               //
                    grassVertexPositions[i + 17][2] = level               //
                    var between = maxY * 6 + 12                           //
                    if (i + between < maxI) {                             // vertex between two centers od the field on Y axis
                        grassVertexPositions[i + between][2] = level      //
                        grassVertexPositions[i - 2 + between][2] = level  //
                        grassVertexPositions[i - 4 + between][2] = level  //
                    }                                                     //
                }                                                         //

                var nextRow = maxY * 2 * 6
                if (i + nextRow < maxI && Fields.get(grassVertexPositions[i + nextRow][0] / 2, grassVertexPositions[i + nextRow][1] / 2).getType() == type) {
                    grassVertexPositions[i + nextRow + 6][2] = level  //
                    grassVertexPositions[i + nextRow + 4][2] = level  //
                    grassVertexPositions[i + nextRow + 2][2] = level  //
                    var between = maxY * 6                            //
                    grassVertexPositions[i + between + 3][2] = level  // vertex between two centers od the field on X axis
                    grassVertexPositions[i + between + 7][2] = level  //
                    grassVertexPositions[i + between + 11][2] = level //
                }                                                     //

                var nextVertex = nextRow + 12
                if (i + nextVertex < maxI && (i + nextVertex) % (maxY * 6) != 0 && Fields.get(grassVertexPositions[i + nextVertex][0] / 2, grassVertexPositions[i + nextVertex][1] / 2).getType() == type) {
                    grassVertexPositions[i + nextVertex][2] = level       //
                    grassVertexPositions[i + nextVertex - 2][2] = level   //
                    grassVertexPositions[i + nextVertex - 4][2] = level   //
                    var between = maxY * 6                                //
                    grassVertexPositions[i + between + 9][2] = level      // vertex between two centers od the field on X and Y axis
                    grassVertexPositions[i + between + 13][2] = level     //
                    grassVertexPositions[i + between + 17][2] = level     //
                }                                                         //
            }
            return grassVertexPositions
        }
    this.init = function (maxX, maxY, textureName) {
        createGround(maxX * 2, maxY * 2, textureName)
        createWater(maxX * 2, maxY * 2)
    }
    this.getMountainLevel = function () {
        return mountainLevel
    }
    this.getHillLevel = function () {
        return hillLevel
    }
}
