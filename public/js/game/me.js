var Me = new function () {
    var gold = 0,
        upkeep = 0,
        income = 0,
        color,
        selectedArmyId = null,
        deselectedArmyId = null,
        nextArmies = {},
        skippedArmies = {},
        quitedArmies = {},
        isSelected = 0,
        parentArmyId = null,
        nextArmyId = null,
        isNextSelected = null,
        me,
        selectedCastleId = null,
        selectedUnitId = null,
        battleSequence = [],
        capitalId = null,
        updateGold = function () {
            $('#gold #value').fadeOut(300, function () {
                $('#gold #value').html(gold)
                $('#gold #value').fadeIn()
                if (gold > 1000) {
                    $('#heroHire').removeClass('buttonOff')
                } else {
                    $('#heroHire').addClass('buttonOff')
                }
            })

        },
        updateUpkeep = function () {
            $('#costs #value').fadeOut(300, function () {
                $('#costs #value').html(upkeep)
                $('#costs #value').fadeIn(300)
            })
        },
        updateIncome = function () {
            $('#income #value').fadeOut(300, function () {
                $('#income #value').html(income)
                $('#income #value').fadeIn(300)
            })
        }

    this.getColor = function () {
        return color
    }
    this.colorEquals = function (value) {
        return color == value
    }
    this.setGold = function (value) {
        gold = value
        updateGold()
    }
    this.setIncome = function (value) {
        income = value
        updateIncome()
    }
    this.setUpkeep = function (value) {
        upkeep = value
        updateUpkeep()
    }
    this.goldIncrement = function (value) {
        gold += value
        updateGold()
    }
    this.upkeepIncrement = function (value) {
        upkeep += value
        if (upkeep < 0) {
            upkeep = 0
        }
        updateUpkeep()
    }
    this.incomeIncrement = function (value) {
        income += value
        updateIncome()
    }
    this.getGold = function () {
        return gold
    }
    this.countCastles = function () {
        return me.getCastles().count()
    }
    /**
     *
     * @param castleId
     * @returns {Castle}
     */
    this.getCastle = function (castleId) {
        return me.getCastles().get(castleId)
    }
    /**
     *
     * @param armyId
     * @returns Army
     */
    this.getArmy = function (armyId) {
        return me.getArmies().get(armyId)
    }
    this.setSelectedCastleId = function (castleId) {
        selectedCastleId = castleId
    }
    this.setSelectedUnitId = function (unitId) {
        selectedUnitId = unitId
    }
    this.getSelectedCastleId = function () {
        return selectedCastleId
    }
    this.getSelectedUnitId = function () {
        return selectedUnitId
    }
    this.resetSkippedArmies = function () {
        skippedArmies = {}
    }
    this.getSelectedArmyId = function () {
        return selectedArmyId
    }
    this.getDeselectedArmyId = function () {
        return deselectedArmyId
    }
    this.setParentArmyId = function (armyId) {
        parentArmyId = armyId
    }
    this.selectArmy = function (armyId, center) {
        var army = this.getArmy(armyId),
            number,
            i = 0

        GameModels.addArmyCircle(army.getX(), army.getY(), army.getBackgroundColor())
        if (army.getHeroKey()) {
            $('#terrain').html(army.getHero(army.getHeroKey()).name)
        }
        Message.remove()

        this.removeFromSkipped(armyId)
        this.deleteQuited(armyId)

        $('#armyStatus').removeClass('buttonOff')

        if (notSet(center)) {
            GameScene.centerOn(army.getX(), army.getY(), function () {
                selectedArmyId = armyId
            })
        } else {
            selectedArmyId = armyId
        }
        AStar.showRange(army)
    }
    this.deselectArmy = function (skipJoin) {
        if (notSet(skipJoin) && parentArmyId && selectedArmyId) {
            var selectedArmy = this.getArmy(selectedArmyId),
                parentArmy = this.getArmy(parentArmyId)
            parentArmy.getX()
            if (selectedArmy.getX() == parentArmy.getX() && selectedArmy.getY() == parentArmy.getY()) {
                WebSocketSendGame.join(selectedArmyId)
            }
        }
        parentArmyId = null

        GameModels.clearArmyCircles()
        GameModels.clearPathCircles()
        this.setIsSelected(0)
        this.armyButtonsOff()
    }
    this.armyButtonsOff = function () {
        if (selectedArmyId) {
            deselectedArmyId = selectedArmyId
        }
        selectedArmyId = null
        $('.path').remove();
        $('#deselectArmy').addClass('buttonOff');
        $('#armyStatus').addClass('buttonOff');
    }
    this.removeFromSkipped = function (armyId) {
        if (isTruthful(skippedArmies[armyId])) {
            delete skippedArmies[armyId]
        }
    }
    this.skip = function () {
        if (!Turn.isMy()) {
            return
        }

        if (GameGui.lock) {
            return
        }

        var armyId = this.getSelectedArmyId()
        if (armyId) {
            Sound.play('skip')
            skippedArmies[armyId] = 1
            this.deselectArmy()
            this.findNext()
        }
    }
    this.findNext = function () {
        if (!Turn.isMy()) {
            return
        }

        if (GameGui.lock) {
            return
        }

        var armyId = this.getSelectedArmyId()
        if (armyId) {
            nextArmies[armyId] = true
        }

        this.deselectArmy()
        var armies = me.getArmies().toArray()

        for (var armyId in armies) {
            if (armies[armyId].getMoves() == 0) {
                continue
            }

            if (isTruthful(skippedArmies[armyId])) {
                continue
            }

            if (isTruthful(quitedArmies[armyId])) {
                continue
            }

            if (isTruthful(nextArmies[armyId])) {
                continue
            }

            //reset = false
            nextArmies[armyId] = true
            this.selectArmy(armyId)
            return
        }

        if ($.isEmptyObject(nextArmies)) {
            Sound.play('error');
            var id = Message.show(translations.nextArmy, $('<div>').html(translations.thereIsNoFreeArmyWithSpareMovePoints))
            Message.cancel(id)
            Message.ok(id, function () {
                WebSocketSendGame.nextTurn()
            })
        } else {
            this.deselectArmy()
            nextArmies = {}
            this.findNext()
        }
    }
    this.addQuited = function (armyId) {
        quitedArmies[armyId] = 1
    }
    this.deleteQuited = function (armyId) {
        if (isTruthful(quitedArmies[armyId])) {
            WebSocketSendGame.unfortify(armyId, 0)
            delete quitedArmies[armyId]
        }
    }
    this.isSelected = function () {
        return isSelected
    }
    this.setIsSelected = function (value) {
        isSelected = value
    }
    this.armyClick = function (armyId) {
        if (GameGui.lock) {
            return
        }

        if (!Turn.isMy()) {
            return
        }

        Sound.play('slash')
        this.selectArmy(armyId, 0)
    }
    this.findFirst = function () {
        if (Me.getArmies().count()) {
            Me.findNext()
        }
    }
    this.turnOn = function () {
        this.resetSkippedArmies()
        if (Turn.isMy() && Turn.getNumber() == 1 && !this.getCastle(this.getFirsCastleId()).getProductionId()) {
            CastleWindow.show(this.getCastle(this.getFirsCastleId()))
        } else {
            var id = Message.simple(translations.yourTurn, translations.thisIsYourTurnNow)
            Message.ok(id, Me.findFirst)
        }
        GameGui.unlock()
        GameGui.titleBlink(translations.yourTurn)
    }
    this.turnOff = function () {
        this.deselectArmy()
        $('#nextTurn').addClass('buttonOff')
        $('#nextArmy').addClass('buttonOff')
        //makeMyCursorLock();
    }
    this.getTurnActive = function () {
        return me.getTurnActive()
    }
    this.getFirsCastleId = function () {
        return me.getCastles().getFirsCastleId()
    }
    this.getMyCastleDefenseFromPosition = function (x, y) {
        var castleId = Fields.get(x, y).getCastleId()
        if (castleId) {
            var castle = this.getCastle(castleId)
            if (castle) {
                return castle.getDefense()
            }
        }
    }
    this.getSelectedHeroSplitKey = function () {
        return this.getArmy(this.getSelectedArmyId()).getHeroSplitKey()
    }
    this.getSelectedSoldierSplitKey = function () {
        return this.getArmy(this.getSelectedArmyId()).getSoldierSplitKey()
    }
    /**
     *
     * @returns {*|Armies}
     */
    this.getArmies = function () {
        return me.getArmies()
    }
    /**
     *
     * @returns {*|Castles}
     */
    this.getCastles = function () {
        return me.getCastles()
    }
    /**
     *
     * @returns {*|Towers}
     */
    this.getTowers = function () {
        return me.getTowers()
    }
    /**
     *
     * @returns {Army}
     */
    this.getSelectedArmy = function () {
        return this.getArmy(selectedArmyId)
    }
    this.sameTeam = function (color) {
        if (me.getTeam() == Players.get(color).getTeam()) {
            return true
        }
    }
    this.getBattleSequence = function (type) {
        return battleSequence[type]
    }
    this.setBattleSequence = function (bSequence) {
        this.setAttackBattleSequence(bSequence['attack'])
        this.setDefenseBattleSequence(bSequence['defense'])
    }
    this.setAttackBattleSequence = function (bSequence) {
        battleSequence['attack'] = bSequence
    }
    this.setDefenseBattleSequence = function (bSequence) {
        battleSequence['defense'] = bSequence
    }
    this.disband = function () {
        var id = Message.show(translations.Disbandarmy, $('<div>').html(translations.areYouSure))
        Message.ok(id, WebSocketSendGame.disband)
        Message.cancel(id)
    }
    this.findHero = function () {
        for (var armyId in this.getArmies().toArray()) {
            var heroId = this.getArmy(armyId).getHeroKey()
            if (heroId) {
                return heroId
            }
        }
    }
    /**
     *
     * @param castleId
     * @returns {Castle}
     */
    this.findNextCastle = function (castleId) {
        var start = false
        for (var id in this.getCastles().toArray()) {
            if (start) {
                return this.getCastle(id)
            }
            if (id == castleId) {
                start = true
            }
        }
    }
    this.findPreviousCastle = function (castleId) {
        var start = false,
            arr = []

        for (var key in this.getCastles().toArray()) {
            arr.push(key)
        }
        for (var i = arr.length - 1; i >= 0; i--) {
            if (start) {
                return this.getCastle(arr[i])
            }
            if (arr[i] == castleId) {
                start = true
            }
        }
    }
    this.setCapitalId = function (id) {
        capitalId = id
    }
    this.getCapitalId = function () {
        return capitalId
    }
    this.init = function (c, g, bSequence, capitals) {
        color = c
        me = Players.get(color)
        capitalId = capitals[color]

        this.setBattleSequence(bSequence)
        this.setGold(g)
        for (var armyId in Players.get(color).getArmies().toArray()) {
            var army = Players.get(color).getArmies().get(armyId)
            if (army.getFortified()) {
                this.addQuited(armyId)
            }
        }
        var armies = this.getArmies()
        for (var armyId in armies.toArray()) {
            var army = armies.get(armyId)
            for (var soldierId in army.getWalkingSoldiers()) {
                this.upkeepIncrement(Units.get(army.getWalkingSoldier(soldierId).unitId).cost)
            }
            for (var soldierId in army.getFlyingSoldiers()) {
                this.upkeepIncrement(Units.get(army.getFlyingSoldier(soldierId).unitId).cost)
            }
            for (var soldierId in army.getSwimmingSoldiers()) {
                this.upkeepIncrement(Units.get(army.getSwimmingSoldier(soldierId).unitId).cost)
            }
        }
        var castles = this.getCastles()
        for (var castleId in castles.toArray()) {
            this.incomeIncrement(castles.get(castleId).getIncome())
        }
        this.incomeIncrement(this.getTowers().count() * 5)
        updateGold()
        updateUpkeep()
        updateIncome()
    }
}