var Unit = new function () {
    //this.getId = function (name) {
    //    for (var unitId in Units.get) {
    //        if (Units.get(unitId) != null && Units.get(unitId).name == name) {
    //            return Units[i].mapUnitId;
    //        }
    //    }
    //
    //    return null;
    //}
    this.getImage = function (unitId, color) {
        return '/img/game/units/' + color + '/' + Units.get(unitId).name.replace(' ', '_').toLowerCase() + '.png'
    }
}