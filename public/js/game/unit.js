var Unit = new function () {
    this.getName = function (unitId) {
        return Units.get(unitId).name.replace(' ', '_').toLowerCase()
    }
}
