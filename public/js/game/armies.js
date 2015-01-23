var Armies = new function () {
    var armies = {}
    this.init = function (armies) {
        for (var armyId in armies) {
            //if (color == game.me.color) {
            //    for (s in player.armies[i].soldiers) {
            //        game.me.costs += game.units[player.armies[i].soldiers[s].unitId].cost;
            //    }
            //    myArmies = true;
            //} else {
            //    enemyArmies = true;
            //}
            this.add(armyId, armies[armyId])
        }
    }
    this.add = function (armyId, army) {
        armies[armyId] = new Army(army)
    }
}