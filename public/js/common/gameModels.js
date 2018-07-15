var GameModels = new function () {
    var pathCircles = [],
        moveCircles = [],
        armySelectionMeshes = [],
        hover = 0.01,
        bridgeLevel = 0.3,
        cursorColor = 'white',
        cursorMesh

    this.init = function () {
        Models.init()
    }

    this.addCastle = function (castle, color) {
        var mesh = Models.getCastle(castle.toArray(), color)

        if (castle.getProductionId()) {
            var productionMesh = GameModels.addProduction(castle.getX(), castle.getY(), Unit.getName(castle.getProductionId()))
            mesh.add(productionMesh)
        }

        mesh.position.set(castle.getX() * 2 + 2, 0, castle.getY() * 2 + 2)

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
    this.addProduction = function (x, y, modelName) {
        var mesh = Models.getUnit(modelName)

        mesh.scale.x = 0.2
        mesh.scale.y = 0.2
        mesh.scale.z = 0.2

        mesh.rotation.y = Math.PI / 2 + Math.PI / 4

        mesh.position.set(-1, 13, 0)

        return mesh
    }
    this.addUnit = function (x, y, modelName) {
        var mesh = Models.getUnit(modelName)

        mesh.scale.x = 0.12
        mesh.scale.y = 0.12
        mesh.scale.z = 0.12

        mesh.rotation.y = Math.PI / 2 + Math.PI / 4

        mesh.position.set(x * 2, 2, (y + 2) * 2)

        GameScene.add(mesh)
        PickerCommon.attach(mesh)
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
    this.cursorPosition = function (x, y, t) {
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

        cursorMesh.position.set(x * 2 + 1, height, y * 2 + 1)
    }
    this.changeCursorColor = function (color) {
        if (cursorColor == color) {
            return
        }

        cursorColor = color

        switch (color) {
            case 'white':
                cursorMesh.material.color.setHex(0xffffff)
                break
            case 'red':
                cursorMesh.material.color.setHex(0xff0000)
                break
        }
    }
    this.addCursor = function () {
        cursorMesh = Models.getCursorModel()

        if (Page.getShadows()) {
            cursorMesh.castShadow = true
        }

        cursorMesh.rotation.x = Math.PI / 2

        GameScene.add(cursorMesh)
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
    this.addArmySelectionBox = function (x, y, color) {

        var type = Fields.get(x, y).getType()

        switch (type) {
            case 'm':
                var height = -Ground.getMountainLevel() + hover
                break
            case 'h':
                var height = Fields.get(x, y).getLevel() * 0.05
                break
            case 'b':
                var height = bridgeLevel + hover
                break
            default :
                var height = hover
                break
        }

        var z = 0,
            bottom1 = Models.getArmyBoxBottom(),
            bottom2 = Models.getCursorModel()

        if (Page.getShadows()) {
            bottom1.castShadow = true
            bottom2.castShadow = true
        }

        bottom1.position.set(x * 2 + 1, height, y * 2 + 1)
        this.cursorPosition(x, y, type, bottom2)

        bottom1.rotation.x = Math.PI / 2
        bottom2.rotation.x = Math.PI / 2

        GameScene.add(bottom1)
        GameScene.add(bottom2)

        armySelectionMeshes.push(bottom1)
        armySelectionMeshes.push(bottom2)

        for (var i = 0; i < 10; i++) {
            if (z) {
                z = z * 2 - z / 4
            } else {
                z = 0.25
            }
            var tmpMesh = Models.getArmyBoxRings(color)
            if (Page.getShadows()) {
                tmpMesh.castShadow = true
            }
            GameScene.add(tmpMesh)
            armySelectionMeshes.push(tmpMesh)
            tmpMesh.position.set(x * 2 + 1, z + height, y * 2)
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
            delete moveCircles[i]
        }
        // moveCircles = []
    }
    this.clearPathCircles = function () {
        for (var i in pathCircles) {
            GameScene.remove(pathCircles[i])
            delete pathCircles[i]
        }
        // pathCircles = []
    }
    this.clearArmySelectionMeshes = function () {
        for (var i in armySelectionMeshes) {
            GameScene.remove(armySelectionMeshes[i])
        }
        armySelectionMeshes = []
    }

}
