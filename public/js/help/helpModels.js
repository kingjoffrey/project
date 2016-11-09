var HelpModels = new function () {

    this.init = function () {
        Models.init()

        var mesh = new THREE.Mesh(new THREE.PlaneBufferGeometry(200, 200), new THREE.MeshLambertMaterial({
            color: 0xffffff,
            side: THREE.DoubleSide
        }))
        mesh.rotation.x = Math.PI / 2
        // mesh.position.set(0, -30, 0)
        if (HelpScene.getShadows()) {
            mesh.receiveShadow = true
        }

        HelpScene.add(mesh)
    }

    this.addCastle = function () {
        var mesh = Models.addCastle({defense: 4, name: 'Castle'}, 'orange')

        // mesh.rotation.y = Math.PI / 2 + Math.PI / 4

        mesh.position.set(25, 0, -25)
        mesh.scale.x = 2
        mesh.scale.y = 2
        mesh.scale.z = 2

        if (HelpScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        HelpScene.add(mesh)
        return mesh
    }
    this.addArmy = function () {
        var mesh = Models.addArmy('orange', 8, 'light_infantry')

        mesh.rotation.y = Math.PI / 2 + Math.PI / 4
        mesh.scale.x = 3
        mesh.scale.y = 3
        mesh.scale.z = 3
        mesh.position.set(25, 0, -25)

        HelpScene.add(mesh)
        return mesh
    }
    this.addUnit = function (modelName) {
        var mesh = Models.addUnit('orange', modelName)
        //mesh.rotation.y = Math.PI / 2 + Math.PI / 4
        mesh.rotation.y = Math.PI / 2
        mesh.scale.x = 3
        mesh.scale.y = 3
        mesh.scale.z = 3
        mesh.position.set(25, 0, -25)

        if (HelpScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        HelpScene.add(mesh)
        return mesh
    }
    this.addRuin = function () {
        var mesh = Models.addRuin('gold')

        mesh.scale.x = 3
        mesh.scale.y = 3
        mesh.scale.z = 3

        mesh.position.set(25, 0, -25)

        mesh.rotation.y = 2 * Math.PI * Math.random()

        if (HelpScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        HelpScene.add(mesh)
        return mesh
    }
    this.addTower = function () {
        var mesh = Models.addTower('orange')
        mesh.position.set(25, 0, -25)

        mesh.scale.x = 2
        mesh.scale.y = 2
        mesh.scale.z = 2

        if (HelpScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        HelpScene.add(mesh)
        return mesh
    }
    this.addHero = function () {
        var mesh = Models.addHero('orange')

        mesh.position.set(20, 0, -20)

        mesh.scale.x = 20
        mesh.scale.y = 20
        mesh.scale.z = 20

        if (HelpScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        HelpScene.add(mesh)
        return mesh
    }
}
