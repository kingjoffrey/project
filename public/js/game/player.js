var Player = function (player) {
    var armies = new Armies(),
        castles = new Castles(),
        towers = new Towers()

    armies.init(player.armies, player.backgroundColor, player.miniMapColor, player.textColor)
    castles.init(player.castles, player.backgroundColor, player.miniMapColor, player.textColor)
    towers.init(player.towers, player.backgroundColor)

    this.getTeam = function () {
        return player.team
    }
    this.getBackgroundColor = function () {
        return player.backgroundColor
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
    this.getTurnActive = function () {
        return player.turnActive
    }
}