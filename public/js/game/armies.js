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
    }
    this.update = function (armyId, army) {
        armies[armyId].update(army)
    }
    this.get = function (armyId) {
        return armies[armyId]
    }
    //this.attachPicker = function () {
    //    for (var armyId in armies) {
    //        Picker.attach(Three.getScene().getObjectById(this.get(armyId).getMeshId()))
    //    }
    //}

    this.toArray = function () {
        return armies
    }
    this.delete = function (armyId, quiet) {
        if (!this.hasArmy(armyId)) {
            throw ('Brak armi o armyId = ' + armyId );
            return
        }

        Fields.get(armies[armyId].getX(), armies[armyId].getY()).removeArmyId(armyId)

        if (isTruthful(quiet)) {
            Zoom.lens.setcenter(armies[armyId].getX(), armies[armyId].getY())
        }

        var mesh = Three.getScene().getObjectById(this.get(armyId).getMeshId())
        //Picker.detach(mesh)
        Three.getScene().remove(mesh)
        $('#' + armyId).remove()
        delete armies[armyId]
    }
    this.hasArmy = function (armyId) {
        return isSet(armies[armyId])
    }
    this.computerLoop = function (a) {
        for (var armyId in a) {
            this.handle(a[armyId])
        }
        Websocket.computer()
    }
    this.handle = function (army) {
        if (this.hasArmy(army.armyId)) {
            armies[army.armyId].update(army)
        } else {
            this.add(army.armyId, army)
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