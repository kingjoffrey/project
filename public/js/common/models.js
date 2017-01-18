var Models = new function () {
    var ruinModel,
        towerModel,
        flagModel,
        armyModels = {},
        castleModels = {},
        flagModels = {},
        treeModel,
        shadows = 1,
        roadTexture,
        swampTexture,
        loader = new THREE.JSONLoader(),
        tl = new THREE.TextureLoader(),
        loading = 0,
        pathMaterialGreen,
        pathMaterialRed,
        pathMaterialWhite,
        pathGeometry,
        font,
        loadFont = function () {
            var loader = new THREE.FontLoader(),
                fontName = 'helvetiker',
                fontWeight = 'regular'

            loader.load(window.location.origin + '/fonts/' + fontName + '_' + fontWeight + '.typeface.json', function (response) {
                font = response;
            })
        },
        createTextMesh = function (text, color) {
            var mesh = new THREE.Mesh(new THREE.TextGeometry(text, {
                font: font,
                size: 1.5,
                height: 0.3
            }), new THREE.MeshPhongMaterial({color: color}))
            mesh.position.set(0, 10, 0.2)
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
            ruinModel = loader.parse(ruin)
        },
        initTower = function () {
            towerModel = loader.parse(tower)
        },
        initCastle = function () {
            castleModels = {
                0: loader.parse(castle_1),
                1: loader.parse(castle_2),
                2: loader.parse(castle_3),
                3: loader.parse(castle_4)
            }
        },
        initFlag = function () {
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
                window[i + 'Model'] = loader.parse(armyModels[i])
                if (i == 'hero') {
                    tl.load(window.location.origin + '/img/modelMaps/hero.png', function (texture) {
                        window['heroModel'].material = new THREE.MeshLambertMaterial({
                            map: texture,
                            side: THREE.DoubleSide
                        })

                    })

                }
            }

            flag_1.scale = 2.5
            flagModels[0] = loader.parse(flag_1)
            flag_2.scale = 2.5
            flagModels[1] = loader.parse(flag_2)
            flag_3.scale = 2.5
            flagModels[2] = loader.parse(flag_3)
            flag_4.scale = 2.5
            flagModels[3] = loader.parse(flag_4)
            flag_5.scale = 2.5
            flagModels[4] = loader.parse(flag_5)
            flag_6.scale = 2.5
            flagModels[5] = loader.parse(flag_6)
            flag_7.scale = 2.5
            flagModels[6] = loader.parse(flag_7)
            flag_8.scale = 2.5
            flagModels[7] = loader.parse(flag_8)
        },
        initTree = function () {
            treeModel = loader.parse(tree)
            treeModel.material = new THREE.MeshLambertMaterial({color: '#003300', side: THREE.DoubleSide})
        },
        isRoad = function (type) {
            if (type == 'r' || type == 'b') {
                return 1
            } else {
                return 0
            }
        },
        initPathCircle = function () {
            var radius = 1,
                segments = 64,
                opacity = 0.7

            pathMaterialGreen = new THREE.MeshBasicMaterial({
                color: 'green',
                transparent: true,
                opacity: opacity,
                side: THREE.DoubleSide
            })
            pathMaterialWhite = new THREE.MeshBasicMaterial({
                color: 'white',
                transparent: true,
                opacity: opacity,
                side: THREE.DoubleSide
            })
            pathMaterialRed = new THREE.MeshBasicMaterial({
                color: 'red',
                transparent: true,
                opacity: opacity,
                side: THREE.DoubleSide
            })
            pathGeometry = new THREE.CircleGeometry(radius, segments)
        }

    this.getPathCircle = function (color) {
        switch (color) {
            case 'green':
                return new THREE.Mesh(pathGeometry, pathMaterialGreen)
            case 'red':
                return new THREE.Mesh(pathGeometry, pathMaterialRed)
            case 'white':
                return new THREE.Mesh(pathGeometry, pathMaterialWhite)
        }
    }
    this.getArmyCircle = function () {
        var radius = 1,
            segments = 64,
            material1 = new THREE.MeshBasicMaterial({
                color: 'gold',
                transparent: true,
                opacity: 0.5,
                side: THREE.DoubleSide
            }),
            material2 = new THREE.MeshBasicMaterial({
                color: 0xffffff,
                transparent: true,
                opacity: 0.7,
                side: THREE.DoubleSide
            }),
            geometry1 = new THREE.CylinderGeometry(0.5, 0, 2, segments, segments, 1),
            geometry2 = new THREE.CircleGeometry(radius, segments)
        //geometry = new THREE.TorusGeometry(radius, 0.3, segments, segments)

        return {cylinder: new THREE.Mesh(geometry1, material1), circle: new THREE.Mesh(geometry2, material2)}
    }
    this.getRuin = function (color) {
        var mesh = new THREE.Mesh(ruinModel.geometry, new THREE.MeshPhongMaterial({
            color: color,
            side: THREE.DoubleSide
        }))
        if (shadows) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }
        return mesh
    }
    this.getTower = function (color) {
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
        return mesh
    }
    this.getCastle = function (castle, color) {
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
        mesh.add(flagMesh)
        mesh.add(createTextMesh(castle.name, '#ffffff'))
        updateCastleModel(mesh, castle.defense)
        return mesh
    }
    this.castleChangeDefense = function (mesh, defense) {
        mesh.children.splice(2, 3)
        updateCastleModel(mesh, defense)
    }
    this.getTree = function (x, y) {
        var mesh = new THREE.Mesh(treeModel.geometry, treeModel.material)
        return mesh
    }
    this.getRoad = function (x, y) {
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

        if (!isRoad(f1) && !isRoad(f2) && !isRoad(f3) && !isRoad(f4)) { // point
            var map = roadTexture.p
        } else if (isRoad(f1) && !isRoad(f2) && !isRoad(f3) && !isRoad(f4)) { // 3
            var map = roadTexture.u3
        } else if (!isRoad(f1) && isRoad(f2) && !isRoad(f3) && !isRoad(f4)) { // 3
            var map = roadTexture.l3
        } else if (!isRoad(f1) && !isRoad(f2) && isRoad(f3) && !isRoad(f4)) { // 3
            var map = roadTexture.d3
        } else if (!isRoad(f1) && !isRoad(f2) && !isRoad(f3) && isRoad(f4)) { // 3
            var map = roadTexture.r3
        } else if (isRoad(f1) && !isRoad(f2) && isRoad(f3) && !isRoad(f4)) { // vertical
            var map = roadTexture.v
        } else if (!isRoad(f1) && isRoad(f2) && !isRoad(f3) && isRoad(f4)) { // horizontal
            var map = roadTexture.h
        } else if (isRoad(f1) && isRoad(f2) && isRoad(f3) && isRoad(f4)) { // center
            var map = roadTexture.c
        } else if (!isRoad(f1) && isRoad(f2) && isRoad(f3) && !isRoad(f4)) {
            var map = roadTexture.ld
        } else if (!isRoad(f1) && !isRoad(f2) && isRoad(f3) && isRoad(f4)) {
            var map = roadTexture.rd
        } else if (isRoad(f1) && isRoad(f2) && !isRoad(f3) && !isRoad(f4)) {
            var map = roadTexture.lu
        } else if (isRoad(f1) && !isRoad(f2) && !isRoad(f3) && isRoad(f4)) {
            var map = roadTexture.ru
        } else if (isRoad(f1) && isRoad(f2) && !isRoad(f3) && isRoad(f4)) { // 1
            var map = roadTexture.u1
        } else if (isRoad(f1) && isRoad(f2) && isRoad(f3) && !isRoad(f4)) { // 1
            var map = roadTexture.l1
        } else if (!isRoad(f1) && isRoad(f2) && isRoad(f3) && isRoad(f4)) { // 1
            var map = roadTexture.d1
        } else if (isRoad(f1) && !isRoad(f2) && isRoad(f3) && isRoad(f4)) { // 1
            var map = roadTexture.r1
        }

        var roadMaterial = new THREE.MeshLambertMaterial({
                map: map,
                side: THREE.DoubleSide,
                transparent: true,
                opacity: 0.7
            }),
            mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), roadMaterial)
        return mesh
    }
    this.getSwamp = function (x, y) {
        var swampMaterial = new THREE.MeshLambertMaterial({
                map: swampTexture,
                side: THREE.DoubleSide,
                transparent: true,
                opacity: 0.5
            }),
            mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), swampMaterial)
        return mesh
    }
    this.getHero = function (color) {
        var armyMaterial = new THREE.MeshLambertMaterial({color: color, side: THREE.DoubleSide})
        var mesh = new THREE.Mesh(window['heroModel'].geometry, armyMaterial)
        return mesh
    }
    this.getUnit = function (color, modelName) {
        var armyMaterial = new THREE.MeshLambertMaterial({color: color, side: THREE.DoubleSide})
        if (modelName + 'Model' in window) {
            var mesh = new THREE.Mesh(window[modelName + 'Model'].geometry, armyMaterial)
        } else {
            var mesh = new THREE.Mesh(untitledModel.geometry, armyMaterial)
        }
        return mesh
    }
    this.getArmy = function (color, number, modelName) {
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

        flagMesh.position.set(-10, 0, 2)
        // mesh.rotation.y = Math.PI / 2 + Math.PI / 4
        mesh.add(flagMesh)
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
        loadFont()
        initRoadTexture()
        initSwampTexture()
        initRuin()
        initTower()
        initArmy()
        initCastle()
        initFlag()
        initTree()
        initPathCircle()
    }
}
