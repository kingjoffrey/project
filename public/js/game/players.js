var Players = new function () {
    var players

    this.init = function (p) {
        players = {}

        for (var color in p) {
            this.add(color, p[color])
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
        return countProperties(players) - 1
    }
    this.toArray = function () {
        return players
    }
    this.showFirst = function (color, func) {
        var capitalId = Game.getCapitalId(color),
            firstCastleId,
            player = this.get(color),
            castles = player.getCastles()

        if (castles.has(capitalId)) {
            var castle = castles.get(capitalId)
            GameScene.centerOn(castle.getX() + 1, castle.getY() + 1, func)
        } else if (firstCastleId = castles.getFirsCastleId()) {
            var castle = castles.get(firstCastleId)
            GameScene.centerOn(castle.getX() + 1, castle.getY() + 1, func)
        } else {
            var armies = player.getArmies()
            for (var armyId in armies.toArray()) {
                var army = armies.get(armyId)
                GameScene.centerOn(army.getX(), army.getY(), func)
                break
            }
        }
    }
    this.hideArmies = function () {
        for (var color in players) {
            var player = players[color],
                armies = player.getArmies()

            for (var armyId in armies.toArray()) {
                GameScene.remove(armies.get(armyId).getMesh())
            }
        }
    }
}
