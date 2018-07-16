var Castle = function (castle, bgC) {
    var mesh,
        bgColor = bgC,
        units = []

    this.toArray = function () {
        return castle
    }
    this.getBackgroundColor = function () {
        return bgColor
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
    this.getCapital = function () {
        return castle.capital
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
    this.getRelocationCastleId = function () {
        return castle.relocationCastleId
    }
    this.getCastleId = function () {
        return castle.id
    }
    this.getEnclaveNumber = function () {
        return castle.enclaveNumber
    }
    this.update = function (bgC) {
        bgColor = bgC
        this.setProductionId(null)
        mesh.children[0].material.color.set(bgColor)
    }
    this.setDefense = function (defense) {
        castle.defense = defense
        Models.castleChangeDefense(mesh, defense, castle.capital)
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
            console.log(mesh.children)
            mesh.children.splice(-1, 1)
        }

        this.setProductionId(unitId)

        if (unitId) {
            mesh.add(GameModels.addProduction(Unit.getName(unitId)))
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

    mesh = GameModels.addCastle(this, bgColor)
}
