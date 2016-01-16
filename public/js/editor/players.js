var Players = new function () {
    var players = {}

    this.init = function (players) {
        for (var color in players) {
            this.add(color, players[color])
        }
    }
    this.add = function (color, player) {
        players[color] = new Player(player, color)
    }
    /**
     *
     * @param color
     * @returns Player
     */
    this.get = function (color) {
        return players[color]
    }
    this.count = function () {
        return Object.size(players) - 1
    }
    this.countHumans = function () {
        var numberOfHumans = 0
        for (var color in players) {
            if (color == 'neutral') {
                continue
            }
            var player = this.get(color)
            if (!player.isComputer()) {
                numberOfHumans++
            }
        }
        return numberOfHumans;
    }
    this.toArray = function () {
        return players
    }
    this.showFirst = function (color, func) {
        var castleId = Game.getCapitalId(color),
            firstCastleId,
            player = this.get(color),
            castles = player.getCastles()

        if (castles.has(castleId)) {
            var castle = castles.get(castleId)
            Zoom.lens.setcenter(castle.getX(), castle.getY(), func)
        } else if (firstCastleId = castles.getFirsCastleId()) {
            var castle = castles.get(firstCastleId)
            Zoom.lens.setcenter(castle.getX(), castle.getY(), func)
        } else {
            var armies = player.getArmies()
            for (var armyId in armies.toArray()) {
                var army = armies.get(armyId)
                Zoom.lens.setcenter(army.getX(), army.getY(), func)
                break
            }
        }
    }
}

Object.size = function (obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

