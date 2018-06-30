var Models = new function () {
    var armyModels = {},
        castleModels = [],
        flagModels = [],
        models = {},
        JSONLoader = new THREE.JSONLoader(),
        objectLoader = new THREE.ObjectLoader(),
        textureLoader = new THREE.TextureLoader(),
        pathMaterialGreen,
        pathMaterialRed,
        pathMaterialWhite,
        pathCircleMaterialGreen,
        pathCircleMaterialWhite,
        pathCircleMaterialRed,
        pathGeometryRectangle,
        pathGeometryCircle,
        pathGeometryRing,
        pathGeometry,
        cursorGeometry,
        materialPurple,
        initModelAndTexture = function (id) {
            JSONLoader.load('/models/' + id + '.json', function (geometry, materials) {
                models[id] = geometry
                textureLoader.load('/img/modelMaps/' + id + '.png', function (texture) {
                    models[id].material = new THREE.MeshLambertMaterial({
                        map: texture,
                        side: THREE.DoubleSide
                    })
                })
            })
        },
        initModel = function (id, material) {
            JSONLoader.load('/models/' + id + '.json', function (geometry, materials) {
                models[id] = geometry
                if (isSet(material)) {
                    models[id].material = material
                }
            })
        },
        initRuin = function () {
            // ruinModel = JSONLoader.parse(ruin)
            initModel('ruin')
        },
        initTree = function () {
            initModel('tree', new THREE.MeshLambertMaterial({color: '#003300', side: THREE.DoubleSide}))
        },
        initBridge = function () {
            initModelAndTexture('bridge')
        },
        initTower = function () {
            initModelAndTexture('tower')
        },
        initCastle = function () {
            JSONLoader.load('/models/castle_1.json', function (geometry, materials) {
                castleModels[0] = geometry
            })
            JSONLoader.load('/models/castle_2.json', function (geometry, materials) {
                castleModels[1] = geometry
            })
            JSONLoader.load('/models/castle_3.json', function (geometry, materials) {
                castleModels[2] = geometry
                textureLoader.load('/img/modelMaps/castle_3.png', function (texture) {
                    castleModels[2].material = new THREE.MeshLambertMaterial({
                        map: texture,
                        side: THREE.DoubleSide
                    })
                })
            })
            JSONLoader.load('/models/castle_4.json', function (geometry, materials) {
                castleModels[3] = geometry
                textureLoader.load('/img/modelMaps/castle_4.png', function (texture) {
                    castleModels[3].material = new THREE.MeshLambertMaterial({
                        map: texture,
                        side: THREE.DoubleSide
                    })
                })
            })
            JSONLoader.load('/models/castle_5.json', function (geometry, materials) {
                castleModels[4] = geometry
                textureLoader.load('/img/modelMaps/castle_5.png', function (texture) {
                    castleModels[4].material = new THREE.MeshLambertMaterial({
                        map: texture,
                        side: THREE.DoubleSide
                    })
                })
            })
        },
        initFlag = function () {
            initModel('flag')
        },
        getCastleModel = function (defense) {
            switch (defense) {
                case 2:
                    return new THREE.Mesh(castleModels[1], new THREE.MeshLambertMaterial({
                        color: '#65402C',
                        side: THREE.DoubleSide
                    }))
                case 3:
                    return new THREE.Mesh(castleModels[2], castleModels[2].material)
                case 4:
                    return new THREE.Mesh(castleModels[3], castleModels[3].material)
                case 5:
                    var mesh = new THREE.Mesh(castleModels[4], castleModels[4].material)
                    mesh.scale.x = 0.5
                    // mesh.scale.y = 0.8
                    mesh.scale.z = 0.5
                    return mesh
            }
        },
        updateCastleModel = function (mesh, defense, capital) {
            if (capital) {
                var m = getCastleModel(5)

                if (Page.getShadows()) {
                    m.castShadow = true
                    m.receiveShadow = true
                }
                mesh.add(m)
            }
            for (var i = 2; i <= defense; i++) {
                if (i == 4) {
                    var m = getCastleModel(i)
                    m.position.set(7, 0, 7)

                    var m1 = getCastleModel(i)
                    m1.position.set(-14, 0, -14)
                    m.add(m1)
                    var m2 = getCastleModel(i)
                    m2.position.set(-14, 0, 0)
                    m.add(m2)
                    var m3 = getCastleModel(i)
                    m3.position.set(0, 0, -14)
                    m.add(m3)

                    if (Page.getShadows()) {
                        m.castShadow = true
                        m.receiveShadow = true
                        m1.castShadow = true
                        m1.receiveShadow = true
                        m2.castShadow = true
                        m2.receiveShadow = true
                        m3.castShadow = true
                        m3.receiveShadow = true
                    }
                } else {
                    var m = getCastleModel(i)
                    if (Page.getShadows()) {
                        m.castShadow = true
                        m.receiveShadow = true
                    }
                }


                mesh.add(m)
            }
        },
        armyLoadCallback = function (id) {
            return function (geometry, materials) {
                armyModels[id] = geometry

                textureLoader.load('/img/modelMaps/' + id + '.png', function (texture) {
                    armyModels[id].material = new THREE.MeshLambertMaterial({
                        map: texture,
                        side: THREE.DoubleSide
                    })
                })
            }
        },
        initArmy = function () {
            var models = [
                'archers',
                'hero',
                'light_infantry',
                'heavy_infantry',
                'dragon',
                'cavalry',
                'navy',
                'undead',
                'wizard',
                'demon'
            ]
            for (var i in models) {
                var id = models[i]
                JSONLoader.load('/models/' + id + '.json', armyLoadCallback(id))

                // armyModels[i] = loader.parse(models[i])
            }

            JSONLoader.load('/models/flag_1.json', function (geometry, materials) {
                flagModels[0] = geometry
            })
            JSONLoader.load('/models/flag_2.json', function (geometry, materials) {
                flagModels[1] = geometry
            })
            JSONLoader.load('/models/flag_3.json', function (geometry, materials) {
                flagModels[2] = geometry
            })
            JSONLoader.load('/models/flag_4.json', function (geometry, materials) {
                flagModels[3] = geometry
            })
            JSONLoader.load('/models/flag_5.json', function (geometry, materials) {
                flagModels[4] = geometry
            })
            JSONLoader.load('/models/flag_6.json', function (geometry, materials) {
                flagModels[5] = geometry
            })
            JSONLoader.load('/models/flag_7.json', function (geometry, materials) {
                flagModels[6] = geometry
            })
            JSONLoader.load('/models/flag_8.json', function (geometry, materials) {
                flagModels[7] = geometry
            })
        },
        initPath = function () {
            var radius = 0.7,
                segments = 64,
                opacity = 0.2

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
            pathCircleMaterialGreen = new THREE.MeshBasicMaterial({
                color: 'green',
                side: THREE.DoubleSide
            })
            pathCircleMaterialWhite = new THREE.MeshBasicMaterial({
                color: 'white',
                side: THREE.DoubleSide
            })
            pathCircleMaterialRed = new THREE.MeshBasicMaterial({
                color: 'red',
                side: THREE.DoubleSide
            })
            pathGeometryRectangle = new THREE.PlaneGeometry(1.9, 1.9)
            pathGeometryCircle = new THREE.CircleGeometry(radius, segments)
            pathGeometryRing = new THREE.RingGeometry(0.7, 0.9, 32)

            materialPurple = new THREE.MeshBasicMaterial({
                color: 0xff00ff,
                transparent: true,
                opacity: 0.5,
                side: THREE.DoubleSide
            })
        }

    this.getCursorModel = function () {
        return new THREE.Mesh(pathGeometryRectangle, pathCircleMaterialWhite)
    }
    this.getPathRectangle = function (color) {
        switch (color) {
            case 'green':
                return new THREE.Mesh(pathGeometryRectangle, pathMaterialGreen)
            case 'red':
                return new THREE.Mesh(pathGeometryRectangle, pathMaterialRed)
            case 'white':
                return new THREE.Mesh(pathGeometryRectangle, pathMaterialWhite)
        }
    }
    this.getPathCircle = function (color) {
        switch (color) {
            case 'green':
                return new THREE.Mesh(pathGeometryCircle, pathCircleMaterialWhite)
            case 'red':
                return new THREE.Mesh(pathGeometryCircle, pathCircleMaterialRed)
            case 'white':
                return new THREE.Mesh(pathGeometryRing, pathCircleMaterialWhite)
        }
    }
    this.getArmyBoxBottom = function () {
        return new THREE.Mesh(pathGeometryRectangle, materialPurple)
    }
    this.getArmyBoxRings = function () {
        var geometry1 = new THREE.PlaneGeometry(2, 0.1)

        var mesh = new THREE.Mesh(geometry1, materialPurple)

        mesh.add(new THREE.Mesh(geometry1, materialPurple))
        mesh.children[0].position.z = 2

        mesh.add(new THREE.Mesh(geometry1, materialPurple))
        mesh.children[1].position.x = -1
        mesh.children[1].rotation.y = Math.PI / 2
        mesh.children[1].position.z = 1

        mesh.add(new THREE.Mesh(geometry1, materialPurple))
        mesh.children[2].position.x = 1
        mesh.children[2].rotation.y = Math.PI / 2
        mesh.children[2].position.z = 1

        return mesh
    }
    this.getArmyRectangle = function (color) {
        var radius = 1,
            segments = 64,
            material1 = new THREE.MeshBasicMaterial({
                color: 'gold',
                transparent: true,
                opacity: 0.9,
                side: THREE.DoubleSide
            }),
            material2 = new THREE.MeshBasicMaterial({
                color: color,
                transparent: true,
                opacity: 0.9,
                side: THREE.DoubleSide
            }),
            geometry1 = new THREE.CylinderGeometry(0.5, 0, 2, segments, segments, 1),
            geometry2 = new THREE.PlaneGeometry(1.9, 1.9)

        return {cylinder: new THREE.Mesh(geometry1, material1), circle: new THREE.Mesh(geometry2, material2)}
    }
    this.getArmyCircle = function (color) {
        var radius = 1,
            segments = 64,
            material1 = new THREE.MeshBasicMaterial({
                color: 'gold',
                transparent: true,
                opacity: 0.9,
                side: THREE.DoubleSide
            }),
            material2 = new THREE.MeshBasicMaterial({
                color: color,
                transparent: true,
                opacity: 0.9,
                side: THREE.DoubleSide
            }),
            geometry1 = new THREE.CylinderGeometry(0.5, 0, 2, segments, segments, 1),
            geometry2 = new THREE.CircleGeometry(radius, segments)
        //geometry = new THREE.TorusGeometry(radius, 0.3, segments, segments)

        return {cylinder: new THREE.Mesh(geometry1, material1), circle: new THREE.Mesh(geometry2, material2)}
    }
    this.getRuin = function (color) {
        return new THREE.Mesh(models['ruin'], new THREE.MeshPhongMaterial({
            color: color,
            side: THREE.DoubleSide
        }))
    }
    this.getTree = function () {
        return new THREE.Mesh(models['tree'], models['tree'].material)
    }
    this.getBridge = function () {
        return new THREE.Mesh(models['bridge'], models['bridge'].material)
    }
    this.getTower = function (color) {
        var mesh = new THREE.Mesh(models['tower'], models['tower'].material),
            flagMesh = new THREE.Mesh(models['flag'], new THREE.MeshLambertMaterial({
                color: color
            }))
        mesh.add(flagMesh)
        return mesh
    }
    this.getCastle = function (castle, color) {
        var mesh = new THREE.Mesh(castleModels[0], new THREE.MeshLambertMaterial({
                color: '#3B3028',
                side: THREE.DoubleSide
            })),
            flagMesh = new THREE.Mesh(models['flag'], new THREE.MeshLambertMaterial({
                color: color
            }))
        mesh.add(flagMesh)
        updateCastleModel(mesh, castle.defense, castle.capital)
        return mesh
    }
    this.castleChangeDefense = function (mesh, defense, capital) {
        mesh.children.splice(1, 4) // usuń 4 elementy począwszy od indexu 1
        updateCastleModel(mesh, defense, capital)
    }
    this.getHero = function (color) {
        return new THREE.Mesh(armyModels.hero, armyModels.hero.material)
    }
    this.getUnit = function (modelName) {
        return new THREE.Mesh(armyModels[modelName], armyModels[modelName].material)
    }
    this.getLifeBar = function (life) {
        var lifeMesh = new THREE.Mesh(new THREE.BoxGeometry(0.5, 2, 28 * life), new THREE.MeshLambertMaterial({
            color: 0x00ff00,
            side: THREE.DoubleSide
        }))
        lifeMesh.position.x = -7
        lifeMesh.position.y = 30
        lifeMesh.rotation.y = Math.PI / 2

        return lifeMesh
    }
    this.getArmy = function (color, number, modelName) {
        var flagMesh = new THREE.Mesh(flagModels[number - 1], new THREE.MeshLambertMaterial({
                color: color,
                side: THREE.DoubleSide
            })),
            mesh = new THREE.Mesh(armyModels[modelName], armyModels[modelName].material)

        flagMesh.position.set(-10, 0, 2)
        mesh.add(flagMesh)
        return mesh
    }
    this.getCastleModels = function () {
        return castleModels
    }
    this.init = function () {
        initRuin()
        initBridge()
        initTower()
        initArmy()
        initCastle()
        initFlag()
        initTree()
        initPath()
    }
}
