var Armies = function () {
    var armies = {}

    this.init = function (armies, bgColor, miniMapColor, textColor) {
        for (var armyId in armies) {
            this.add(armyId, armies[armyId], bgColor, miniMapColor, textColor)
        }
    }
    this.add = function (armyId, army, bgColor, miniMapColor, textColor) {
        armies[armyId] = new Army(army, bgColor, miniMapColor, textColor)
    }
    this.get = function (armyId) {
        return armies[armyId]
    }
    this.attachEventsControls = function () {
        for (var armyId in armies) {
            EventsControls.attach(Three.getScene().getObjectById(this.get(armyId).getMeshId()))
        }
    }

    this.toArray = function () {
        return armies
    }
}