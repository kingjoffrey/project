var GameModels = new function () {

    this.init = function () {
        Models.init()
    }

    this.addCastle = function () {
        var mesh = Models.addCastle({defense: 4, name: 'Castle'}, 'orange')

        // mesh.rotation.y = Math.PI / 2 + Math.PI / 4

        mesh.position.set(25, 0, -25)
        mesh.scale.x = 2
        mesh.scale.y = 2
        mesh.scale.z = 2

        if (GameScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        GameScene.add(mesh)
        return mesh
    }
    this.addArmy = function (x, y, color, number, modelName) {
        var mesh = Models.addArmy(color, number, 'light_infantry')

        mesh.rotation.y = Math.PI / 2 + Math.PI / 4
        mesh.scale.x = 5
        mesh.scale.y = 5
        mesh.scale.z = 5

        this.setArmyPosition(mesh, x, y)
        GameScene.add(mesh)
        return mesh
    }
    this.setArmyPosition = function (mesh, x, y) {
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
            case 'b':
                var height = Ground.getWaterLevel()
                break
            default :
                var height = 0
                break
        }
        mesh.position.set(x * 2 + 0.5, height, y * 2 + 0.5)
    }
    this.addUnit = function (modelName) {
        var mesh = Models.addUnit('orange', modelName)
        //mesh.rotation.y = Math.PI / 2 + Math.PI / 4
        mesh.rotation.y = Math.PI / 2
        mesh.scale.x = 5
        mesh.scale.y = 5
        mesh.scale.z = 5
        mesh.position.set(20, 0, -20)

        if (GameScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        GameScene.add(mesh)
        return mesh
    }
    this.addRuin = function () {
        var mesh = Models.addRuin('gold')

        mesh.scale.x = 3
        mesh.scale.y = 3
        mesh.scale.z = 3

        mesh.position.set(25, 0, -25)

        mesh.rotation.y = 2 * Math.PI * Math.random()

        if (GameScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        GameScene.add(mesh)
        return mesh
    }
    this.addTower = function () {
        var mesh = Models.addTower('orange')
        mesh.position.set(20, 0, -20)

        mesh.scale.x = 2
        mesh.scale.y = 2
        mesh.scale.z = 2

        if (GameScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        GameScene.add(mesh)
        return mesh
    }
    this.addHero = function () {
        var mesh = Models.addHero('orange')

        mesh.position.set(20, 0, -20)

        mesh.scale.x = 20
        mesh.scale.y = 20
        mesh.scale.z = 20

        if (GameScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        GameScene.add(mesh)
        return mesh
    }
    this.addTree = function (x, y) {
        var mesh = Models.addTree(x, y)
        mesh.position.set(x * 2 + 1, 0, y * 2 + 1)
        mesh.rotation.y = 2 * Math.PI * Math.random()

        if (GameScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }
        GameScene.add(mesh)
    }
    this.addSwamp = function (x, y) {
        var mesh = Models.addSwamp(x, y)

        mesh.rotation.x = Math.PI / 2
        mesh.position.set(x * 2 + 1, 0.0001, y * 2 + 1)

        if (GameScene.getShadows()) {
            mesh.receiveShadow = true
        }
        GameScene.add(mesh)
    }
    this.addRoad = function (x, y) {
        var mesh = Models.addRoad(x, y)
        mesh.rotation.x = Math.PI / 2
        mesh.position.set(x * 2 + 1, 0.0001, y * 2 + 1)

        if (GameScene.getShadows()) {
            mesh.receiveShadow = true
        }
        GameScene.add(mesh)
    }
}
