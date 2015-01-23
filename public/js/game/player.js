var Player = function (player) {
    Armies.init(player.armies)

    for (var castleId in player.castles) {
        Castle.createWithColor(castleId, color)
        if (color == game.me.color) {
            game.me.income += player.castles[castleId].income;
            if (firstCastleId > castleId) {
                firstCastleId = castleId;
            }
            myCastles = true;
            Castle.initMyProduction(castleId);
        } else {
            enemyCastles = true;
        }
        Castle.updateDefense(castleId, player.castles[castleId]);
        //Castle.owner(i, color);
    }

    for (towerId in player.towers) {
        //Tower.create(towerId, color)
        if (color == game.me.color) {
            game.me.income += 5
        }
    }
}