var Armies = new function () {
    var armies = {}
    this.init = function (armies, bgColor, miniMapColor, textColor) {
        for (var armyId in armies) {
            //if (color == game.me.color) {
            //    for (s in player.armies[i].soldiers) {
            //        game.me.costs += game.units[player.armies[i].soldiers[s].unitId].cost;
            //    }
            //    myArmies = true;
            //} else {
            //    enemyArmies = true;
            //}
            this.add(armyId, armies[armyId], bgColor, miniMapColor, textColor)
        }
    }
    this.add = function (armyId, army, bgColor, miniMapColor, textColor) {
        armies[armyId] = new Army(army, bgColor, miniMapColor, textColor)
    }
    this.get = function (armyId) {
        return armies[armyId]
    }
}