var Me = new function () {
    var gold = 0,
        costs = 0,
        income = 0,
        color,
        selectedArmyId = null,
        deselectedArmyId = null,
        detached = [],
        nextArmies = {},
        skippedArmies = {},
        quitedArmies = {},
        isSelected = 0,
        parentArmyId = null,
        nextArmyId = null,
        isNextSelected = null,
        me,
        selectedCastleId = null,
        selectedUnitId = null

    this.init = function (c) {
        color = c
        me = Players.get(color)

        this.attachEventsControls()

        updateGold()
        updateCosts()
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
    var updateCosts = function () {
        $('#costs #value').fadeOut(300, function () {
            $('#costs #value').html(gold)
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
    this.setCosts = function (value) {
        costs = value
        updateCosts()
    }
    this.setIncome = function (value) {
        income = value
        updateIncome()
    }
    this.goldIncrement = function (value) {
        gold += value
        updateGold()
    }
    this.costIncrement = function (value) {
        costs += value
        updateCosts()
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
    this.getCastle = function (castleId) {
        return me.getCastles().get(castleId)
    }
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
    this.resetQuitedArmies = function () {
        quitedArmies = {}
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
        selectedArmyId = armyId
        var army = this.getArmy(armyId)
        for (var i = EventsControls.objects.length - 1; i > 0; i--) {
            if (EventsControls.objects[i].id == army.getMeshId()) {
                continue
            }
            detached.push(EventsControls.objects[i].id)
            EventsControls.detach(EventsControls.objects[i])
        }
        Three.addCircle(army.getX(), army.getY())

        Message.remove()

        //Castle.selectedArmyCursor();
        //this.enemyCursorWhenSelected();
        //Castle.myRemoveCursor();

        this.removeFromSkipped(armyId)
        this.unfortify(armyId)

        //$('#army' + a.armyId)
        //    .css('background', 'url(/img/game/units/' + a.color + '/border_army.gif)');

        this.updateInfo(armyId)
        $('#name').html('Army')

        $('#splitArmy').removeClass('buttonOff');
        $('#deselectArmy').removeClass('buttonOff');
        $('#armyStatus').removeClass('buttonOff');
        $('#disbandArmy').removeClass('buttonOff');
        $('#skipArmy').removeClass('buttonOff');
        $('#quitArmy').removeClass('buttonOff');

        if (army.getHeroKey()) {
            if (Fields.get(army.getX(), army.getY()).getRuinId()) {
                $('#searchRuins').removeClass('buttonOff');
            }
            //    $('#showArtifacts').removeClass('buttonOff');
        }

        if (this.colorEquals(Fields.get(army.getX(), army.getY()).getCastleColor())) {
            $('#razeCastle').removeClass('buttonOff');
            $('#buildCastleDefense').removeClass('buttonOff');
            $('#showCastle').removeClass('buttonOff');
        }

        if (notSet(center)) {
            //zoomer.setCenterIfOutOfScreen(a.x * 40, a.y * 40);
            Zoom.lens.setcenter(army.getX(), army.getY())
        }
    }
    this.deselectArmy = function (skipJoin) {
        if (notSet(skipJoin) && parentArmyId && selectedArmyId) {
            var selectedArmy = this.getArmy(selectedArmyId)
            var parentArmy = this.getArmy(parentArmyId)
            if (selectedArmy.getX() == parentArmy.getX() && selectedArmy.getY() == parentArmy.getY()) {
                Websocket.join(selectedArmyId)
            }
        }

        Three.clearCircles()
        for (var i in detached) {
            EventsControls.attach(Three.getScene().getObjectById(detached[i]))
        }
        detached = []

        Me.setIsSelected(0)
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
        $('#splitArmy').addClass('buttonOff');
        $('#deselectArmy').addClass('buttonOff');
        $('#armyStatus').addClass('buttonOff');
        $('#skipArmy').addClass('buttonOff');
        $('#quitArmy').addClass('buttonOff');
        $('#searchRuins').addClass('buttonOff');
        $('#razeCastle').addClass('buttonOff');
        $('#buildCastleDefense').addClass('buttonOff');
        $('#showCastle').addClass('buttonOff');
        $('#showArtifacts').addClass('buttonOff');
        $('#disbandArmy').addClass('buttonOff');
    }
    this.attachEventsControls = function () {
        me.getArmies().attachEventsControls()
        me.getCastles().attachEventsControls()
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
            Message.simple(translations.nextArmy, translations.thereIsNoFreeArmyToWithSpareMovePoints)
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
    this.fortify = function () {
        if (!Turn.isMy()) {
            return
        }
        if (Gui.lock) {
            return
        }
        if (selectedArmyId) {
            Websocket.fortify(selectedArmyId)
            quitedArmies[selectedArmyId] = 1
            this.deselectArmy()
            this.findNext()
        }
    }
    this.unfortify = function (armyId) {
        if (!Turn.isMy()) {
            return
        }
        if (Gui.lock) {
            return
        }
        if (isTruthful(quitedArmies[armyId])) {
            Websocket.unfortify(armyId, 0)
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
            return;
        }

        if (!Turn.isMy()) {
            return
        }

        if (selectedArmyId) {
            var army = this.getArmy(selectedArmyId)
            if (army.countHeroes() + army.countSoldiers() == 1) {
                return
            }

            //$('#army' + army.armyId).css('background', 'none');
            //$('#army' + army.armyId + ' .unit').css('background', 'url(/img/game/units/' + army.color + '/border_unit.gif)');

            var newHeroKey = null;
            var newSoldierKey = null;

            army.setHeroSplitKey(null)
            army.setSoldierSplitKey(null)

            for (var key in army.getHeroes()) {
                if (army.skippedHeroes[key]) {
                    continue
                }
                army.skippedHeroes[key] = true
                newHeroKey = key
                army.setHeroSplitKey(key)
                break
            }

            if (newHeroKey !== null) {
                //Army.setImg(army, newHeroKey, null);
                //Army.changeImg(army)
                Me.updateInfo(selectedArmyId)
                $('#moves').html(army.getHeroMovesLeft(newHeroKey))
                return
            }

            for (var key in army.getSoldiers()) {
                if (army.skippedSoldiers[key]) {
                    continue;
                }
                army.skippedSoldiers[key] = true
                newSoldierKey = key
                army.soldierSplitKey = key
                break;
            }

            if (newSoldierKey !== null) {
                //Army.setImg(army, null, newSoldierKey);
                //Army.changeImg(army)
                Me.updateInfo(selectedArmyId)
                $('#moves').html(army.getSoldierMovesLeft(newSoldierKey));
                return;
            }

            //Army.setImg(army, army.heroKey, army.soldierKey);
            //Army.changeImg(army)
            army.getMovementType()
            Me.updateInfo(selectedArmyId)
            $('#name').html('Army')

            //$('#army' + army.armyId).css('background', 'url(/img/game/units/' + army.color + '/border_army.gif)');
            //$('#army' + army.armyId + ' .unit').css('background', 'none');

            army.skippedHeroes = {}
            army.skippedSoldiers = {}
        } else {
            Sound.play('slash')
            this.selectArmy(armyId, 0)
        }
    }
    this.turnOn = function () {
        this.resetSkippedArmies()
        this.showFirst()
        Message.turn()
        Gui.unlock()
        titleBlink('Your turn!')
        if (!Hero.findMy()) {
            $('#heroResurrection').removeClass('buttonOff')
        }
        if (gold > 1000) {
            $('#heroHire').removeClass('buttonOff')
        }
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
    this.showFirst = function () {
        var castleId = Game.getCapitalId(color)
        var firstCastleId
        if (isSet(this.getCastle(castleId))) {
            Zoom.lens.setcenter(this.getCastle(castleId).getX(), this.getCastle(castleId).getY())
        } else if (firstCastleId = this.getFirsCastleId()) {
            Zoom.lens.setcenter(this.getCastle(firstCastleId).getX(), this.getCastle(firstCastleId).getY())
        } else {
            var armies = me.getArmies().toArray()
            for (var armyId in armies) {
                Zoom.lens.setcenter(armies[armyId].getX(), armies[armyId].getY())
                return
            }
        }
    }
    this.getFirsCastleId = function () {
        for (var castleId in me.getCastles().toArray()) {
            return castleId
        }
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
    this.getArmies = function () {
        return me.getArmies()
    }
    this.getCastles = function () {
        return me.getCastles()
    }
    this.getTowers = function () {
        return me.getTowers()
    }
    this.getSelectedArmy = function () {
        return this.getArmy(selectedArmyId)
    }
}