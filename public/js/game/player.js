var Player = function (player) {
    var armies = Armies,
        castles = Castles,
        towers = Towers,
        team = player.team,
        backgroundColor = player.backgroundColor,
        miniMapColor = player.miniMapColor,
        textColor = player.textColor

//console.log(player)

    armies.init(player.armies, backgroundColor, miniMapColor, textColor)
    castles.init(player.castles, backgroundColor, miniMapColor, textColor)
    towers.init(player.towers, backgroundColor)

    this.getTeam = function () {
        return team
    }
    this.getBackgroundColor = function () {
        return backgroundColor
    }
    this.getArmies = function () {
        return armies
    }
    this.getCastles = function () {
        return castles
    }
}