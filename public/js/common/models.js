var Models = new function () {
    var ruinModel,
        towerModel,
        flagModel,
        armyModels = {},
        castleModels = {},
        flagModels = {},
        mountainModel,
        hillModel,
        treeModel,
        waterModel,
        shadows,
        roadTexture,
        swampTexture,
        circles = [],
        armyCircles = [],
        loader = new THREE.JSONLoader(),
        tl = new THREE.TextureLoader(),
        loading = 0,
        createTextMesh = function (text, color) {
            var mesh = new THREE.Mesh(new THREE.TextGeometry(text, {
                size: 0.25,
                height: 0.05
            }), new THREE.MeshPhongMaterial({color: color}))
            mesh.position.set(0, 2, 0.2)
            mesh.rotation.y = -Math.PI / 4
            return mesh
        },
        initRoadTexture = function () {
            roadTexture = {}
            tl.load('/img/game/road/road_c.png', function (tex) {
                roadTexture.c = tex
                loading++
            })
            tl.load('/img/game/road/road_d1.png', function (tex) {
                roadTexture.d1 = tex
                loading++
            })
            tl.load('/img/game/road/road_d3.png', function (tex) {
                roadTexture.d3 = tex
                loading++
            })
            tl.load('/img/game/road/road_h.png', function (tex) {
                roadTexture.h = tex
                loading++
            })
            tl.load('/img/game/road/road_l1.png', function (tex) {
                roadTexture.l1 = tex
                loading++
            })
            tl.load('/img/game/road/road_l3.png', function (tex) {
                roadTexture.l3 = tex
                loading++
            })
            tl.load('/img/game/road/road_ld.png', function (tex) {
                roadTexture.ld = tex
                loading++
            })
            tl.load('/img/game/road/road_lu.png', function (tex) {
                roadTexture.lu = tex
                loading++
            })
            tl.load('/img/game/road/road_p.png', function (tex) {
                roadTexture.p = tex
                loading++
            })
            tl.load('/img/game/road/road_r1.png', function (tex) {
                roadTexture.r1 = tex
                loading++
            })
            tl.load('/img/game/road/road_r3.png', function (tex) {
                roadTexture.r3 = tex
                loading++
            })
            tl.load('/img/game/road/road_rd.png', function (tex) {
                roadTexture.rd = tex
                loading++
            })
            tl.load('/img/game/road/road_ru.png', function (tex) {
                roadTexture.ru = tex
                loading++
            })
            tl.load('/img/game/road/road_u1.png', function (tex) {
                roadTexture.u1 = tex
                loading++
            })
            tl.load('/img/game/road/road_u3.png', function (tex) {
                roadTexture.u3 = tex
                loading++
            })
            tl.load('/img/game/road/road_v.png', function (tex) {
                roadTexture.v = tex
                loading++
            })
        },
        initSwampTexture = function () {
            swampTexture
            tl.load('/img/game/swamp.png', function (tex) {
                swampTexture = tex
                loading++
            })
        },
        initRuin = function () {
            ruin.scale = 6
            ruinModel = loader.parse(ruin)
        },
        initTower = function () {
            tower.scale = 2.4
            towerModel = loader.parse(tower)
        },
        initCastle = function () {
            castle_1.scale = 3.8
            castle_2.scale = 3.8
            castle_3.scale = 3.8
            castle_4.scale = 3.8
            castleModels = {
                0: loader.parse(castle_1),
                1: loader.parse(castle_2),
                2: loader.parse(castle_3),
                3: loader.parse(castle_4)
            }
        },
        initFlag = function () {
            flag.scale = 0.6
            flagModel = loader.parse(flag)
        },
        getCastleModel = function (defense) {
            switch (defense) {
                case 2:
                    return new THREE.Mesh(castleModels[1].geometry, new THREE.MeshLambertMaterial({
                        color: '#65402C',
                        side: THREE.DoubleSide
                    }))
                case 3:
                    return new THREE.Mesh(castleModels[2].geometry, new THREE.MeshPhongMaterial({
                        color: '#0054C4',
                        side: THREE.DoubleSide
                    }))
                case 4:
                    return new THREE.Mesh(castleModels[3].geometry, new THREE.MeshLambertMaterial({
                        color: '#6B6B6B',
                        side: THREE.DoubleSide
                    }))
            }
        },
        updateCastleModel = function (mesh, defense) {
            for (var i = 2; i <= defense; i++) {
                var m = getCastleModel(i)

                if (shadows) {
                    m.castShadow = true
                    m.receiveShadow = true
                }
                mesh.add(m)
            }
        },
        initArmy = function () {
            armyModels = {
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
                armyModels[i].scale = 16
                window[i + 'Model'] = loader.parse(armyModels[i])
            }

            flag_1.scale = 15
            flagModels[0] = loader.parse(flag_1)
            flag_2.scale = 15
            flagModels[1] = loader.parse(flag_2)
            flag_3.scale = 15
            flagModels[2] = loader.parse(flag_3)
            flag_4.scale = 15
            flagModels[3] = loader.parse(flag_4)
            flag_5.scale = 15
            flagModels[4] = loader.parse(flag_5)
            flag_6.scale = 15
            flagModels[5] = loader.parse(flag_6)
            flag_7.scale = 15
            flagModels[6] = loader.parse(flag_7)
            flag_8.scale = 15
            flagModels[7] = loader.parse(flag_8)
        },
        initTree = function () {
            tree.scale = 6
            treeModel = loader.parse(tree)
            treeModel.material = new THREE.MeshLambertMaterial({color: '#003300', side: THREE.DoubleSide})
        }

    this.addPathCircle = function (x, y, color, t) {
        var radius = 1,
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
                var height = Ground.getMountainLevel() + 0.01
                break
            case 'h':
                var height = Ground.getHillLevel() + 0.01
                break
            default :
                var height = 0.01
                break
        }
        circle.position.set(x * 2 + 1, height, y * 2 + 1)
        circle.rotation.x = Math.PI / 2

        Scene.add(circle)
        circles.push(circle)
    }
    this.addArmyCircle = function (x, y, color) {
        var radius = 1,
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
            geometry1 = new THREE.CylinderGeometry(0.5, 0, 1, segments, segments, 1),
            geometry2 = new THREE.CircleGeometry(radius, segments)
        //geometry = new THREE.TorusGeometry(radius, 0.3, segments, segments)

        switch (Fields.get(x, y).getType()) {
            case 'm':
                var height = 1.5
                break
            case 'h':
                var height = 0.5
                break
            default :
                var height = 0.1
                break
        }
        var cylinder = new THREE.Mesh(geometry1, material1)
        cylinder.position.set(x * 2 + 1, 2 + height, y * 2 + 1)
        //cylinder.rotation.x = Math.PI / 2
        Scene.add(cylinder)
        armyCircles.push(cylinder)

        var circle = new THREE.Mesh(geometry2, material2)
        circle.position.set(x * 2 + 1, height, y * 2 + 1)
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
    this.addRuin = function (x, y, color) {
        var mesh = new THREE.Mesh(ruinModel.geometry, new THREE.MeshPhongMaterial({
            color: color,
            side: THREE.DoubleSide
        }))
        if (shadows) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }
        mesh.position.set(x * 2 + 0.5, 0, y * 2 + 1)
        mesh.rotation.y = 2 * Math.PI * Math.random()
        Scene.add(mesh)
        return mesh
    }
    this.addTower = function (x, y, color) {
        var mesh = new THREE.Mesh(towerModel.geometry, new THREE.MeshLambertMaterial({
                color: '#6B6B6B',
                side: THREE.DoubleSide
            })),
            flagMesh = new THREE.Mesh(flagModel.geometry, new THREE.MeshLambertMaterial({
                color: color,
                side: THREE.DoubleSide
            }))
        if (shadows) {
            mesh.castShadow = true
            mesh.receiveShadow = true
            flagMesh.castShadow = true
            flagMesh.receiveShadow = true
        }
        mesh.add(flagMesh)
        mesh.position.set(x * 2 + 1.5, 0, y * 2 + 0.5)
        Scene.add(mesh)
        return mesh
    }
    this.addCastle = function (castle, color) {
        var mesh = new THREE.Mesh(castleModels[0].geometry, new THREE.MeshLambertMaterial({
                color: '#3B3028',
                side: THREE.DoubleSide
            })),
            flagMesh = new THREE.Mesh(flagModel.geometry, new THREE.MeshLambertMaterial({
                color: color,
                side: THREE.DoubleSide
            }))
        if (shadows) {
            mesh.castShadow = true
            mesh.receiveShadow = true
            flagMesh.castShadow = true
            flagMesh.receiveShadow = true
        }
        mesh.position.set(castle.x * 2 + 2, 0, castle.y * 2 + 2)
        mesh.add(flagMesh)
        mesh.add(createTextMesh(castle.name, '#ffffff'))
        updateCastleModel(mesh, castle.defense)
        Scene.add(mesh)
        return mesh
    }
    this.castleChangeDefense = function (mesh, defense) {
        mesh.children.splice(2, 3)
        updateCastleModel(mesh, defense)
    }
    this.addTree = function (x, y) {
        var mesh = new THREE.Mesh(treeModel.geometry, treeModel.material)
        mesh.position.set(x * 2 + 1, 0, y * 2 + 1)
        mesh.rotation.y = 2 * Math.PI * Math.random()

        if (shadows) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }
        Scene.add(mesh)
    }
    this.addRoad = function (x, y) {
        var x = x * 1,
            y = y * 1,
            f1, f2, f3, f4

        if (y - 1 >= 0) {
            f1 = Fields.get(x, y - 1).getType()
        }
        if (x + 1 <= Fields.getMaxY()) {
            f2 = Fields.get(x + 1, y).getType()
        }
        if (y + 1 <= Fields.getMaxX()) {
            f3 = Fields.get(x, y + 1).getType()
        }
        if (x - 1 >= 0) {
            f4 = Fields.get(x - 1, y).getType()
        }

        if (f1 != 'r' && f2 != 'r' && f3 != 'r' && f4 != 'r') { // point
            var map = roadTexture.p
        } else if (f1 == 'r' && f2 != 'r' && f3 != 'r' && f4 != 'r') { // 3
            var map = roadTexture.u3
        } else if (f1 != 'r' && f2 == 'r' && f3 != 'r' && f4 != 'r') { // 3
            var map = roadTexture.l3
        } else if (f1 != 'r' && f2 != 'r' && f3 == 'r' && f4 != 'r') { // 3
            var map = roadTexture.d3
        } else if (f1 != 'r' && f2 != 'r' && f3 != 'r' && f4 == 'r') { // 3
            var map = roadTexture.r3
        } else if (f1 == 'r' && f2 != 'r' && f3 == 'r' && f4 != 'r') { // vertical
            var map = roadTexture.v
        } else if (f1 != 'r' && f2 == 'r' && f3 != 'r' && f4 == 'r') { // horizontal
            var map = roadTexture.h
        } else if (f1 == 'r' && f2 == 'r' && f3 == 'r' && f4 == 'r') { // center
            var map = roadTexture.c
        } else if (f1 != 'r' && f2 == 'r' && f3 == 'r' && f4 != 'r') {
            var map = roadTexture.ld
        } else if (f1 != 'r' && f2 != 'r' && f3 == 'r' && f4 == 'r') {
            var map = roadTexture.rd
        } else if (f1 == 'r' && f2 == 'r' && f3 != 'r' && f4 != 'r') {
            var map = roadTexture.lu
        } else if (f1 == 'r' && f2 != 'r' && f3 != 'r' && f4 == 'r') {
            var map = roadTexture.ru
        } else if (f1 == 'r' && f2 == 'r' && f3 != 'r' && f4 == 'r') { // 1
            var map = roadTexture.u1
        } else if (f1 == 'r' && f2 == 'r' && f3 == 'r' && f4 != 'r') { // 1
            var map = roadTexture.l1
        } else if (f1 != 'r' && f2 == 'r' && f3 == 'r' && f4 == 'r') { // 1
            var map = roadTexture.d1
        } else if (f1 == 'r' && f2 != 'r' && f3 == 'r' && f4 == 'r') { // 1
            var map = roadTexture.r1
        }

        var roadMaterial = new THREE.MeshLambertMaterial({
                map: map,
                side: THREE.DoubleSide,
                transparent: true,
                opacity: 0.7
            }),
            mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), roadMaterial)
        mesh.rotation.x = Math.PI / 2
        mesh.position.set(x * 2 + 1, 0.01, y * 2 + 1)

        if (shadows) {
            mesh.receiveShadow = true
        }
        Scene.add(mesh)
    }
    this.addSwamp = function (x, y) {
        var swampMaterial = new THREE.MeshLambertMaterial({
                map: swampTexture,
                side: THREE.DoubleSide,
                transparent: true,
                opacity: 0.5
            }),
            mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), swampMaterial)
        mesh.rotation.x = Math.PI / 2
        mesh.position.set(x * 2 + 1, 0.01, y * 2 + 1)

        if (shadows) {
            mesh.receiveShadow = true
        }
        Scene.add(mesh)
    }
    this.addArmy = function (x, y, color, number, modelName) {
        var armyMaterial = new THREE.MeshLambertMaterial({color: color, side: THREE.DoubleSide}),
            flagMesh = new THREE.Mesh(flagModels[number - 1].geometry, new THREE.MeshLambertMaterial({
                color: color,
                side: THREE.DoubleSide
            }))
        if (modelName + 'Model' in window) {
            var mesh = new THREE.Mesh(window[modelName + 'Model'].geometry, armyMaterial)
        } else {
            var mesh = new THREE.Mesh(untitledModel.geometry, armyMaterial)
        }
        switch (Fields.get(x, y).getType()) {
            case 'm':
                var height = Ground.getMountainLevel()
                break
            case 'h':
                var height = Ground.getHillLevel()
                break
            case 'w':
                var height = Ground.getWaterLevel()
                break
            default :
                var height = 0
                break
        }
        if (shadows) {
            mesh.castShadow = true
            mesh.receiveShadow = true
            flagMesh.castShadow = true
        }
        flagMesh.position.set(-0.2, 0, 0)
        mesh.position.set(x * 2 + 0.5, height, y * 2 + 0.5)
        mesh.rotation.y = Math.PI / 2 + Math.PI / 4
        mesh.add(flagMesh)
        Scene.add(mesh)
        return mesh
    }
    this.getCastleModels = function () {
        return castleModels
    }
    this.getRuinModel = function () {
        return ruinModel
    }
    this.getTowerModel = function () {
        return towerModel
    }
    this.getTreeModel = function () {
        return treeModel
    }
    this.getRoadTexture = function () {
        return roadTexture
    }
    this.getSwampTexture = function () {
        return swampTexture
    }
    this.getLoading = function () {
        return loading
    }
    this.init = function () {
        shadows = Scene.getShadows()
        initRoadTexture()
        initSwampTexture()
        initRuin()
        initTower()
        initArmy()
        initCastle()
        initFlag()
        initTree()
    }
}
