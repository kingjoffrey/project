var CommonMe = new function () {
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
        battleSequence = []

    this.init = function (c, g, bSequence) {
        color = c
        me = Players.get(color)

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
    this.getColor = function () {
        return color
    }
    this.colorEquals = function (value) {
        return color == value
    }
    var updateGold = function () {
        $('#gold #value').fadeOut(300, function () {
            $('#gold #value').html(gold)
            $('#gold #value').fadeIn()
            if (gold > 1000) {
                $('#heroHire').removeClass('buttonOff')
            } else {
                $('#heroHire').addClass('buttonOff')
            }
        })

    }
    var updateUpkeep = function () {
        $('#costs #value').fadeOut(300, function () {
            $('#costs #value').html(upkeep)
            $('#costs #value').fadeIn(300)
        })
    }
    var updateIncome = function () {
        $('#income #value').fadeOut(300, function () {
            $('#income #value').html(income)
            $('#income #value').fadeIn(300)
        })
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
            unitsBox = $('#unitsBox'),
            number,
            unitTypes = {}

        unitsBox.html('')
        if (number = countProperties(army.getHeroes())) {
            unitsBox.append($('<div>').html(number).css({'background-image': 'url(' + Hero.getImage(color) + ')'}))
        }

        for (var soldierId in army.getWalkingSoldiers()) {
            if (isSet(unitTypes[army.getWalkingSoldier(soldierId).unitId])) {
                unitTypes[army.getWalkingSoldier(soldierId).unitId]++
            } else {
                unitTypes[army.getWalkingSoldier(soldierId).unitId] = 1
            }
        }
        for (var soldierId in army.getFlyingSoldiers()) {
            if (isSet(unitTypes[army.getFlyingSoldier(soldierId).unitId])) {
                unitTypes[army.getFlyingSoldier(soldierId).unitId]++
            } else {
                unitTypes[army.getFlyingSoldier(soldierId).unitId] = 1
            }
        }
        for (var soldierId in army.getSwimmingSoldiers()) {
            if (isSet(unitTypes[army.getSwimmingSoldier(soldierId).unitId])) {
                unitTypes[army.getSwimmingSoldier(soldierId).unitId]++
            } else {
                unitTypes[army.getSwimmingSoldier(soldierId).unitId] = 1
            }
        }
        for (var unitId in unitTypes) {
            unitsBox.append($('<div>').html(unitTypes[unitId]).css({'background-image': 'url(' + Unit.getImage(unitId, color) + ')'}))
        }

        Gui.armyBoxAdjust()
        Models.addArmyCircle(army.getX(), army.getY(), army.getBackgroundColor())
        Message.remove()

        this.removeFromSkipped(armyId)
        this.deleteQuited(armyId)
        this.updateInfo(armyId)
        $('#name').html('Army')

        $('#deselectArmy').removeClass('buttonOff');
        $('#armyStatus').removeClass('buttonOff');

        if (notSet(center)) {
            //zoomer.setCenterIfOutOfScreen(a.x * 40, a.y * 40);
            MiniMap.centerOn(army.getX(), army.getY(), function () {
                selectedArmyId = armyId
            })
        } else {
            selectedArmyId = armyId
        }
    }
    this.deselectArmy = function (skipJoin) {
        Message.remove()
        if (notSet(skipJoin) && parentArmyId && selectedArmyId) {
            var selectedArmy = this.getArmy(selectedArmyId),
                parentArmy = this.getArmy(parentArmyId)
            parentArmy.getX()
            if (selectedArmy.getX() == parentArmy.getX() && selectedArmy.getY() == parentArmy.getY()) {
                WebSocketSend.join(selectedArmyId)
            }
        }
        parentArmyId = null

        $('#unitsBox').html('')
        Models.clearArmyCircles()
        Models.clearPathCircles()
        this.setIsSelected(0)
        //Castle.deselectedArmyCursor()
        //this.enemyCursorWhenUnselected()
        //Castle.myCursor()

        $('#name').html('');
        $('#moves').html('');
        $('#attack').html('');
        $('#defense').html('');

        this.armyButtonsOff()
    }
    this.armyButtonsOff = function () {
        if (selectedArmyId) {
            deselectedArmyId = selectedArmyId

            //Army.deselected.heroSplitKey = null
            //Army.deselected.soldierSplitKey = null
            //
            //Army.deselected.skippedHeroes = {};
            //Army.deselected.skippedSoldiers = {};

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

        if (Gui.lock) {
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

        if (Gui.lock) {
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
                WebSocketSend.nextTurn()
            })
        } else {
            this.deselectArmy()
            nextArmies = {}
            this.findNext()
        }
    }
    this.updateInfo = function (armyId) {
        var army = this.getArmy(armyId)
        $('#name').html(army.name);
        $('#attack').html(army.attack);
        $('#defense').html(army.defense);
        $('#moves').html(army.moves);
    }
    this.addQuited = function (armyId) {
        quitedArmies[armyId] = 1
    }
    this.deleteQuited = function (armyId) {
        if (isTruthful(quitedArmies[armyId])) {
            WebSocketSend.unfortify(armyId, 0)
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
        if (Gui.lock) {
            return
        }

        if (!Turn.isMy()) {
            return
        }

        Sound.play('slash')
        this.selectArmy(armyId, 0)
    }
    this.findFirst = function () {
        if (CommonMe.getArmies().count()) {
            CommonMe.findNext()
        }
    }
    this.turnOn = function () {
        this.resetSkippedArmies()
        if (Turn.isMy() && Turn.getNumber() == 1 && !this.getCastle(this.getFirsCastleId()).getProductionId()) {
            CastleWindow.show(this.getCastle(this.getFirsCastleId()))
        } else {
            //Players.showFirst(color)
            var id = Message.show(translations.yourTurn, translations.thisIsYourTurnNow)
            Message.ok(id, CommonMe.findFirst)
        }
        Gui.unlock()
        Gui.titleBlink(translations.yourTurn)
        this.handleHeroButtons()
    }
    this.turnOff = function () {
        this.deselectArmy()
        $('#nextTurn').addClass('buttonOff')
        $('#nextArmy').addClass('buttonOff')
        $('#heroResurrection').addClass('buttonOff')
        $('#heroHire').addClass('buttonOff')
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
        var id = Message.show(translations.disbandArmy, $('<div>').html(translations.areYouSure))
        Message.ok(id, WebSocketSend.disband)
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
    this.handleHeroButtons = function () {
        if (!this.findHero() && this.getGold() >= 100) {
            $('#heroResurrection').removeClass('buttonOff')
            $('#heroHire').addClass('buttonOff')
        } else if (this.getGold() >= 1000) {
            $('#heroResurrection').addClass('buttonOff')
            $('#heroHire').removeClass('buttonOff')
        } else {
            $('#heroResurrection').addClass('buttonOff')
            $('#heroHire').addClass('buttonOff')
        }
    }
}