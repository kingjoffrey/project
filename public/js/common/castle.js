var Castle = function (castle, bgC) {
    var mesh = GameModels.addCastle(castle, bgC),
        bgColor = bgC

    this.toArray = function () {
        return castle
    }
    this.getBackgroundColor = function () {
        return bgColor
    }
    this.getMesh = function () {
        return mesh
    }
    this.setProductionId = function (value) {
        castle.productionId = value
    }
    this.setProductionTurn = function (value) {
        castle.productionTurn = value
    }
    this.setRelocationCastleId = function (value) {
        castle.relocationCastleId = value
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
        Models.castleChangeDefense(mesh, defense)
    }
    this.handle = function (unitId, stop, relocation) {
        if (relocation) {
            if (!unitId) {
                Message.error(translations.noUnitSelected)
            } else {
                var id = Message.simple(translations.relocation, translations.selectCastleToWhichYouWantToRelocateThisProduction)
                Message.addButton(id, 'cancel', function () {
                    Me.setSelectedCastleId(0)
                })
                Me.setSelectedCastleId(castle.id)
                Me.setSelectedUnitId(unitId)
            }
            return
        }

        if (stop) {
            var unitId = -1
        }

        if (unitId) {
            WebSocketSendGame.production(castle.id, unitId)
        }
    }
}
