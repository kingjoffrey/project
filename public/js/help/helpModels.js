var HelpModels = new function () {

    this.init = function () {
        Models.init()

        var mesh = new THREE.Mesh(new THREE.PlaneBufferGeometry(200, 200), new THREE.MeshLambertMaterial({
            color: 0xffffff,
            side: THREE.DoubleSide
        }))
        mesh.rotation.x = Math.PI / 2
        mesh.position.y = -0.1
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
        //mesh.rotation.y = Math.PI / 2 + Math.PI / 4
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
        Fields.createTextures()
        var waterMesh = Ground.init(Fields.getMaxX(), Fields.getMaxY(), Fields.getTextureCanvas(), Fields.getWaterTextureCanvas())

        waterMesh.position.set(25, 0, -25)

        HelpScene.add(waterMesh)

        return waterMesh
    }
}
