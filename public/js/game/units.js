var Units = new function () {
    var units
    this.init = function (u) {
        units = u
    }
    this.get = function (unitId) {
        return units[unitId]
    }
}