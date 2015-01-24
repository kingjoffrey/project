var Armies = new function () {
    var armies = {}
    this.init = function (armies, bgColor) {
        for (var armyId in armies) {
            //if (color == game.me.color) {
            //    for (s in player.armies[i].soldiers) {
            //        game.me.costs += game.units[player.armies[i].soldiers[s].unitId].cost;
            //    }
            //    myArmies = true;
            //} else {
            //    enemyArmies = true;
            //}
            this.add(armyId, armies[armyId], bgColor)
        }
    }
    this.add = function (armyId, army, bgColor) {
        armies[armyId] = new Army(army, bgColor)
    }
    this.get = function () {
        return armies[armyId]
    }
}