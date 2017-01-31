var Ground = new function () {
    var mountainLevel = 1.95,
        hillLevel = 0.9,
        bottomLevel = 2,
        waterLevel = 0.1,
        cloudsLevel = -30,
        grassGeometry = new THREE.BufferGeometry(),
        grassMesh,
        grassMaterial,
        tl = new THREE.TextureLoader(),
        createWater = function (x, y, canvas) {
            var maxX = x * 2,
                maxY = y * 2

            var texture = new THREE.Texture(canvas)
            texture.needsUpdate = true

            var mesh = new THREE.Mesh(new THREE.PlaneBufferGeometry(maxX, maxY), new THREE.MeshLambertMaterial({
                map: texture,
                side: THREE.DoubleSide
            }))
            mesh.rotation.x = Math.PI / 2
            mesh.position.set(maxX / 2, -waterLevel, maxY / 2)
            GameScene.add(mesh)
            PickerCommon.attach(mesh)
            if (GameScene.getShadows()) {
                mesh.receiveShadow = true
            }
        },
        createGround = function (x, y, canvas) {
            var maxX = x * 2,
                maxY = y * 2,
                xy = [],
                uv = [],
                maxI = maxX * maxY * 6,
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
                    grassVertexPositions[i][2] = waterLevel - 0.01
                }
                if (grassVertexPositions[i][0] == maxX) {
                    grassVertexPositions[i][2] = waterLevel - 0.01
                }
                if (grassVertexPositions[i][1] == 0) {
                    grassVertexPositions[i][2] = waterLevel - 0.01
                }
                if (grassVertexPositions[i][1] == maxY) {
                    grassVertexPositions[i][2] = waterLevel - 0.01
                }
                // every field?
                if (grassVertexPositions[i][0] % 2 == 0 && grassVertexPositions[i][1] % 2 == 0) {
                    var type = Fields.get(grassVertexPositions[i][0] / 2, grassVertexPositions[i][1] / 2, 1).getType()
                    switch (type) {
                        case 'w':
                            grassVertexPositions = changeGroundLevel(grassVertexPositions, maxX, maxY, maxI, i, bottomLevel, type)
                            break
                        case 'b':
                            grassVertexPositions = changeGroundLevel(grassVertexPositions, maxX, maxY, maxI, i, bottomLevel, 'w')
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
            grassVertexPositions = adjustMountainLevels(grassVertexPositions, maxX, maxY)

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

            var texture = new THREE.Texture(canvas)
            texture.needsUpdate = true

            grassMaterial = new THREE.MeshLambertMaterial({
                map: texture,
                side: THREE.DoubleSide
            })
            grassMesh = new THREE.Mesh(grassGeometry, grassMaterial)
            grassMesh.rotation.x = Math.PI / 2
            if (GameScene.getShadows()) {
                grassMesh.receiveShadow = true
            }
            GameScene.add(grassMesh)
            PickerCommon.attach(grassMesh)

            //var helper = new THREE.WireframeHelper(grassMesh, 0xff00ff)
            //helper.material.linewidth = 1
            //GameScene.add(helper)
        },
        adjustMountainLevels = function (gVP, maxX, maxY) {
            for (var i = 0; i < gVP.length; i++) {
                if (gVP[i][0] == 0) {
                    continue
                }
                if (gVP[i][0] == maxX) {
                    continue
                }
                if (gVP[i][1] == 0) {
                    continue
                }
                if (gVP[i][1] == maxY) {
                    continue
                }
                if (gVP[i][0] % 2 != 0 || gVP[i][1] % 2 != 0) {
                    continue
                }
                if (i % 12 != 0) {
                    continue
                }
                if (Fields.get(gVP[i][0] / 2, gVP[i][1] / 2, 1).getType() == 'm') {
                    var between = maxY * 6
                    if (gVP[i][2] == 0) {
                        gVP[i][2] = -hillLevel
                        gVP[i - 2][2] = -hillLevel
                        gVP[i - 4][2] = -hillLevel
                        gVP[i - between - 3][2] = -hillLevel                //
                        gVP[i - between + 1][2] = -hillLevel                //
                        gVP[i - between + 5][2] = -hillLevel               //
                    }

                    if (gVP[i + 2][2] == 0) {
                        gVP[i + 2][2] = -hillLevel
                        gVP[i + 4][2] = -hillLevel
                        gVP[i + 6][2] = -hillLevel
                        gVP[i - between + 3][2] = -hillLevel  //
                        gVP[i - between + 7][2] = -hillLevel  //
                        gVP[i - between + 11][2] = -hillLevel //
                    }

                    if (gVP[i + 8][2] == 0) {
                        gVP[i + 8][2] = -hillLevel
                        gVP[i + 10][2] = -hillLevel
                        gVP[i + 12][2] = -hillLevel
                        gVP[i - between + 9][2] = -hillLevel  //
                        gVP[i - between + 13][2] = -hillLevel  //
                        gVP[i - between + 17][2] = -hillLevel //
                    }

                    if (gVP[i + 1][2] == 0) {
                        gVP[i - 3][2] = -hillLevel  //
                        gVP[i + 1][2] = -hillLevel  //
                        gVP[i + 5][2] = -hillLevel  //
                        gVP[i + between][2] = -hillLevel  //
                        gVP[i + between - 2][2] = -hillLevel
                        gVP[i + between - 4][2] = -hillLevel
                    }

                    if (gVP[i + 9][2] == 0) {
                        gVP[i + 9][2] = -hillLevel  //
                        gVP[i + 13][2] = -hillLevel  //
                        gVP[i + 17][2] = -hillLevel  //
                        gVP[i + between + 8][2] = -hillLevel  //
                        gVP[i + between + 10][2] = -hillLevel
                        gVP[i + between + 12][2] = -hillLevel
                    }

                    var between2 = 2 * between
                    if (gVP[i + between + 1][2] == 0) {
                        gVP[i + between - 3][2] = -hillLevel  //
                        gVP[i + between + 1][2] = -hillLevel  //
                        gVP[i + between + 5][2] = -hillLevel  //
                        gVP[i + between2][2] = -hillLevel  //
                        gVP[i + between2 - 2][2] = -hillLevel
                        gVP[i + between2 - 4][2] = -hillLevel
                    }

                    if (gVP[i + between + 3][2] == 0) {
                        gVP[i + between + 3][2] = -hillLevel  //
                        gVP[i + between + 7][2] = -hillLevel  //
                        gVP[i + between + 11][2] = -hillLevel  //
                        gVP[i + between2 + 2][2] = -hillLevel  //
                        gVP[i + between2 + 4][2] = -hillLevel
                        gVP[i + between2 + 6][2] = -hillLevel
                    }

                    if (gVP[i + between + 9][2] == 0) {
                        gVP[i + between + 9][2] = -hillLevel  //
                        gVP[i + between + 13][2] = -hillLevel  //
                        gVP[i + between + 17][2] = -hillLevel  //
                        gVP[i + between2 + 8][2] = -hillLevel  //
                        gVP[i + between2 + 10][2] = -hillLevel
                        gVP[i + between2 + 12][2] = -hillLevel
                    }
                }
            }
            return gVP
        },
        changeGroundLevel = function (gVP, maxX, maxY, maxI, i, level, type) {
            //if (type == 'w') {
            var rand = 0
            //var rand = -0.1
            //} else {
            //    var rand = -Math.random() / 5
            //}

            if (i % 12 == 0) {
                gVP[i + 3][2] = level + rand                //
                gVP[i + 7][2] = level + rand                //
                gVP[i + 11][2] = level + rand               //
                var between = maxY * 6 + 6                                   //
                if (i + between < maxI) {                                    // center vertex of the field
                    gVP[i + between][2] = level + rand      //
                    gVP[i + between - 2][2] = level + rand  //
                    gVP[i + between - 4][2] = level + rand  //
                }

                //rand = Math.random() / 2
                if ((i + 12) % (maxY * 6) != 0 && Fields.get(gVP[i + 12][0] / 2, gVP[i + 12][1] / 2, 1).getTypeWithoutBridge() == type) {
                    gVP[i + 9][2] = level - rand                //
                    gVP[i + 13][2] = level - rand               //
                    gVP[i + 17][2] = level - rand               //
                    var between = maxY * 6 + 12                                  //
                    if (i + between < maxI) {                                        // vertex between two centers od the field on Y axis
                        gVP[i + between][2] = level - rand      //
                        gVP[i - 2 + between][2] = level - rand  //
                        gVP[i - 4 + between][2] = level - rand  //
                    }                                                            //
                }
                //
                //rand = Math.random() / 2
                var nextRow = maxY * 2 * 6
                if (i + nextRow < maxI && Fields.get(gVP[i + nextRow][0] / 2, gVP[i + nextRow][1] / 2, 1).getTypeWithoutBridge() == type) {
                    gVP[i + nextRow + 6][2] = level - rand  //
                    gVP[i + nextRow + 4][2] = level - rand  //
                    gVP[i + nextRow + 2][2] = level - rand  //
                    var between = maxY * 6                                   //
                    gVP[i + between + 3][2] = level - rand  // vertex between two centers od the field on X axis
                    gVP[i + between + 7][2] = level - rand  //
                    gVP[i + between + 11][2] = level - rand //
                }                                                            //

                //rand = Math.random() / 2
                var nextVertex = nextRow + 12
                if (i + nextVertex < maxI && (i + nextVertex) % (maxY * 6) != 0 && Fields.get(gVP[i + nextVertex][0] / 2, gVP[i + nextVertex][1] / 2, 1).getTypeWithoutBridge() == type) {
                    gVP[i + nextVertex][2] = level - rand       //
                    gVP[i + nextVertex - 2][2] = level - rand   //
                    gVP[i + nextVertex - 4][2] = level - rand   //
                    var between = maxY * 6                                       //
                    gVP[i + between + 9][2] = level - rand      // vertex between two centers od the field on X and Y axis
                    gVP[i + between + 13][2] = level - rand     //
                    gVP[i + between + 17][2] = level - rand     //
                }                                                                //
            }
            return gVP
        }
    this.getMountainLevel = function () {
        return mountainLevel
    }
    this.getHillLevel = function () {
        return hillLevel
    }
    this.getWaterLevel = function () {
        return waterLevel
    }
    this.change = function (x, y, type) {
        if (isSet(x)) {
            Fields.get(x, y).setType(type)
        }

        GameScene.remove(grassMesh)
        Fields.createTextures()
        createGround(Fields.getMaxX(), Fields.getMaxY(), Fields.getCanvas())
    }
    this.init = function (maxX, maxY, groundCanvas, waterCanvas) {
        createGround(maxX, maxY, groundCanvas)
        createWater(maxX, maxY, waterCanvas)
    }
}
