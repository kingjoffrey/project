var Ruins = new function () {
    var ruins = {}
    this.init = function (ruins) {
        for (var ruinId in ruins) {
            this.add(ruinId, new Ruin(ruins[ruinId]))
        }
    }
    this.add = function (ruinId, ruin) {
        if (ruin instanceof Ruin) {
            ruins[ruinId] = ruin
            Fields.get(ruin.getX(), ruin.getY()).setRuinId(ruinId)
        } else {
            ruins[ruinId] = new Ruin(ruin)
            Fields.get(ruin.x, ruin.y).setRuinId(ruinId)
        }
    }
    this.get = function (ruinId) {
        return ruins[ruinId]
    }
    this.delete = function (ruinId) {
        delete ruins[ruinId]
    }
    this.clear = function (ruinId) {
        Scene.remove(ruins[ruinId].getMesh())
        delete ruins[ruinId]
    }
    this.handle = function (r) {
        this.get(r.ruin.ruinId).update(r.ruin.empty)
        switch (r.find[0]) {
            case 'gold':
                if (CommonMe.colorEquals(r.color)) {
                    Sound.play('gold1');
                    CommonMe.goldIncrement(r.find[1])
                    Message.simple(translations.ruins, translations.youHaveFound + ' ' + r.find[1] + ' ' + translations.gold);
                    CommonMe.getArmies().get(r.army.id).update(r.army) // zerowanie ruchów herosa
                }
                break;
            case 'death':
                var army = Players.get(r.color).getArmies().get(r.army.id)
                army.setNumberOfUnits(r.army)
                if (army.getNumberOfUnits() > 1) {
                    army.update(r.army)
                } else {
                    Players.get(r.color).getArmies().destroy(r.army.id)
                }
                if (CommonMe.colorEquals(r.color)) {
                    Sound.play('death');
                    Message.simple(translations.ruins, translations.youHaveFound + ' ' + translations.death)
                }
                break
            case 'allies':
                Players.get(r.color).getArmies().get(r.army.id).update(r.army)
                if (CommonMe.colorEquals(r.color)) {
                    Sound.play('allies');
                    Message.simple(translations.ruins, r.find[1] + ' ' + translations.alliesJoinedYourArmy);
                }
                break
            case 'null':
                if (CommonMe.colorEquals(r.color)) {
                    Sound.play('click');
                    Message.simple(translations.ruins, translations.youHaveFoundNothing)
                    CommonMe.getArmies().get(r.army.id).update(r.army) // zerowanie ruchów herosa
                }
                break
            case 'empty':
                if (CommonMe.colorEquals(r.color)) {
                    Sound.play('error');
                    Message.simple(translations.ruins, translations.ruinsAreEmpty)
                    CommonMe.getArmies().get(r.army.id).update(r.army) // zerowanie ruchów herosa
                }
                break;
        }
    }
    this.toArray = function () {
        return ruins
    }
}
