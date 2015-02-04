var Player = function (player) {
    var armies = new Armies(),
        castles = new Castles(),
        towers = new Towers(),
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
    this.isComputer = function () {
        return player.computer
    }
}