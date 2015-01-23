var Player = function (player) {
    var armies = Armies,
        castles = Castles,
        towers = {}

    armies.init(player.armies)



    for (towerId in player.towers) {
        //Tower.create(towerId, color)
        if (color == game.me.color) {
            game.me.income += 5
        }
    }
}