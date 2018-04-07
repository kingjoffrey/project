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
    this.countHumanss = function () {
        var numberOfHumans = 0
        for (var color in players) {
            if (color == 'neutral') {
                continue
            }
            var player = this.get(color)
            if (!player.isComputer()) {
                console.log('a')
                numberOfHumans++
            }
        }
        return numberOfHumans
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
            GameScene.centerOn(castle.getX(), castle.getY(), func)
        } else if (firstCastleId = castles.getFirsCastleId()) {
            var castle = castles.get(firstCastleId)
            GameScene.centerOn(castle.getX(), castle.getY(), func)
        } else {
            var armies = player.getArmies()
            for (var armyId in armies.toArray()) {
                var army = armies.get(armyId)
                GameScene.centerOn(army.getX(), army.getY(), func)
                break
            }
        }
    }
}
