var GameModels = new function () {
    var pathCircles = [],
        moveCircles = [],
        armyCircles = [],
        hover = 0.01,
        bridgeLevel = 0.3

    this.init = function () {
        Models.init()
    }

    this.addCastle = function (castle, color) {
        var mesh = Models.getCastle(castle, color)

        mesh.position.set(castle.x * 2 + 2, 0, castle.y * 2 + 2)

        // mesh.children[0].rotation.y = Math.PI + Math.PI / 4

        mesh.scale.x = 0.2
        mesh.scale.y = 0.2
        mesh.scale.z = 0.2

        mesh.children[0].scale.x = 7
        mesh.children[0].scale.y = 7
        mesh.children[0].scale.z = 7

        if (Page.getShadows()) {
            mesh.receiveShadow = true
            mesh.castShadow = true
            mesh.children[0].castShadow = true
        }

        GameScene.add(mesh)
        return mesh
    }
    this.addArmy = function (x, y, color, number, modelName, canSwim, life) {
        var mesh = Models.getArmy(color, number, modelName),
            lifeMesh = Models.getLifeBar(life)

        mesh.add(lifeMesh)

        mesh.rotation.y = Math.PI / 2 + Math.PI / 4
        // mesh.children[0].rotation.y = Math.PI + Math.PI / 4

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
        this.setArmyPosition(mesh, x, y, canSwim)
        GameScene.add(mesh)
        return mesh
    }
    this.setArmyPosition = function (mesh, x, y, canSwim) {
        switch (Fields.get(x, y).getType()) {
            case 'm':
                var height = -Ground.getMountainLevel()
                break
            case 'h':
                var height = Fields.get(x, y).getLevel() * 0.05
                break
            case 'w':
                var height = Ground.getWaterLevel()
                break
            case 'b':
                if (canSwim) {
                    var height = 0
                } else {
                    var height = bridgeLevel
                }
                break
            default :
                var height = 0
                break
        }
        mesh.position.set(x * 2 + 0.5, height, y * 2 + 0.5)
    }
    this.addRuin = function (x, y, color) {
        var mesh = Models.getRuin(color)

        mesh.scale.x = 0.1
        mesh.scale.y = 0.1
        mesh.scale.z = 0.1

        mesh.position.set(x * 2 + 1, 0, y * 2 + 1)

        // mesh.rotation.y = 2 * Math.PI * Math.random()

        if (Page.getShadows()) {
            mesh.castShadow = true
        }

        GameScene.add(mesh)
        return mesh
    }
    this.addBridge = function (x, y, rotate) {
        var mesh = Models.getBridge()

        mesh.position.set(x * 2 + 1, 0, y * 2 + 1)

        if (rotate) {
            mesh.rotation.y = Math.PI / 2
        }

        if (Page.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        GameScene.add(mesh)
        return mesh
    }
    this.addTower = function (x, y, color) {
        var mesh = Models.getTower(color)
        mesh.position.set(x * 2 + 0.5, 0, y * 2 + 0.5)

        // mesh.children[0].rotation.y = Math.PI + Math.PI / 4

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
        // if (isTouchDevice()) {
        var maxI = 1
        // } else {
        //     var maxI = Math.ceil(Math.random() * 3)
        // }

        // for (var i = 0; i < maxI; i++) {
        //     var mesh = Models.getTree(),
        //         randomX = Math.random() * 2,
        //         randomY = Math.random() * 2
        //
        //     console.log(randomX + ' ' + randomY)

        // mesh.position.set(x * 2 + randomX, 0, y * 2 + randomY)

        var mesh = Models.getTree()
        mesh.position.set(x * 2 + 1, 0, y * 2 + 1)
        mesh.rotation.y = 2 * Math.PI * Math.random()

        mesh.scale.x = 0.15
        mesh.scale.y = 0.15
        mesh.scale.z = 0.15

        if (Page.getShadows()) {
            mesh.castShadow = true
        }
        GameScene.add(mesh)
        // }
    }
    this.cursorPosition = function (x, y, t, cursor) {
        switch (t) {
            case 'm':
                var height = -Ground.getMountainLevel() + hover / 2
                break
            case 'h':
                var height = Fields.get(x, y).getLevel() * 0.05 + hover / 2
                break
            case 'b':
                var height = bridgeLevel + hover / 2
                break
            default :
                var height = hover / 2
                break
        }

        cursor.position.set(x * 2 + 1, height, y * 2 + 1)
    }
    this.addCursor = function () {
        var mesh = Models.getCursorModel()

        if (Page.getShadows()) {
            mesh.castShadow = true
        }

        mesh.rotation.x = Math.PI / 2

        GameScene.add(mesh)

        return mesh
    }
    this.addPathRectangle = function (x, y, color, t) {
        switch (t) {
            case 'm':
                var height = -Ground.getMountainLevel() + hover
                break
            case 'h':
                var height = Fields.get(x, y).getLevel() * 0.05 + hover
                break
            case 'E':
                this.addPathRectangle(x, y, 'red', Fields.get(x, y).getType())
                return
                break
            case 'b':
                var height = bridgeLevel + hover
                break
            default :
                var height = hover
                break
        }

        var circle = Models.getPathRectangle(color)

        if (Page.getShadows()) {
            circle.castShadow = true
        }

        circle.position.set(x * 2 + 1, height, y * 2 + 1)
        circle.rotation.x = Math.PI / 2

        GameScene.add(circle)
        pathCircles.push(circle)
    }
    this.addPathCircle = function (x, y, color, t) {
        switch (t) {
            case 'm':
                var height = -Ground.getMountainLevel() + hover
                break
            case 'h':
                var height = Fields.get(x, y).getLevel() * 0.05
                break
            case 'E':
                this.addPathCircle(x, y, 'red', Fields.get(x, y).getType())
                return
                break
            case 'b':
                var height = bridgeLevel + hover
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
    this.addArmyBox = function (x, y, color) {
        var mesh1 = Models.getArmyBox(color),
            mesh2 = Models.getArmyBox(color),
            mesh3 = Models.getArmyBox(color)
        switch (Fields.get(x, y).getType()) {
            case 'm':
                var height = 0
                break
            case 'h':
                var height = 0
                break
            case 'b':
                var height = 0
                break
            default :
                var height = 0
                break
        }

        GameScene.add(mesh1)
        armyCircles.push(mesh1)
        mesh1.position.set(x * 2 + 1, 0.25 + height, y * 2)

        GameScene.add(mesh2)
        armyCircles.push(mesh2)
        mesh2.position.set(x * 2 + 1, 0.75 + height, y * 2)

        GameScene.add(mesh3)
        armyCircles.push(mesh3)
        mesh3.position.set(x * 2 + 1, 1.25 + height, y * 2)

        if (Page.getShadows()) {
            mesh1.castShadow = true
            mesh2.castShadow = true
            mesh3.castShadow = true
        }
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
