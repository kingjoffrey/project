var Castle = function (castle, bgC, miniMapColor, textColor) {
    var mesh = Three.addCastle(castle, bgC),
        bgColor = bgC

    map.append(
        $('<div>').css({
            'left': castle.x * 2 + 'px',
            'top': castle.y * 2 + 'px',
            'background': miniMapColor,
            'border-color': textColor
        })
            .attr('id', 'c' + castle.id)
            .addClass('c')
    )

    this.toArray = function () {
        return castle
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
        return castle.income
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
    this.update = function (bgC, miniMapColor, textColor) {
        bgColor = bgC
        this.setProductionId(null)
        $('#c' + castle.id).css({
            'background': miniMapColor,
            'border-color': textColor
        })
        mesh.children[0].material.color.set(bgColor)
    }
    this.handle = function (stop, relocation) {
        var unitId = $('input:radio[name=production]:checked').val()

        if (relocation) {
            if (!unitId) {
                Message.error(translations.noUnitSelected)
            } else {
                var id = Message.simple(translations.relocation, translations.selectCastleToWhichYouWantToRelocateThisProduction)
                Message.cancel(id, function () {
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
            WebSocketGame.production(castle.id, unitId)
            return
        }
    }
    this.setDefense = function (defense) {
        castle.defense = defense
        Three.castleChangeDefense(mesh, defense)
    }
}
