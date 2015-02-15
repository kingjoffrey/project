var Units = new function () {
    var units
    this.init = function (u) {
        units = u
    }
    this.get = function (unitId) {
        if (notSet(units[unitId])) {
            throw  unitId
        }
        return units[unitId]
    }
    this.toArray = function () {
        return units
    }
    this.getFirstUnitId = function () {
        for (var unitId in units) {
            return unitId
        }
    }
}