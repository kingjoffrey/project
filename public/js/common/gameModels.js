var GameModels = new function () {
    var pathCircles = [],
        moveCircles = [],
        armyCircles = []

    this.init = function () {
        Models.init()
    }

    this.addCastle = function (castle, color) {
        var mesh = Models.getCastle(castle, color)

        // mesh.rotation.y = Math.PI / 2 + Math.PI / 4

        mesh.position.set(castle.x * 2 + 2, 0, castle.y * 2 + 2)

        mesh.scale.x = 0.2
        mesh.scale.y = 0.2
        mesh.scale.z = 0.2

        mesh.children[0].scale.x = 7
        mesh.children[0].scale.y = 7
        mesh.children[0].scale.z = 7

        if (GameScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
            mesh.children[0].castShadow = true
        }

        GameScene.add(mesh)
        return mesh
    }
    this.addArmy = function (x, y, color, number, modelName) {
        var mesh = Models.getArmy(color, number, modelName)

        mesh.rotation.y = Math.PI / 2 + Math.PI / 4
        mesh.scale.x = 0.1
        mesh.scale.y = 0.1
        mesh.scale.z = 0.1

        mesh.children[0].scale.x = 2
        mesh.children[0].scale.y = 2
        mesh.children[0].scale.z = 2

        if (GameScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
            mesh.children[0].castShadow = true
        }
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
    this.addRuin = function (x, y, color) {
        var mesh = Models.getRuin(color)

        mesh.scale.x = 0.05
        mesh.scale.y = 0.05
        mesh.scale.z = 0.05

        mesh.position.set(x * 2 + 0.5, 0, y * 2 + 0.5)

        mesh.rotation.y = 2 * Math.PI * Math.random()

        if (GameScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        GameScene.add(mesh)
        return mesh
    }
    this.addTower = function (x, y, color) {
        var mesh = Models.getTower(color)
        mesh.position.set(x * 2 + 0.5, 0, y * 2 + 0.5)

        mesh.scale.x = 0.3
        mesh.scale.y = 0.3
        mesh.scale.z = 0.3

        mesh.children[0].scale.x = 3.3
        mesh.children[0].scale.y = 3.3
        mesh.children[0].scale.z = 3.3

        if (GameScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
            mesh.children[0].castShadow = true
        }

        GameScene.add(mesh)
        return mesh
    }
    this.addTree = function (x, y) {
        var mesh = Models.getTree(x, y)
        mesh.position.set(x * 2 + 1, 0, y * 2 + 1)
        mesh.rotation.y = 2 * Math.PI * Math.random()

        mesh.scale.x = 0.3
        mesh.scale.y = 0.3
        mesh.scale.z = 0.3

        if (GameScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }
        GameScene.add(mesh)
    }
    this.addSwamp = function (x, y) {
        var mesh = Models.getSwamp(x, y)

        mesh.rotation.x = Math.PI / 2
        mesh.position.set(x * 2 + 1, 0.0001, y * 2 + 1)

        if (GameScene.getShadows()) {
            mesh.receiveShadow = true
        }
        GameScene.add(mesh)
    }
    this.addRoad = function (x, y) {
        var mesh = Models.getRoad(x, y)
        mesh.rotation.x = Math.PI / 2
        mesh.position.set(x * 2 + 1, 0.0001, y * 2 + 1)

        if (GameScene.getShadows()) {
            mesh.receiveShadow = true
        }
        GameScene.add(mesh)
    }
    this.addPathCircle = function (x, y, color, t) {
        switch (t) {
            case 'm':
                var height = Ground.getMountainLevel() + 0.01
                break
            case 'h':
                var height = Ground.getHillLevel() + 0.01
                break
            case 'E':
                this.addPathCircle(x, y, 'red', Fields.get(x, y).getType())
                return
                break
            default :
                var height = 0.01
                break
        }

        var circle = Models.getPathCircle(color)

        circle.position.set(x * 2 + 1, height, y * 2 + 1)
        circle.rotation.x = Math.PI / 2

        GameScene.add(circle)
        pathCircles.push(circle)
    }
    this.addArmyCircle = function (x, y, color) {
        var meshes = Models.getArmyCircle()
        switch (Fields.get(x, y).getType()) {
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

        meshes.cylinder.position.set(x * 2 + 1, 4 + height, y * 2 + 1)
        //cylinder.rotation.x = Math.PI / 2
        GameScene.add(meshes.cylinder)
        armyCircles.push(meshes.cylinder)


        meshes.circle.position.set(x * 2 + 1, height, y * 2 + 1)
        meshes.circle.rotation.x = Math.PI / 2
        if (GameScene.getShadows()) {
            meshes.circle.castShadow = true
        }
        GameScene.add(meshes.circle)
        armyCircles.push(meshes.circle)
    }
    this.movePathCircles = function () {
        for (var i in pathCircles) {
            moveCircles[i] = pathCircles[i]
        }
        pathCircles = []
    }
    this.clearMoveCircles = function () {
        for (var i in moveCircles) {
            GameScene.remove(moveCircles[i])
        }
        moveCircles = []
    }
    this.clearPathCircles = function () {
        for (var i in pathCircles) {
            GameScene.remove(pathCircles[i])
        }
        pathCircles = []
    }
    this.clearArmyCircles = function () {
        for (var i in armyCircles) {
            GameScene.remove(armyCircles[i])
        }
        armyCircles = []
    }

}