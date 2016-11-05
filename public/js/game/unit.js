var Unit = new function () {
    this.getImage = function (unitId, color) {
        // return '/img/game/units/' + color + '/' + Units.get(unitId).name.replace(' ', '_').toLowerCase() + '.png'
        return '/img/game/killed.png'
    }
    this.getName = function (unitId) {
        return Units.get(unitId).name.replace(' ', '_').toLowerCase()
    }
}