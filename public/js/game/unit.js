var Unit = new function () {
    this.getName = function (unitId) {
        return Units.get(unitId).name.replace(' ', '_').toLowerCase()
    }
    this.countNumberOfUnits = function (a) {
        var numberOfUnits = countProperties(a.heroes) + countProperties(a.walk) + countProperties(a.swim) + countProperties(a.fly)

        if (numberOfUnits > 8) {
            numberOfUnits = 8
        }
        return numberOfUnits
    }
}
