var Player = function (player, color) {
    var armies = new Armies(),
        castles = new Castles(),
        towers = new Towers()

    armies.init(player.armies, player.backgroundColor, player.miniMapColor, player.textColor, color)
    castles.init(player.backgroundColor, player.miniMapColor, player.textColor, color)
    towers.init(player.towers, player.backgroundColor, color)

    this.getTeam = function () {
        return player.teamId
    }
    this.getBackgroundColor = function () {
        return player.backgroundColor
    }
    this.getTextColor = function () {
        return player.textColor
    }
    /**
     *
     * @returns {Armies}
     */
    this.getArmies = function () {
        return armies
    }
    /**
     *
     * @returns {Castles}
     */
    this.getCastles = function () {
        return castles
    }
    /**
     *
     * @returns {Towers}
     */
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
    this.getLost = function () {
        return player.lost
    }
    this.isCapital = function (castleId) {
        return (player.capitalId == castleId)
    }
    this.setCapitalId = function (castleId) {
        player.capitalId = castleId
    }
    this.getCapitalId = function () {
        return player.capitalId
    }

    for (var castleId in player.castles) {
        castles.add(castleId, player.castles[castleId], this.isCapital(castleId))
    }

}