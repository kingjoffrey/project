var Player = function (player, color) {
    var armies = new Armies(),
        castles = new Castles(),
        towers = new Towers()

    armies.init(player.armies, player.backgroundColor, player.miniMapColor, player.textColor, color)
    castles.init(player.castles, player.backgroundColor, player.miniMapColor, player.textColor, color)
    towers.init(player.towers, player.backgroundColor, color)

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
    this.getTowers = function () {
        return towers
    }
    this.isComputer = function () {
        return player.computer
    }
    this.getTurnActive = function () {
        return player.turnActive
    }
    this.getLongName = function () {
        return player.longName
    }
}