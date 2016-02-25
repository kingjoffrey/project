var Ground = new function () {
    var mountainLevel = 1.95,
        hillLevel = 0.65,
        bottomLevel = 2,
        waterLevel = 0.1,
        cloudsLevel = -30,
        grassVertexPositions = [],
        grassGeometry = new THREE.BufferGeometry(),
        grassMesh,
        grassMaterial,
        tl = new THREE.TextureLoader(),
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
            tl.load('/img/editor/jasny_niebieski.png', function (texture) {
                var mesh = new THREE.Mesh(new THREE.PlaneBufferGeometry(maxX, maxY), new THREE.MeshLambertMaterial({
                    map: texture,
                    //color: 0x0000ff,
                    side: THREE.DoubleSide
                    //transparent: true,
                    //opacity: 0.1
                }))
                mesh.rotation.x = Math.PI / 2
                mesh.position.set(maxX / 2, -waterLevel, maxY / 2)
                Scene.add(mesh)
            })
        },
        ccreateWater = function (maxX, maxY) {
            var light = new THREE.DirectionalLight(0xffffbb, 1);
            light.position.set(-1, 1, -1);
            Scene.add(light)

            var waterNormals = new THREE.TextureLoader().load('/img/editor/jasny_niebieski.png')
            waterNormals.wrapS = waterNormals.wrapT = THREE.RepeatWrapping

            var water = new THREE.Water(Scene.getRenderer(), Scene.getCamera(), Scene.get(), {
                textureWidth: 1024,
                textureHeight: 1024,
                waterNormals: waterNormals,
                alpha: 1.0,
                sunDirection: light.position.clone().normalize(),
                sunColor: 0xffffff,
                waterColor: 0x0000ff,
                distortionScale: 5.0,
            })

            Scene.setWater(water)

            var mirrorMesh = new THREE.Mesh(
                new THREE.PlaneBufferGeometry(maxX, maxY),
                water.material
            )

            mirrorMesh.add(water);
            mirrorMesh.rotation.x = -Math.PI / 2
            mirrorMesh.position.x = maxX / 2
            mirrorMesh.position.y = -waterLevel
            mirrorMesh.position.z = maxY / 2
            Scene.add(mirrorMesh);

            //var cubeMap = new THREE.CubeTexture([]);
            //cubeMap.format = THREE.RGBFormat;
            //
            //var loader = new THREE.ImageLoader();
            //loader.load('/img/skyboxsun25degtest.png', function (image) {
            //
            //    var getSide = function (x, y) {
            //
            //        var size = 1024;
            //
            //        var canvas = document.createElement('canvas');
            //        canvas.width = size;
            //        canvas.height = size;
            //
            //        var context = canvas.getContext('2d');
            //        context.drawImage(image, -x * size, -y * size);
            //
            //        return canvas;
            //
            //    };
            //
            //    cubeMap.images[0] = getSide(2, 1); // px
            //    cubeMap.images[1] = getSide(0, 1); // nx
            //    cubeMap.images[2] = getSide(1, 0); // py
            //    cubeMap.images[3] = getSide(1, 2); // ny
            //    cubeMap.images[4] = getSide(1, 1); // pz
            //    cubeMap.images[5] = getSide(3, 1); // nz
            //    cubeMap.needsUpdate = true;
            //
            //});
            //
            //var cubeShader = THREE.ShaderLib['cube'];
            //cubeShader.uniforms['tCube'].value = cubeMap;
            //
            //var skyBoxMaterial = new THREE.ShaderMaterial({
            //    fragmentShader: cubeShader.fragmentShader,
            //    vertexShader: cubeShader.vertexShader,
            //    uniforms: cubeShader.uniforms,
            //    depthWrite: false,
            //    side: THREE.BackSide
            //});
            //
            //var skyBox = new THREE.Mesh(
            //    new THREE.BoxGeometry(1000, 1000, 1000),
            //    skyBoxMaterial
            //);
            //
            //Scene.add(skyBox)
        },
        createGround = function (maxX, maxY, textureName) {
            var xy = [],
                uv = [],
                maxI = maxX * maxY * 6

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
                    var type = Fields.get(grassVertexPositions[i][0] / 2, grassVertexPositions[i][1] / 2).getType()
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
            adjustMountainLevels(grassVertexPositions, maxX, maxY)

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

            if (isSet(textureName)) {
                tl.load(textureName, function (texture) {
                    grassMaterial = new THREE.MeshLambertMaterial({
                        map: texture,
                        side: THREE.DoubleSide
                    })
                    grassMesh = new THREE.Mesh(grassGeometry, grassMaterial)
                    grassMesh.rotation.x = Math.PI / 2
                    if (Scene.getShadows()) {
                        grassMesh.receiveShadow = true
                    }
                    Scene.add(grassMesh)
                    PickerCommon.attach(grassMesh)

                    //var helper = new THREE.WireframeHelper(grassMesh, 0xff00ff)
                    //helper.material.linewidth = 1
                    //Scene.add(helper)
                })
            } else {
                grassMesh = new THREE.Mesh(grassGeometry, grassMaterial)
                grassMesh.rotation.x = Math.PI / 2
                if (Scene.getShadows()) {
                    grassMesh.receiveShadow = true
                }
                Scene.add(grassMesh)
                PickerCommon.attach(grassMesh)
            }
        },
        adjustMountainLevels = function (grassVertexPositions, maxX, maxY) {
            for (var i = 0; i < grassVertexPositions.length; i++) {
                if (grassVertexPositions[i][0] == 0) {
                    continue
                }
                if (grassVertexPositions[i][0] == maxX) {
                    continue
                }
                if (grassVertexPositions[i][1] == 0) {
                    continue
                }
                if (grassVertexPositions[i][1] == maxY) {
                    continue
                }
                if (grassVertexPositions[i][0] % 2 != 0 || grassVertexPositions[i][1] % 2 != 0) {
                    continue
                }
                if (i % 12 != 0) {
                    continue
                }
                if (Fields.get(grassVertexPositions[i][0] / 2, grassVertexPositions[i][1] / 2).getType() == 'm') {
                    var between = maxY * 6
                    if (grassVertexPositions[i][2] == 0) {
                        grassVertexPositions[i][2] = -hillLevel
                        grassVertexPositions[i - 2][2] = -hillLevel
                        grassVertexPositions[i - 4][2] = -hillLevel
                        grassVertexPositions[i - between - 3][2] = -hillLevel                //
                        grassVertexPositions[i - between + 1][2] = -hillLevel                //
                        grassVertexPositions[i - between + 5][2] = -hillLevel               //
                    }

                    if (grassVertexPositions[i + 2][2] == 0) {
                        grassVertexPositions[i + 2][2] = -hillLevel
                        grassVertexPositions[i + 4][2] = -hillLevel
                        grassVertexPositions[i + 6][2] = -hillLevel
                        grassVertexPositions[i - between + 3][2] = -hillLevel  //
                        grassVertexPositions[i - between + 7][2] = -hillLevel  //
                        grassVertexPositions[i - between + 11][2] = -hillLevel //
                    }

                    if (grassVertexPositions[i + 8][2] == 0) {
                        grassVertexPositions[i + 8][2] = -hillLevel
                        grassVertexPositions[i + 10][2] = -hillLevel
                        grassVertexPositions[i + 12][2] = -hillLevel
                        grassVertexPositions[i - between + 9][2] = -hillLevel  //
                        grassVertexPositions[i - between + 13][2] = -hillLevel  //
                        grassVertexPositions[i - between + 17][2] = -hillLevel //
                    }

                    if (grassVertexPositions[i + 1][2] == 0) {
                        grassVertexPositions[i - 3][2] = -hillLevel  //
                        grassVertexPositions[i + 1][2] = -hillLevel  //
                        grassVertexPositions[i + 5][2] = -hillLevel  //
                        grassVertexPositions[i + between][2] = -hillLevel  //
                        grassVertexPositions[i + between - 2][2] = -hillLevel
                        grassVertexPositions[i + between - 4][2] = -hillLevel
                    }

                    if (grassVertexPositions[i + 9][2] == 0) {
                        grassVertexPositions[i + 9][2] = -hillLevel  //
                        grassVertexPositions[i + 13][2] = -hillLevel  //
                        grassVertexPositions[i + 17][2] = -hillLevel  //
                        grassVertexPositions[i + between + 8][2] = -hillLevel  //
                        grassVertexPositions[i + between + 10][2] = -hillLevel
                        grassVertexPositions[i + between + 12][2] = -hillLevel
                    }

                    var between2 = 2 * between
                    if (grassVertexPositions[i + between + 1][2] == 0) {
                        grassVertexPositions[i + between - 3][2] = -hillLevel  //
                        grassVertexPositions[i + between + 1][2] = -hillLevel  //
                        grassVertexPositions[i + between + 5][2] = -hillLevel  //
                        grassVertexPositions[i + between2][2] = -hillLevel  //
                        grassVertexPositions[i + between2 - 2][2] = -hillLevel
                        grassVertexPositions[i + between2 - 4][2] = -hillLevel
                    }

                    if (grassVertexPositions[i + between + 3][2] == 0) {
                        grassVertexPositions[i + between + 3][2] = -hillLevel  //
                        grassVertexPositions[i + between + 7][2] = -hillLevel  //
                        grassVertexPositions[i + between + 11][2] = -hillLevel  //
                        grassVertexPositions[i + between2 + 2][2] = -hillLevel  //
                        grassVertexPositions[i + between2 + 4][2] = -hillLevel
                        grassVertexPositions[i + between2 + 6][2] = -hillLevel
                    }

                    if (grassVertexPositions[i + between + 9][2] == 0) {
                        grassVertexPositions[i + between + 9][2] = -hillLevel  //
                        grassVertexPositions[i + between + 13][2] = -hillLevel  //
                        grassVertexPositions[i + between + 17][2] = -hillLevel  //
                        grassVertexPositions[i + between2 + 8][2] = -hillLevel  //
                        grassVertexPositions[i + between2 + 10][2] = -hillLevel
                        grassVertexPositions[i + between2 + 12][2] = -hillLevel
                    }
                }
            }
        },
        changeGroundLevel = function (grassVertexPositions, maxX, maxY, maxI, i, level, type) {
            //if (type == 'w') {
            var rand = 0
            //var rand = -0.1
            //} else {
            //    var rand = -Math.random() / 5
            //}

            if (i % 12 == 0) {
                grassVertexPositions[i + 3][2] = level + rand                //
                grassVertexPositions[i + 7][2] = level + rand                //
                grassVertexPositions[i + 11][2] = level + rand               //
                var between = maxY * 6 + 6                                   //
                if (i + between < maxI) {                                    // center vertex of the field
                    grassVertexPositions[i + between][2] = level + rand      //
                    grassVertexPositions[i + between - 2][2] = level + rand  //
                    grassVertexPositions[i + between - 4][2] = level + rand  //
                }

                //rand = Math.random() / 2
                if ((i + 12) % (maxY * 6) != 0 && Fields.get(grassVertexPositions[i + 12][0] / 2, grassVertexPositions[i + 12][1] / 2).getTypeWithoutBridge() == type) {
                    grassVertexPositions[i + 9][2] = level - rand                //
                    grassVertexPositions[i + 13][2] = level - rand               //
                    grassVertexPositions[i + 17][2] = level - rand               //
                    var between = maxY * 6 + 12                                  //
                    if (i + between < maxI) {                                        // vertex between two centers od the field on Y axis
                        grassVertexPositions[i + between][2] = level - rand      //
                        grassVertexPositions[i - 2 + between][2] = level - rand  //
                        grassVertexPositions[i - 4 + between][2] = level - rand  //
                    }                                                            //
                }
                //
                //rand = Math.random() / 2
                var nextRow = maxY * 2 * 6
                if (i + nextRow < maxI && Fields.get(grassVertexPositions[i + nextRow][0] / 2, grassVertexPositions[i + nextRow][1] / 2).getTypeWithoutBridge() == type) {
                    grassVertexPositions[i + nextRow + 6][2] = level - rand  //
                    grassVertexPositions[i + nextRow + 4][2] = level - rand  //
                    grassVertexPositions[i + nextRow + 2][2] = level - rand  //
                    var between = maxY * 6                                   //
                    grassVertexPositions[i + between + 3][2] = level - rand  // vertex between two centers od the field on X axis
                    grassVertexPositions[i + between + 7][2] = level - rand  //
                    grassVertexPositions[i + between + 11][2] = level - rand //
                }                                                            //

                //rand = Math.random() / 2
                var nextVertex = nextRow + 12
                if (i + nextVertex < maxI && (i + nextVertex) % (maxY * 6) != 0 && Fields.get(grassVertexPositions[i + nextVertex][0] / 2, grassVertexPositions[i + nextVertex][1] / 2).getTypeWithoutBridge() == type) {
                    grassVertexPositions[i + nextVertex][2] = level - rand       //
                    grassVertexPositions[i + nextVertex - 2][2] = level - rand   //
                    grassVertexPositions[i + nextVertex - 4][2] = level - rand   //
                    var between = maxY * 6                                       //
                    grassVertexPositions[i + between + 9][2] = level - rand      // vertex between two centers od the field on X and Y axis
                    grassVertexPositions[i + between + 13][2] = level - rand     //
                    grassVertexPositions[i + between + 17][2] = level - rand     //
                }                                                                //
            }
            return grassVertexPositions
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
        var maxX = Fields.getMaxX(),
            maxY = Fields.getMaxY()
        //grassVertices = new Float32Array(grassVertexPositions.length * 3),
        //i = y * 12 + x * 24 * (maxY * 1 + 1),
        //maxI = maxX * maxY * 6
        //console.log(i)
        //switch (type) {
        //    case 'w':
        //        grassVertexPositions = changeGroundLevel(grassVertexPositions, maxX, maxY, maxI, i, bottomLevel, type)
        //        break
        //    case 'b':
        //        grassVertexPositions = changeGroundLevel(grassVertexPositions, maxX, maxY, maxI, i, bottomLevel, type)
        //        break
        //    case 'm':
        //        grassVertexPositions = changeGroundLevel(grassVertexPositions, maxX, maxY, maxI, i, -mountainLevel, type)
        //        break
        //    case 'h':
        //        grassVertexPositions = changeGroundLevel(grassVertexPositions, maxX, maxY, maxI, i, -hillLevel, type)
        //        break
        //}
        //
        //for (var i = 0; i < grassVertexPositions.length; i++) {
        //    var index = 3 * i
        //    grassVertices[index + 0] = grassVertexPositions[i][0]
        //    grassVertices[index + 1] = grassVertexPositions[i][1]
        //    grassVertices[index + 2] = grassVertexPositions[i][2]
        //}
        //grassGeometry.addAttribute('position', new THREE.BufferAttribute(grassVertices, 3))
        Scene.remove(grassMesh)
        //grassMesh = new THREE.Mesh(grassGeometry, grassMaterial)
        //grassMesh.rotation.x = Math.PI / 2
        //Scene.add(grassMesh)
        createGround(maxX * 2, maxY * 2)
    }
    this.init = function (maxX, maxY, textureName) {
        createGround(maxX * 2, maxY * 2, textureName)
        createWater(maxX * 2, maxY * 2)
        //createClouds(maxX * 2, maxY * 2)
    }
}
