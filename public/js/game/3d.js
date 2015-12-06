var Three = new function () {
    var ruinModel,
        towerModel,
        flagModel,
        castleModel_1,
        castleModel_2,
        castleModel_3,
        castleModel_4,
        armyModel,
        mountainModel,
        hillModel,
        treeModel,
        waterModel,
        minHeight = 665,
        minWidth = 950,
        gameWidth,
        gameHeight,
        scene = new THREE.Scene(),
        camera,
        renderer = new THREE.WebGLRenderer({antialias: true}),
        pointLight = new THREE.PointLight(0xdddddd),
        theLight = new THREE.DirectionalLight(0xffffff, 1),
        loader = new THREE.JSONLoader(),
        circles = [],
        armyCircles = [],
        showShadows = 0,
        cameraY = 76,
        timeOut = 100,
        createTextMesh = function (text, color) {
            var mesh = new THREE.Mesh(new THREE.TextGeometry(text, {
                size: 0.5,
                height: 0.1
            }), new THREE.MeshPhongMaterial({color: color}))
            mesh.position.set(0, 7, 0.2)
            mesh.rotation.y = -Math.PI / 4
            return mesh
        },
        animate = function () {
            if (TWEEN.update()) {
                requestAnimationFrame(animate)
                renderer.render(scene, camera)
            } else {
                renderer.render(scene, camera)
                setTimeout(function () {
                    requestAnimationFrame(animate)
                }, timeOut)
            }
        }

    if (showShadows) {
        renderer.shadowMapEnabled = true
        renderer.shadowMapSoft = false
    }

    pointLight.position.set(-100000, 100000, 100000);
    scene.add(pointLight)

    theLight.position.set(1500, 1000, 1000)
    if (showShadows) {
        theLight.castShadow = true
        theLight.shadowDarkness = 0.5
        theLight.shadowMapWidth = 8192
        theLight.shadowMapHeight = 8192
        //theLight.shadowCameraVisible = true
        //theLight.shadowCameraRight = 150;
        //theLight.shadowCameraLeft = -150;
        //theLight.shadowCameraTop = 150;
        //theLight.shadowCameraBottom = -150;
    }
    scene.add(theLight);

    this.getCameraY = function () {
        return cameraY
    }
    this.getScene = function () {
        return scene
    }
    this.getCamera = function () {
        return camera
    }
    this.getRenderer = function () {
        return renderer
    }
    this.addPathCircle = function (x, y, color, t) {
        var radius = 2,
            segments = 64,
            material = new THREE.MeshBasicMaterial({
                color: color,
                transparent: true,
                opacity: 0.5,
                side: THREE.DoubleSide
            }),
            geometry = new THREE.CircleGeometry(radius, segments)

        var circle = new THREE.Mesh(geometry, material)

        switch (t) {
            case 'm':
                var height = 3
                break
            case 'h':
                var height = 1
                break
            default :
                var height = 0.1
                break
        }
        circle.position.set(x * 4 - 216, height, y * 4 - 311)
        circle.rotation.x = Math.PI / 2

        scene.add(circle)
        circles.push(circle)
    }
    this.addArmyCircle = function (x, y, color) {
        var radius = 2,
            segments = 64,
            material1 = new THREE.MeshBasicMaterial({
                color: 'gold',
                transparent: true,
                opacity: 0.7,
                side: THREE.DoubleSide
            }),
            material2 = new THREE.MeshBasicMaterial({
                color: 0xffffff,
                transparent: true,
                opacity: 0.7,
                side: THREE.DoubleSide
            }),
            geometry1 = new THREE.CylinderGeometry(1, 0, 4, segments, segments, 1),
            geometry2 = new THREE.CircleGeometry(radius, segments)
        //geometry = new THREE.TorusGeometry(radius, 0.3, segments, segments)

        switch (Fields.get(x, y).getType()) {
            case 'm':
                var height = 3
                break
            case 'h':
                var height = 1
                break
            default :
                var height = 0.1
                break
        }
        var cylinder = new THREE.Mesh(geometry1, material1)
        cylinder.position.set(x * 4 - 216, 7 + height, y * 4 - 311)
        //cylinder.rotation.x = Math.PI / 2
        scene.add(cylinder)
        armyCircles.push(cylinder)

        var circle = new THREE.Mesh(geometry2, material2)
        circle.position.set(x * 4 - 216, height, y * 4 - 311)
        circle.rotation.x = Math.PI / 2
        scene.add(circle)
        armyCircles.push(circle)
    }
    this.clearPathCircles = function () {
        for (var i in circles) {
            scene.remove(circles[i])
        }
        circles = []
    }
    this.clearArmyCircles = function () {
        for (var i in armyCircles) {
            scene.remove(armyCircles[i])
        }
        armyCircles = []
    }

    var initRuin = function () {
        ruin.scale = 3
        ruinModel = loader.parse(ruin)
    }
    this.addRuin = function (x, y, color) {
        var ruinMaterial = new THREE.MeshPhongMaterial({color: color, side: THREE.DoubleSide})
        var mesh = new THREE.Mesh(ruinModel.geometry, ruinMaterial)
        mesh.position.set(x * 4 - 216, 0, y * 4 - 311)

        if (showShadows) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }
        scene.add(mesh)

        return mesh.id
    }

    var initTower = function () {
        tower.scale = 1.2
        towerModel = loader.parse(tower)
        flagModel = loader.parse(flag)
    }
    this.addTower = function (x, y, color) {
        var towerMaterial = new THREE.MeshLambertMaterial({color: '#6B6B6B', side: THREE.DoubleSide})

        var mesh = new THREE.Mesh(towerModel.geometry, towerMaterial)
        mesh.position.set(x * 4 - 216, 0, y * 4 - 311)

        if (showShadows) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }
        scene.add(mesh)

        var material = new THREE.MeshLambertMaterial({color: color, side: THREE.DoubleSide})
        var flagMesh = new THREE.Mesh(flagModel.geometry, material)
        if (showShadows) {
            flagMesh.castShadow = true
            flagMesh.receiveShadow = true
        }
        mesh.add(flagMesh)

        return mesh
    }

    var initCastle = function () {
            castle_1.scale = 1.9
            castle_2.scale = 1.9
            castle_3.scale = 1.9
            castle_4.scale = 1.9
            castleModel_1 = loader.parse(castle_1)
            castleModel_2 = loader.parse(castle_2)
            castleModel_3 = loader.parse(castle_3)
            castleModel_4 = loader.parse(castle_4)
            flag.scale = 0.3
            flagModel = loader.parse(flag)
        },
        getCastleModel = function (defense) {
            switch (defense) {
                case 2:
                    var material = new THREE.MeshLambertMaterial({color: '#65402C', side: THREE.DoubleSide})
                    return new THREE.Mesh(castleModel_2.geometry, material)
                case 3:
                    var material = new THREE.MeshPhongMaterial({color: '#0054C4', side: THREE.DoubleSide})
                    return new THREE.Mesh(castleModel_3.geometry, material)
                case 4:
                    var material = new THREE.MeshLambertMaterial({color: '#6B6B6B', side: THREE.DoubleSide})
                    return new THREE.Mesh(castleModel_4.geometry, material)
            }
        },
        updateCastleModel = function (mesh, defense) {
            for (var i = 2; i <= defense; i++) {
                var m = getCastleModel(i)

                if (showShadows) {
                    m.castShadow = true
                    m.receiveShadow = true
                }
                mesh.add(m)
            }
        }


    this.addCastle = function (castle, color) {
        //castle.x, castle.y, bgC, castle.defense
        var castleMaterial = new THREE.MeshLambertMaterial({color: '#3B3028', side: THREE.DoubleSide})

        var mesh = new THREE.Mesh(castleModel_1.geometry, castleMaterial)
        mesh.position.set(castle.x * 4 - 214, 0, castle.y * 4 - 308)

        if (showShadows) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }
        scene.add(mesh)

        var material = new THREE.MeshLambertMaterial({color: color, side: THREE.DoubleSide})
        var flagMesh = new THREE.Mesh(flagModel.geometry, material)
        if (showShadows) {
            flagMesh.castShadow = true
            flagMesh.receiveShadow = true
        }
        mesh.add(flagMesh)

        mesh.add(createTextMesh(castle.name, '#ffffff'))

        updateCastleModel(mesh, castle.defense)
        return mesh
    }
    this.castleChangeDefense = function (mesh, defense) {
        mesh.children.splice(2, 3)
        updateCastleModel(mesh, defense)
    }

    var initArmy = function () {
        var armyModels = {
            'untitled': untitled,
            'archers': archers,
            'hero': hero,
            'light_infantry': light_infantry,
            'heavy_infantry': heavy_infantry,
            'giants': giants,
            'dwarves': dwarves,
            'griffins': griffins,
            'dragon': dragon,
            'cavalry': cavalry,
            'navy': navy,
            'wolves': wolves,
            'undead': undead,
            'wizard': wizard,
            'devil': devil,
            'demon': demon,
            'pegasi': pegasi
        }
        for (var i in armyModels) {
            armyModels[i].scale = 6
            window[i + 'Model'] = loader.parse(armyModels[i])
        }

        flag_1.scale = 5
        flag_1Model = loader.parse(flag_1)
        flag_2.scale = 5
        flag_2Model = loader.parse(flag_2)
        flag_3.scale = 5
        flag_3Model = loader.parse(flag_3)
        flag_4.scale = 5
        flag_4Model = loader.parse(flag_4)
        flag_5.scale = 5
        flag_5Model = loader.parse(flag_5)
        flag_6.scale = 5
        flag_6Model = loader.parse(flag_6)
        flag_7.scale = 5
        flag_7Model = loader.parse(flag_7)
        flag_8.scale = 5
        flag_8Model = loader.parse(flag_8)
    }
    this.addArmy = function (x, y, color, number, modelName) {
        var material = new THREE.MeshLambertMaterial({color: color, side: THREE.DoubleSide})

        var armyMaterial = new THREE.MeshLambertMaterial({color: color, side: THREE.DoubleSide})

        if (modelName + 'Model' in window) {
            var mesh = new THREE.Mesh(window[modelName + 'Model'].geometry, armyMaterial)
        } else {
            var mesh = new THREE.Mesh(untitledModel.geometry, armyMaterial)
        }

        switch (Fields.get(x, y).getType()) {
            case 'm':
                var height = 3
                break
            case 'h':
                var height = 1
                break
            default :
                var height = 0
                break
        }

        mesh.position.set(x * 4 - 216, height, y * 4 - 311)
        mesh.rotation.y = Math.PI / 2 + Math.PI / 4

        if (showShadows) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }
        var flagMesh = new THREE.Mesh(getFlag(number).geometry, material)
        if (showShadows) {
            flagMesh.castShadow = true
        }
        flagMesh.position.set(-2, 0, 0)
        mesh.add(flagMesh)

        scene.add(mesh)

        return mesh
    }
    var getFlag = function (number) {
        switch (number) {
            case 1:
                return flag_1Model
            case 2:
                return flag_2Model
            case 3:
                return flag_3Model
            case 4:
                return flag_4Model
            case 5:
                return flag_5Model
            case 6:
                return flag_6Model
            case 7:
                return flag_7Model
            default :
                return flag_8Model
        }
    }

    var initFields = function () {
        tree.scale = 3
        hill.scale = 1.3

        mountainModel = loader.parse(mountain)
        hillModel = loader.parse(hill)
        treeModel = loader.parse(tree)

        mountainModel.material = new THREE.MeshLambertMaterial({color: '#4e5a61', side: THREE.DoubleSide})
        hillModel.material = new THREE.MeshLambertMaterial({color: '#415824', side: THREE.DoubleSide})
        treeModel.material = new THREE.MeshLambertMaterial({color: '#003300', side: THREE.DoubleSide})
        //waterModel.material = new THREE.MeshPhongMaterial({color: 0x0000ff, side: THREE.DoubleSide})
    }

    this.addMountain = function (x, y) {
        var mesh = new THREE.Mesh(mountainModel.geometry, mountainModel.material)
        mesh.position.set(x * 4 - 216, 0, y * 4 - 311)
        mesh.rotation.y = 2 * Math.PI * Math.random()

        if (showShadows) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }
        scene.add(mesh)

    }
    this.addHill = function (x, y) {
        var mesh = new THREE.Mesh(hillModel.geometry, hillModel.material)
        mesh.position.set(x * 4 - 216, 0, y * 4 - 311)
        mesh.rotation.y = 2 * Math.PI * Math.random()

        if (showShadows) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }
        scene.add(mesh)
    }
    this.addTree = function (x, y) {
        var mesh = new THREE.Mesh(treeModel.geometry, treeModel.material)
        mesh.position.set(x * 4 - 216, 0, y * 4 - 311)
        mesh.rotation.y = 2 * Math.PI * Math.random()

        if (showShadows) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }
        scene.add(mesh)
    }
    this.addWater = function (x, y) {
        var mesh = new THREE.Mesh(waterModel.geometry, waterModel.material)
        mesh.position.set(x * 4 - 216, 0.1, y * 4 - 311)
        mesh.rotation.y = 2 * Math.PI * Math.random()

        scene.add(mesh)
    }

    this.init = function () {
        gameWidth = $('body').innerWidth()
        gameHeight = $('body').innerHeight()
        if (gameWidth < minWidth) {
            gameWidth = minWidth
        }
        if (gameHeight < minHeight) {
            gameHeight = minHeight
        }

        $('#game')
            .append(renderer.domElement)
            .css({
                    width: gameWidth + 'px',
                    height: gameHeight + 'px'
                }
            )

        camera = new THREE.PerspectiveCamera(22, gameWidth / gameHeight, 1, 1000)
        camera.rotation.order = 'YXZ'
        camera.rotation.y = -Math.PI / 4
        camera.rotation.x = Math.atan(-1 / Math.sqrt(2))
        camera.position.set(0, cameraY, 0)
        camera.scale.addScalar(1)
        renderer.setSize(gameWidth, gameHeight)

        initRuin()
        initTower()
        initCastle()
        initArmy()
        initFields()
        animate()
    }
    this.resize = function () {
        gameWidth = $(window).innerWidth()
        gameHeight = $(window).innerHeight()
        if (gameWidth < minWidth) {
            gameWidth = minWidth
        }
        if (gameHeight < minHeight) {
            gameHeight = minHeight
        }
        $('#game')
            .css({
                    width: gameWidth + 'px',
                    height: gameHeight + 'px'
                }
            )
        renderer.setSize(gameWidth, gameHeight)
        camera.aspect = gameWidth / gameHeight
        camera.updateProjectionMatrix()
    }
    this.getWidth = function () {
        return gameWidth
    }
    this.getHeight = function () {
        return gameHeight
    }
    this.setFPS = function (fps) {
        timeOut = parseInt(1000 / fps)
    }

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
        waterGeometry.addAttribute('position', new THREE.BufferAttribute(waterVertices, 3))

        var textureLoader = new THREE.TextureLoader();
        textureLoader.load('/img/deska_0.png', function (texture) {

            var grassMaterial = new THREE.MeshBasicMaterial({map: texture, side: THREE.DoubleSide}),
                waterMaterial = new THREE.MeshBasicMaterial({color: 0x0000ff}),
                grassMesh = new THREE.Mesh(grassGeometry, grassMaterial),
                waterMesh = new THREE.Mesh(waterGeometry, waterMaterial)

            grassMesh.rotation.x = Math.PI / 2;
            waterMesh.rotation.x = -Math.PI / 2;

            scene.add(grassMesh);
            scene.add(waterMesh);

            Picker.attach(grassMesh)
        });


        //var ground = new THREE.Mesh(new THREE.PlaneBufferGeometry(Fields.getMaxX() * 4, Fields.getMaxY() * 4), new THREE.MeshLambertMaterial({map: THREE.ImageUtils.loadTexture('/img/maps/1.png')}));
        //var ground = new THREE.Mesh(new THREE.PlaneBufferGeometry(436, 624), new THREE.MeshLambertMaterial({color: 0x00dd00}));
        //ground.rotation.x = -Math.PI / 2
        //if (showShadows) {
        //    ground.receiveShadow = true
        //}
        //scene.add(ground)
        //Picker.attach(ground)
    }
}
