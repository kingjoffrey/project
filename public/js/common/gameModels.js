var GameModels = new function () {
    var pathCircles = [],
        moveCircles = [],
        armyCircles = [],
        hover = 0.1

    this.init = function () {
        Models.init()
    }

    this.addCastle = function (castle, color) {
        var mesh = Models.getCastle(castle, color)

        mesh.position.set(castle.x * 2 + 2, 0, castle.y * 2 + 2)

        mesh.children[0].rotation.y = Math.PI + Math.PI / 4

        mesh.scale.x = 0.2
        mesh.scale.y = 0.2
        mesh.scale.z = 0.2

        mesh.children[0].scale.x = 7
        mesh.children[0].scale.y = 7
        mesh.children[0].scale.z = 7

        if (Page.getShadows()) {
            mesh.castShadow = true
            mesh.children[0].castShadow = true
        }

        GameScene.add(mesh)
        return mesh
    }
    this.addArmy = function (x, y, color, number, modelName) {
        var mesh = Models.getArmy(color, number, modelName)

        mesh.rotation.y = Math.PI / 2 + Math.PI / 4
        mesh.children[0].rotation.y = Math.PI + Math.PI / 4

        mesh.scale.x = 0.1
        mesh.scale.y = 0.1
        mesh.scale.z = 0.1

        mesh.children[0].scale.x = 2
        mesh.children[0].scale.y = 2
        mesh.children[0].scale.z = 2

        mesh.children[0].position.set(-16, 0, 4)

        if (Page.getShadows()) {
            mesh.castShadow = true
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

        if (Page.getShadows()) {
            mesh.castShadow = true
        }

        GameScene.add(mesh)
        return mesh
    }
    this.addBridge = function (x, y) {
        var mesh = Models.getBridge()

        // mesh.scale.x = 0.5
        // mesh.scale.y = 0.5
        // mesh.scale.z = 0.5

        mesh.position.set(x * 2 + 0.5, 0, y * 2 + 0.5)

        if (Page.getShadows()) {
            mesh.castShadow = true
        }

        GameScene.add(mesh)
        return mesh
    }
    this.addTower = function (x, y, color) {
        var mesh = Models.getTower(color)
        mesh.position.set(x * 2 + 0.5, 0, y * 2 + 0.5)

        mesh.children[0].rotation.y = Math.PI + Math.PI / 4

        mesh.scale.x = 0.3
        mesh.scale.y = 0.3
        mesh.scale.z = 0.3

        mesh.children[0].scale.x = 3.3
        mesh.children[0].scale.y = 3.3
        mesh.children[0].scale.z = 3.3

        if (Page.getShadows()) {
            mesh.castShadow = true
            mesh.children[0].castShadow = true
        }

        GameScene.add(mesh)
        return mesh
    }
    this.addTree = function (x, y) {
        // if (Page.hasTouch()) {
        //     var maxI = 1
        // } else {
        //     var maxI = Math.floor((Math.random() * 5))
        // }

        // for (var i = 0; i < maxI + 2; i++) {
        var mesh = Models.getTree(),
            randomX = Math.floor((Math.random() * 2)),
            randomY = Math.floor((Math.random() * 2))

        // console.log(randomX + ' ' + randomY)

        mesh.position.set(x * 2 + randomX, 0, y * 2 + randomY)
        mesh.rotation.y = 2 * Math.PI * Math.random()

        mesh.scale.x = 0.15
        mesh.scale.y = 0.15
        mesh.scale.z = 0.15

        // if (Page.getShadows()) {
        //     mesh.castShadow = true
        // }
        GameScene.add(mesh)
        // }
    }
    this.addPathCircle = function (x, y, color, t) {
        switch (t) {
            case 'm':
                var height = -Ground.getMountainLevel() + hover
                break
            case 'h':
                var height = -Ground.getHillLevel() + hover
                break
            case 'E':
                this.addPathCircle(x, y, 'red', Fields.get(x, y).getType())
                return
                break
            default :
                var height = hover
                break
        }

        var circle = Models.getPathCircle(color)

        if (Page.getShadows()) {
            circle.castShadow = true
        }

        circle.position.set(x * 2 + 1, height, y * 2 + 1)
        circle.rotation.x = Math.PI / 2

        GameScene.add(circle)
        pathCircles.push(circle)
    }
    this.addArmyCircle = function (x, y, color) {
        var meshes = Models.getArmyCircle(color)
        switch (Fields.get(x, y).getType()) {
            case 'm':
                var height = -Ground.getMountainLevel() + hover
                break
            case 'h':
                var height = -Ground.getHillLevel() + hover
                break
            default :
                var height = hover
                break
        }

        meshes.cylinder.position.set(x * 2 + 1, 4 + height, y * 2 + 1)

        GameScene.add(meshes.cylinder)
        armyCircles.push(meshes.cylinder)

        meshes.circle.position.set(x * 2 + 1, height, y * 2 + 1)
        meshes.circle.rotation.x = Math.PI / 2
        if (Page.getShadows()) {
            meshes.circle.castShadow = true
            meshes.cylinder.castShadow = true
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
