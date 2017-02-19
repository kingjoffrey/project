var Models = new function () {
    var ruinModel,
        towerModel,
        flagModel,
        armyModels = {},
        castleModels = [],
        flagModels = [],
        treeModel,
        loader = new THREE.JSONLoader(),
        objectLoader=new THREE.ObjectLoader(),
        tl = new THREE.TextureLoader(),
        loading = 0,
        pathMaterialGreen,
        pathMaterialRed,
        pathMaterialWhite,
        pathGeometry,
        initRuin = function () {
            ruinModel = loader.parse(ruin)
        },
        initTower = function () {
            towerModel = loader.parse(tower)
        },
        initCastle = function () {
            castleModels = [
                loader.parse(castle_1),
                loader.parse(castle_2),
                loader.parse(castle_3),
                loader.parse(castle_4)
            ]
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
                        color: '#6B6B6B',
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

                if (Page.getShadows()) {
                    m.castShadow = true
                    m.receiveShadow = true
                }
                mesh.add(m)
            }
        },
        initArmy = function () {
            armyModels = {
                'archers': archers,
                'hero': hero,
                'light_infantry': light_infantry,
                'heavy_infantry': heavy_infantry,
                'dragon': dragon,
                'cavalry': cavalry,
                'navy': navy,
                'undead': undead,
                'wizard': wizard,
                'demon': demon
            }
            for (var i in armyModels) {
                window[i + 'Model'] = loader.parse(armyModels[i])
                // if (i == 'hero') {
                //     tl.load(window.location.origin + '/img/modelMaps/hero.png', function (texture) {
                //         window['heroModel'].material = new THREE.MeshLambertMaterial({
                //             map: texture,
                //             side: THREE.DoubleSide
                //         })
                //     })
                // }
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
        initPathCircle = function () {
            var radius = 0.7,
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
    this.getArmyCircle = function (color) {
        var radius = 1,
            segments = 64,
            material1 = new THREE.MeshBasicMaterial({
                color: 'gold',
                transparent: true,
                opacity: 0.5,
                side: THREE.DoubleSide
            }),
            material2 = new THREE.MeshBasicMaterial({
                color: color,
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
        if (Page.getShadows()) {
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
        if (Page.getShadows()) {
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
        if (Page.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
            flagMesh.castShadow = true
            flagMesh.receiveShadow = true
        }
        mesh.add(flagMesh)
        updateCastleModel(mesh, castle.defense)
        return mesh
    }
    this.castleChangeDefense = function (mesh, defense) {
        mesh.children.splice(1, 3) // usuń 3 elementy począwszy od indexu 1
        updateCastleModel(mesh, defense)
    }
    this.getTree = function (x, y) {
        return new THREE.Mesh(treeModel.geometry, treeModel.material)
    }
    this.getHero = function (color) {
        return new THREE.Mesh(window['heroModel'].geometry, new THREE.MeshLambertMaterial({
            color: color,
            side: THREE.DoubleSide
        }))
    }
    this.getUnit = function (color, modelName) {
        return new THREE.Mesh(window[modelName + 'Model'].geometry, new THREE.MeshLambertMaterial({
            color: color,
            side: THREE.DoubleSide
        }))
    }
    this.getArmy = function (color, number, modelName) {
        var flagMesh = new THREE.Mesh(flagModels[number - 1].geometry, new THREE.MeshLambertMaterial({
                color: color,
                side: THREE.DoubleSide
            })),
            mesh = new THREE.Mesh(window[modelName + 'Model'].geometry, new THREE.MeshLambertMaterial({
                color: color,
                side: THREE.DoubleSide
            }))

        flagMesh.position.set(-10, 0, 2)
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
    this.getLoading = function () {
        return loading
    }
    this.init = function () {
        initRuin()
        initTower()
        initArmy()
        initCastle()
        initFlag()
        initTree()
        initPathCircle()
    }
}
