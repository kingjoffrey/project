var Me = new function () {
    var gold = 0,
        upkeep = 0,
        income = 0,
        color,
        selectedArmyId = null,
        deselectedArmyId = null,
        nextArmies = {},
        skippedArmies = {},
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
            $('#gold').fadeOut(300, function () {
                $('#gold').html(gold)
                $('#gold').fadeIn()
            })

            if (gold > 1000) {
                $('#heroHire').removeClass('buttonOff')
            } else {
                $('#heroHire').addClass('buttonOff')
            }
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
    this.goldIncrement = function (value) {
        gold += value
        updateGold()
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
    this.getSelectedArmyId = function () {
        return selectedArmyId
    }
    this.getDeselectedArmyId = function () {
        return deselectedArmyId
    }
    this.setParentArmyId = function (armyId) {
        parentArmyId = armyId
    }
    this.selectArmy = function (armyId) {
        PickerCommon.cursor(0)

        var army = this.getArmy(armyId),
            number,
            i = 0

        // GameRenderer.shadowsInfo()

        GameModels.addArmySelectionBox(army.getX(), army.getY(), army.getBackgroundColor())
        var heroId = army.getHeroKey()
        if (heroId) {
            $('#terrain').html(army.getHero(heroId).name)
        }
        Message.remove()

        this.removeFromSkipped(armyId)
        WebSocketSendGame.unfortify(armyId)

        $('#skipArmy').removeClass('buttonOff').click(function () {
            Me.skip()
        })
        $('#quitArmy').removeClass('buttonOff').click(function () {
            WebSocketSendGame.fortify()
        })

        selectedArmyId = armyId

        AStar.showRange(army)
        PickerGame.cursorChange()
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

        GameModels.clearArmySelectionMeshes()
        GameModels.clearPathCircles()
        this.setIsSelected(0)
        this.armyButtonsOff()
        PickerGame.cursorChange()
    }
    this.armyButtonsOff = function () {
        if (selectedArmyId) {
            deselectedArmyId = selectedArmyId
        }
        selectedArmyId = null
        $('.path').remove()
        $('#skipArmy').addClass('buttonOff').off()
        $('#quitArmy').addClass('buttonOff').off()
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

        if (GameGui.getLock()) {
            return
        }

        var armyId = this.getSelectedArmyId()
        if (armyId) {
            Sound.play('skip')
            skippedArmies[armyId] = 1
            this.deselectArmy()
            if (!isTouchDevice()) {
                this.findNext()
            }
        }
    }
    this.findNext = function (quiet) {
        if (!Turn.isMy()) {
            return
        }

        if (GameGui.getLock()) {
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

            if (armies[armyId].getFortified()) {
                continue
            }

            if (isTruthful(nextArmies[armyId])) {
                continue
            }
            //reset = false
            nextArmies[armyId] = true
            this.selectArmy(armyId)
            console.log('next army Id=' + armyId)
            console.log('aaa')
            GameScene.centerOn(this.getSelectedArmy().getX(), this.getSelectedArmy().getY())

            return armyId
        }

        if ($.isEmptyObject(nextArmies)) {
            if (notSet(quiet)) {
                Sound.play('error')
                var id = Message.show(translations.nextArmy, $('<div>').html(translations.thereIsNoFreeArmyWithSpareMovePoints))
                Message.addButton(id, 'cancel')
                Message.addButton(id, 'nextTurn', function () {
                    WebSocketSendGame.nextTurn()
                })
            }
        } else {
            this.deselectArmy()
            nextArmies = {}
            console.log('bbb')
            this.findNext(quiet)
        }
    }
    this.setFortified = function (armyId, f) {
        this.getArmy(armyId).setFortified(f)
    }
    this.isSelected = function () {
        return isSelected
    }
    this.setIsSelected = function (value) {
        isSelected = value
    }
    this.armyClick = function (armyId) {
        if (GameGui.getLock()) {
            return
        }

        if (!Turn.isMy()) {
            return
        }

        Sound.play('slash')

        this.selectArmy(armyId)
    }
    this.turnOn = function () {
        $('#turnInfo').hide()

        skippedArmies = {}
        nextArmies = {}

        GameGui.unlock()
        GameGui.titleBlink(translations.yourTurn)
    }
    this.turnOff = function () {
        this.deselectArmy()
        $('#nextTurn').addClass('buttonOff')
        $('#nextArmy').addClass('buttonOff')
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
        Message.addButton(id, 'Disbandarmy', WebSocketSendGame.disband)
        Message.addButton(id, 'cancel')
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
    this.getCapitalId = function () {
        return capitalId
    }
    this.init = function (c, g, bSequence, capitals) {
        selectedArmyId = null
        deselectedArmyId = null
        nextArmies = {}
        skippedArmies = {}
        isSelected = 0
        parentArmyId = null
        nextArmyId = null
        isNextSelected = null
        selectedCastleId = null
        selectedUnitId = null
        battleSequence = []

        color = c
        me = Players.get(color)
        capitalId = capitals[color]

        gold = 0
        upkeep = 0
        income = 0

        this.setBattleSequence(bSequence)
        this.setGold(g)
    }
}