var Armies = function () {
    var armies = {},
        bgColor,
        miniMapColor,
        textColor

    this.init = function (armies, bgC, miniMapC, textC) {
        bgColor = bgC
        miniMapColor = miniMapC
        textColor = textC
        for (var armyId in armies) {
            this.add(armyId, armies[armyId])
        }
    }
    this.add = function (armyId, army) {
        armies[armyId] = new Army(army, bgColor, miniMapColor, textColor)
    }
    this.update = function (armyId, army) {
        armies[armyId].update(army)
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
    this.delete = function (armyId, quiet) {
        if (notSet(armies[armyId])) {
            throw ('Brak armi o armyId = ' + armyId );
            return
        }

        this.fields(armies[armyId])

        if (!quiet) {
            Zoom.lens.setcenter(armies[armyId].getX(), armies[armyId].getY())
        }

        var mesh = Three.getScene().getObjectById(this.get(armyId).getMeshId())
        EventsControls.detach(mesh)
        Three.getScene().remove(mesh)
        $('#' + armyId).remove()
        delete armies[armyId]
    }
}