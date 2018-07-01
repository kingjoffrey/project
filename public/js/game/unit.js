var Unit = new function () {
    this.getName = function (unitId) {
        return Units.get(unitId).name.replace(' ', '_').toLowerCase()
    }
    this.countNumberOfUnits = function (a) {
        var numberOfUnits = countProperties(a.heroes) + countProperties(a.walk) + countProperties(a.swim) + countProperties(a.fly)

        if (numberOfUnits > 10) {
            numberOfUnits = 10
        }
        return numberOfUnits
    }
    this.convertName = function (name) {
        return name.replace(' ', '_').toLowerCase()
    }
}
