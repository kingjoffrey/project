var Castle = function (castle, color, isCapital) {
    var mesh,
        units = []

    this.toArray = function () {
        return castle
    }
    this.getMesh = function () {
        return mesh
    }
    this.setProductionId = function (unitId) {
        castle.productionId = unitId
    }
    this.setProductionTurn = function (value) {
        castle.productionTurn = value
    }
    this.getX = function () {
        return castle.x
    }
    this.getY = function () {
        return castle.y
    }
    this.getName = function () {
        return castle.name
    }
    this.getIncome = function () {
        return castle.income * castle.defense
    }
    this.getDefense = function () {
        return castle.defense
    }
    this.getProduction = function () {
        return castle.production
    }
    this.getProductionId = function () {
        return castle.productionId
    }
    this.getProductionTurn = function () {
        return castle.productionTurn
    }
    this.getCastleId = function () {
        return castle.id
    }
    this.getEnclaveNumber = function () {
        return castle.enclaveNumber
    }
    this.changeOwner = function (color) {
        this.setProductionId(null)
        mesh.children[0].material.color.set(color)
    }
    this.setDefense = function (defense) {
        castle.defense = defense
        for (var i in mesh.children) {
            if (mesh.children[i].name == 'def') {
                mesh.remove(mesh.children[i])
            }
        }
        Models.castleChangeDefense(mesh, defense)
    }
    this.updateCapital = function (isCapital) {
        for (var i in mesh.children) {
            if (mesh.children[i].name == 'capital') {
                mesh.remove(mesh.children[i])
            }
        }
        if (isCapital) {
            mesh.add(Models.getCapital())
        }
    }
    this.handle = function (unitId, stop) {
        if (stop) {
            var unitId = -1
        }

        if (unitId) {
            WebSocketSendGame.production(castle.id, unitId)
        }
    }
    this.changeProduction = function (unitId) {
        if (this.getProductionId()) {
            for (var i in mesh.children) {
                if (mesh.children[i].name == 'production') {
                    mesh.remove(mesh.children[i])
                }
            }
        }

        this.setProductionId(unitId)

        if (unitId) {
            mesh.add(GameModels.getProduction(Unit.getName(unitId)))
        }
    }
    this.addUnit = function (i, name) {
        var c = countProperties(this.getProduction()) / 2

        units.push(GameModels.addUnit(
            this.getX() + i - c + 0.5,
            this.getY() + i - c + 0.5,
            name))
    }
    this.removeUnits = function () {
        for (var i in units) {
            GameScene.remove(units[i])
        }
        units = []
    }

    mesh = GameModels.getCastle(this, color, isCapital)
    if (this.getProductionId()) {
        mesh.add(GameModels.getProduction(Unit.getName(this.getProductionId())))
    }
    GameScene.add(mesh)
}
