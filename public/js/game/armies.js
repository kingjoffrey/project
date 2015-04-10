var Armies = function () {
    var armies = {},
        bgColor,
        miniMapColor,
        textColor,
        color

    this.init = function (armies, bgC, miniMapC, textC, c) {
        bgColor = bgC
        miniMapColor = miniMapC
        textColor = textC
        color = c
        for (var armyId in armies) {
            this.add(armyId, armies[armyId])
        }
    }
    this.add = function (armyId, army) {
        armies[armyId] = new Army(army, bgColor, miniMapColor, textColor, color)
        Fields.get(army.x, army.y).addArmyId(armyId, color)
    }
    /**
     *
     * @param armyId
     * @returns {Army}
     */
    this.get = function (armyId) {
        return armies[armyId]
    }
    this.toArray = function () {
        return armies
    }
    this.delete = function (armyId, show) {
        if (!this.hasArmy(armyId)) {
            throw ('Brak armi o armyId = ' + armyId );
            return
        }
        var army = this.get(armyId)
        Fields.get(army.getX(), army.getY()).removeArmyId(armyId)

        if (isTruthful(show)) {
            console.log('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa')
            Zoom.lens.setcenter(army.getX(), army.getY())
        }

        Three.getScene().remove(army.getMesh())
        $('#' + armyId).remove()
        delete armies[armyId]
    }
    this.hasArmy = function (armyId) {
        return isSet(armies[armyId])
    }
    this.handle = function (army) {
        if (this.hasArmy(army.id)) {
            armies[army.id].update(army)
        } else {
            this.add(army.id, army)
        }
    }
    this.count = function () {
        var i = 0
        for (var armyId in armies) {
            i++
        }
        return i
    }
}