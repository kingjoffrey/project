var Player = function (player) {
    var armies = Armies,
        castles = Castles,
        towers = Towers

    armies.init(player.castles)
    castles.init(player.castles)
    towers.init(player.towers)
}