if (!Detector.webgl) Detector.addGetWebGLMessage();

var Models = new function () {
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
        circles = [],
        armyCircles = [],
        loader = new THREE.JSONLoader(),
        createTextMesh = function (text, color) {
            var mesh = new THREE.Mesh(new THREE.TextGeometry(text, {
                size: 0.5,
                height: 0.1
            }), new THREE.MeshPhongMaterial({color: color}))
            mesh.position.set(0, 7, 0.2)
            mesh.rotation.y = -Math.PI / 4
            return mesh
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

        Scene.add(circle)
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
        Scene.add(cylinder)
        armyCircles.push(cylinder)

        var circle = new THREE.Mesh(geometry2, material2)
        circle.position.set(x * 4 - 216, height, y * 4 - 311)
        circle.rotation.x = Math.PI / 2
        Scene.add(circle)
        armyCircles.push(circle)
    }
    this.clearPathCircles = function () {
        for (var i in circles) {
            Scene.remove(circles[i])
        }
        circles = []
    }
    this.clearArmyCircles = function () {
        for (var i in armyCircles) {
            Scene.remove(armyCircles[i])
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
        Scene.add(mesh)

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

        //if (showShadows) {
        //    mesh.castShadow = true
        //    mesh.receiveShadow = true
        //}
        Scene.add(mesh)

        var material = new THREE.MeshLambertMaterial({color: color, side: THREE.DoubleSide})
        var flagMesh = new THREE.Mesh(flagModel.geometry, material)
        //if (showShadows) {
        //    flagMesh.castShadow = true
        //    flagMesh.receiveShadow = true
        //}
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
        initRuin()
        initTower()
        initCastle()
        initArmy()
        initFields()
    }
}
