var Armies = function () {
    var armies,
        bgColor,
        miniMapColor,
        textColor,
        color

    this.init = function (a, bgC, miniMapC, textC, c) {
        armies = {}

        bgColor = bgC
        miniMapColor = miniMapC
        textColor = textC
        color = c
        for (var armyId in a) {
            this.add(armyId, a[armyId])
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
    this.destroy = function (armyId) {
        if (!this.hasArmy(armyId)) {
            throw ('Brak armi o armyId = ' + armyId );
            return
        }
        var army = this.get(armyId)
        Fields.get(army.getX(), army.getY()).removeArmyId(armyId)

        GameScene.remove(army.getMesh())
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