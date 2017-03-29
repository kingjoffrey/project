var HelpModels = new function () {
    var addTree = function (x, y, maxX, maxY) {
            var mesh = Models.getTree()

            mesh.position.set(2 * x - maxX + 1, 2 * y - maxY + 1, 0)
            mesh.rotation.x = -Math.PI / 2

            mesh.scale.x = 0.15
            mesh.scale.y = 0.15
            mesh.scale.z = 0.15

            if (Page.getShadows()) {
                mesh.castShadow = true
            }

            return mesh
        },
        addBridge = function (x, y, maxX, maxY) {
            var mesh = Models.getBridge()

            mesh.position.set(2 * x - maxX + 1, 2 * y - maxY + 1, 0)
            mesh.rotation.x = -Math.PI / 2

            mesh.rotation.y = Math.PI / 2

            if (Page.getShadows()) {
                mesh.castShadow = true
                mesh.receiveShadow = true
            }

            return mesh
        },
        addFooting = function () {
            var radius = 10,
                segments = 64,
                material1 = new THREE.MeshBasicMaterial({
                    color: 'gold',
                    side: THREE.DoubleSide
                }),
                material2 = new THREE.MeshBasicMaterial({
                    color: 'green',
                    side: THREE.DoubleSide
                }),
                geometry1 = new THREE.CylinderGeometry(radius, radius, 1, segments, 1, 0),
                geometry2 = new THREE.CircleGeometry(radius, segments)

            var circle = new THREE.Mesh(geometry2, material2)

            circle.rotateX(Math.PI / 2)

            circle.position.y = 0.5

            var mesh = new THREE.Mesh(geometry1, material1)

            mesh.add(circle)

            mesh.position.y = 10

            if (Page.getShadows()) {
                mesh.receiveShadow = true
            }

            HelpScene.add(mesh)
        }
    this.addCastle = function (defense) {
        if (defense == 5) {
            var capital = 1
        } else {
            var capital = 0
        }
        var mesh = Models.getCastle({'defense': defense, 'capital': capital}, 'orange')

        mesh.rotation.y = Math.PI / 16

        mesh.position.set(25, 0, -25)
        mesh.scale.x = 0.5
        mesh.scale.y = 0.5
        mesh.scale.z = 0.5

        mesh.children[0].scale.x = 7
        mesh.children[0].scale.y = 7
        mesh.children[0].scale.z = 7

        if (Page.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
            mesh.children[0].castShadow = true
        }

        HelpScene.add(mesh)
        return mesh
    }
    this.addArmy = function () {
        var mesh = Models.getArmy('orange', 8, 'light_infantry')

        mesh.rotation.y = Math.PI / 2 + Math.PI / 4
        mesh.scale.x = 0.5
        mesh.scale.y = 0.5
        mesh.scale.z = 0.5

        mesh.children[0].scale.x = 2
        mesh.children[0].scale.y = 2
        mesh.children[0].scale.z = 2

        mesh.position.set(20, 0, -20)

        if (Page.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
            mesh.children[0].castShadow = true
        }

        HelpScene.add(mesh)
        return mesh
    }
    this.addUnit = function (modelName) {
        var mesh = Models.getUnit('orange', modelName)
        mesh.rotation.y = Math.PI / 2
        mesh.scale.x = 0.5
        mesh.scale.y = 0.5
        mesh.scale.z = 0.5

        mesh.position.set(20, 0, -20)

        if (Page.getShadows()) {
            mesh.castShadow = true
            // mesh.receiveShadow = true
        }

        mesh.position.y = 1

        addFooting()

        HelpScene.add(mesh)
        return mesh
    }
    this.addRuin = function () {
        var mesh = Models.getRuin('gold')

        mesh.scale.x = 0.3
        mesh.scale.y = 0.3
        mesh.scale.z = 0.3

        mesh.position.set(25, 0, -25)

        mesh.rotation.y = 2 * Math.PI * Math.random()

        if (Page.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        HelpScene.add(mesh)
        return mesh
    }
    this.addTower = function () {
        var mesh = Models.getTower('orange')
        mesh.position.set(20, 0, -20)

        mesh.scale.x = 1
        mesh.scale.y = 1
        mesh.scale.z = 1

        mesh.children[0].scale.x = 3.3
        mesh.children[0].scale.y = 3.3
        mesh.children[0].scale.z = 3.3

        if (Page.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
            mesh.children[0].castShadow = true
        }

        HelpScene.add(mesh)
        return mesh
    }
    this.addHero = function () {
        var mesh = Models.getHero('orange')

        mesh.rotation.y = Math.PI / 2

        mesh.scale.x = 0.5
        mesh.scale.y = 0.5
        mesh.scale.z = 0.5
        mesh.position.set(20, 0, -20)

        if (Page.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        HelpScene.add(mesh)
        return mesh
    }
    this.addTerrain = function (matrix) {
        Fields.init(matrix)
        Fields.createTextures(1)
        var waterMesh = Ground.init(Fields.getMaxX(), Fields.getMaxY(), Fields.getTextureCanvas(), Fields.getWaterTextureCanvas())

        waterMesh.position.set(25, 0, -25)

        for (var y in matrix) {
            for (var x in matrix[y]) {
                switch (matrix[y][x]) {
                    case 'f':
                        var mesh = addTree(x, y, Fields.getMaxX(), Fields.getMaxY())
                        waterMesh.add(mesh)
                        break
                    case 'b':
                        var mesh = addBridge(x, y, Fields.getMaxX(), Fields.getMaxY())
                        waterMesh.add(mesh)
                        break
                }
            }
        }


        HelpScene.add(waterMesh)

        return waterMesh
    }
    this.init = function () {
        var mesh = new THREE.Mesh(new THREE.PlaneBufferGeometry(200, 200), new THREE.MeshLambertMaterial({
            color: 0x296a00,
            side: THREE.DoubleSide
        }))
        mesh.rotation.x = Math.PI / 2
        mesh.position.y = -0.1
        if (Page.getShadows()) {
            mesh.receiveShadow = true
        }

        HelpScene.add(mesh)
    }
}
