var Ruins = new function () {
    var ruins

    this.init = function (r) {
        ruins = {}

        for (var ruinId in r) {
            this.add(ruinId, new Ruin(r[ruinId]))
        }
    }
    this.add = function (ruinId, ruin) {
        ruins[ruinId] = ruin
        Fields.get(ruin.getX(), ruin.getY()).setRuinId(ruinId)
    }
    /**
     *
     * @param ruinId
     * @returns {Ruin}
     */
    this.get = function (ruinId) {
        return ruins[ruinId]
    }
    this.delete = function (ruinId) {
        delete ruins[ruinId]
    }
    this.clear = function (ruinId) {
        GameScene.remove(ruins[ruinId].getMesh())
        delete ruins[ruinId]
    }
    this.handle = function (r) {
        this.get(r.ruin.ruinId).update(r.ruin.empty)
        switch (r.find[0]) {
            case 'gold':
                if (Me.colorEquals(r.color)) {
                    Sound.play('gold1');
                    Me.goldIncrement(r.find[1])
                    Message.simple(translations.ruins, translations.youHaveFound + ' ' + r.find[1] + ' ' + translations.gold);
                    Me.getArmies().get(r.army.id).update(r.army) // zerowanie ruchów herosa
                }
                break;
            case 'death':
                var army = Players.get(r.color).getArmies().get(r.army.id)
                var numberOfUnits = Unit.countNumberOfUnits(r.army)
                if (numberOfUnits > 1) {
                    console.log(numberOfUnits)
                    army.update(r.army)
                } else {
                    console.log('dupa')
                    Players.get(r.color).getArmies().destroy(r.army.id)
                }
                if (Me.colorEquals(r.color)) {
                    Sound.play('death');
                    Message.simple(translations.ruins, translations.youHaveFound + ' ' + translations.death)
                }
                break
            case 'allies':
                Players.get(r.color).getArmies().get(r.army.id).update(r.army)
                if (Me.colorEquals(r.color)) {
                    Sound.play('allies');
                    Message.simple(translations.ruins, r.find[1] + ' ' + translations.alliesJoinedYourArmy);
                }
                break
            case 'null':
                if (Me.colorEquals(r.color)) {
                    Sound.play('click');
                    Message.simple(translations.ruins, translations.youHaveFoundNothing)
                    Me.getArmies().get(r.army.id).update(r.army) // zerowanie ruchów herosa
                }
                break
            case 'empty':
                if (Me.colorEquals(r.color)) {
                    Sound.play('error');
                    Message.simple(translations.ruins, translations.ruinsAreEmpty)
                    Me.getArmies().get(r.army.id).update(r.army) // zerowanie ruchów herosa
                }
                break;
        }
    }
    this.toArray = function () {
        return ruins
    }
}
