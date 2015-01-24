var Player = function (player) {
    var armies = Armies,
        castles = Castles,
        towers = Towers,
        team = player.team,
        backgroundColor = player.backgroundColor

    armies.init(player.castles, backgroundColor)
    castles.init(player.castles, backgroundColor)
    towers.init(player.towers, backgroundColor)

    this.getTeam = function () {
        return team
    }
    this.getBackgroundColor = function () {
        return backgroundColor
    }
}