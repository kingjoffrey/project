this.loadGround = function () {
    var xy = [],
        maxX = Fields.getMaxX(),
        maxY = Fields.getMaxY(),
        grassGeometry = new THREE.BufferGeometry(),
        waterGeometry = new THREE.BufferGeometry(),
        grassVertexPositions = [],
        waterVertexPositions = []

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
        if (Fields.get(grassVertexPositions[i][0], grassVertexPositions[i][1]).getType() == 'w') {
            grassVertexPositions[i][2] = 0.3
        }
        if (Fields.get(grassVertexPositions[i][0], grassVertexPositions[i][1]).getType() == 'b') {
            grassVertexPositions[i][2] = 0.3
        }
        if (Fields.get(grassVertexPositions[i][0], grassVertexPositions[i][1]).getType() == 'm') {
            grassVertexPositions[i][2] = -0.5
        }
        if (Fields.get(grassVertexPositions[i][0], grassVertexPositions[i][1]).getType() == 'h') {
            grassVertexPositions[i][2] = -0.3
        }
    }
    //console.log(grassVertexPositions)

    waterVertexPositions.push([0, 0, -0.2])
    waterVertexPositions.push([maxX, 0, -0.2])
    waterVertexPositions.push([0, maxY, -0.2])

    waterVertexPositions.push([maxX, maxY, -0.2])
    waterVertexPositions.push([0, maxY, -0.2])
    waterVertexPositions.push([maxX, 0, -0.2])

    var grassVertices = new Float32Array(grassVertexPositions.length * 3),
        normals = new Float32Array(grassVertexPositions.length * 3),
        colors = new Float32Array(grassVertexPositions.length * 3),
        uvs = new Float32Array(grassVertexPositions.length * 2),
        waterVertices = new Float32Array(waterVertexPositions.length * 3)

    for (var i = 0; i < grassVertexPositions.length; i++) {
        grassVertices[i * 3 + 0] = grassVertexPositions[i][0] * 4 - 215.6;
        grassVertices[i * 3 + 1] = grassVertexPositions[i][1] * 4 - 311.5;
        grassVertices[i * 3 + 2] = grassVertexPositions[i][2];
    }

    for (var i = 0; i < waterVertexPositions.length; i++) {
        waterVertices[i * 3 + 0] = waterVertexPositions[i][0] * 4 - 215;
        waterVertices[i * 3 + 1] = waterVertexPositions[i][1] * 4 - 308;
        waterVertices[i * 3 + 2] = waterVertexPositions[i][2];
    }

    grassGeometry.addAttribute('position', new THREE.BufferAttribute(grassVertices, 3))
    grassGeometry.addAttribute('normal', new THREE.BufferAttribute(normals, 3))
    grassGeometry.addAttribute('color', new THREE.BufferAttribute(colors, 3))
    grassGeometry.addAttribute('uv', new THREE.BufferAttribute(uvs, 2))
    waterGeometry.addAttribute('position', new THREE.BufferAttribute(waterVertices, 3))

    grassGeometry.computeVertexNormals()

    //var textureLoader = new THREE.TextureLoader();
    //textureLoader.load('/img/deska_0.png', function (texture) {
    //
    //var grassMaterial = new THREE.MeshLambertMaterial({map: texture, side: THREE.DoubleSide}),
    var grassMaterial = new THREE.MeshLambertMaterial({color: 0x567630, side: THREE.DoubleSide}),
        waterMaterial = new THREE.MeshBasicMaterial({color: 0x0000ff}),
        grassMesh = new THREE.Mesh(grassGeometry, grassMaterial),
        waterMesh = new THREE.Mesh(waterGeometry, waterMaterial)

    grassMesh.rotation.x = Math.PI / 2;
    waterMesh.rotation.x = -Math.PI / 2;

    scene.add(grassMesh);
    scene.add(waterMesh);

    Picker.attach(grassMesh)
    //});


    //var ground = new THREE.Mesh(new THREE.PlaneBufferGeometry(Fields.getMaxX() * 4, Fields.getMaxY() * 4), new THREE.MeshLambertMaterial({map: THREE.ImageUtils.loadTexture('/img/maps/1.png')}));
    //var ground = new THREE.Mesh(new THREE.PlaneBufferGeometry(436, 624), new THREE.MeshLambertMaterial({color: 0x00dd00}));
    //ground.rotation.x = -Math.PI / 2
    //if (showShadows) {
    //    ground.receiveShadow = true
    //}
    //scene.add(ground)
    //Picker.attach(ground)
}

var Ground = new function (maxX, maxY, textureName) {
    this.init = function () {
        createWater()
        createGround()
    }
    var createWater = function () {
        var waterVertexPositions = [],
            waterVertices = new Float32Array(18),
            waterGeometry = new THREE.BufferGeometry(),
            waterMaterial = new THREE.MeshBasicMaterial({color: 0x0000ff})

        waterVertexPositions.push([0, 0, -0.2])
        waterVertexPositions.push([maxX, 0, -0.2])
        waterVertexPositions.push([0, maxY, -0.2])

        waterVertexPositions.push([maxX, maxY, -0.2])
        waterVertexPositions.push([0, maxY, -0.2])
        waterVertexPositions.push([maxX, 0, -0.2])

        for (var i = 0; i < 18; i++) {
            waterVertices[i * 3 + 0] = waterVertexPositions[i][0] * 4 - 215;
            waterVertices[i * 3 + 1] = waterVertexPositions[i][1] * 4 - 308;
            waterVertices[i * 3 + 2] = waterVertexPositions[i][2];
        }

        waterGeometry.addAttribute('position', new THREE.BufferAttribute(waterVertices, 3))

        Three.scene.add(new THREE.Mesh(waterGeometry, waterMaterial))
    }
    var createGround = function () {
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
            grassVertexPositions.push([xy[i][0], xy[i][1], 0])
            grassVertexPositions.push([xy[i][0] + 1, xy[i][1], 0])
            grassVertexPositions.push([xy[i][0], xy[i][1] + 1, 0])

            grassVertexPositions.push([xy[i][0] + 1, xy[i][1] + 1, 0])
            grassVertexPositions.push([xy[i][0], xy[i][1] + 1, 0])
            grassVertexPositions.push([xy[i][0] + 1, xy[i][1], 0])
        }
        for (var i = 0; i < grassVertexPositions.length; i++) {
            if (Fields.get(grassVertexPositions[i][0], grassVertexPositions[i][1]).getType() == 'w') {
                grassVertexPositions[i][2] = 0.3
            }
            if (Fields.get(grassVertexPositions[i][0], grassVertexPositions[i][1]).getType() == 'b') {
                grassVertexPositions[i][2] = 0.3
            }
            if (Fields.get(grassVertexPositions[i][0], grassVertexPositions[i][1]).getType() == 'm') {
                grassVertexPositions[i][2] = -0.5
            }
            if (Fields.get(grassVertexPositions[i][0], grassVertexPositions[i][1]).getType() == 'h') {
                grassVertexPositions[i][2] = -0.3
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

