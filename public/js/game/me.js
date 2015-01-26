var Me = new function () {
    var gold = 0,
        costs = 0,
        income = 0,
        color,
        selectedArmyId = null,
        detached = new Array()

    this.init = function (me) {
        gold = me.gold
        goldUpdate()
        costs = me.costs
        costsUpdate()
        income = me.income
        incomeUpdate()
        color = me.color

        this.attachEventsControls()
    }
    this.getColor = function () {
        return color
    }

    var goldUpdate = function () {
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
    var costsUpdate = function () {
        $('#costs #value').fadeOut(300, function () {
            $('#costs #value').html(gold)
            $('#costs #value').fadeIn(300)
        })
    }
    var incomeUpdate = function () {
        $('#income #value').fadeOut(300, function () {
            $('#income #value').html(income)
            $('#income #value').fadeIn(300)
        })
    }

    this.goldIncrement = function (value) {
        gold += value
        goldUpdate()
    }
    this.costIncrement = function (value) {
        costs += value
        costsUpdate()
    }
    this.incomeIncrement = function (value) {
        income += value
        incomeUpdate()
    }
    this.countCastles = function () {
        return Players.get(color).getCastles().count()
    }
    this.getCastle = function (castleId) {
        return Players.get(color).getCastles().get(castleId)
    }
    this.getArmy = function (armyId) {
        return Players.get(color).getArmies().get(armyId)
    }
    this.getSelectedArmyId = function () {
        return selectedArmyId
    }
    this.setSelectedArmyId = function (armyId) {
        selectedArmyId = armyId
        if (selectedArmyId) {
            for (var i in EventsControls.objects) {
                if (i == 0) {
                    continue
                }
                //if (EventsControls.objects[i].id == Players.get(color).getArmies().get(armyId).getMeshId()) {
                //    continue
                //}
                detached.push(EventsControls.objects[i])
                EventsControls.detach(EventsControls.objects[i])
            }

            Message.remove()

            //Castle.selectedArmyCursor();
            //this.enemyCursorWhenSelected();
            //Castle.myRemoveCursor();

            //this.removeFromSkipped(a.armyId);
            //this.unfortify(a.armyId);

            //$('#army' + a.armyId)
            //    .css('background', 'url(/img/game/units/' + a.color + '/border_army.gif)');

            //this.updateInfo(a);
            $('#name').html('Army');

            $('#splitArmy').removeClass('buttonOff');
            $('#deselectArmy').removeClass('buttonOff');
            $('#armyStatus').removeClass('buttonOff');
            $('#disbandArmy').removeClass('buttonOff');
            $('#skipArmy').removeClass('buttonOff');
            $('#quitArmy').removeClass('buttonOff');

            //if (isSet(Army.selected.heroKey)) {
            //    if (Ruin.getIdByPosition(Army.selected.x, Army.selected.y) !== null) {
            //        $('#searchRuins').removeClass('buttonOff');
            //    }
            //    $('#showArtifacts').removeClass('buttonOff');
            //}

            //if (Castle.getMy(a.x, a.y)) {
            //    $('#razeCastle').removeClass('buttonOff');
            //    $('#buildCastleDefense').removeClass('buttonOff');
            //    $('#showCastle').removeClass('buttonOff');
            //}

            //if (notSet(center)) {
                //zoomer.setCenterIfOutOfScreen(a.x * 40, a.y * 40);
                //Zoom.lens.setcenter(a.x, a.y)
            //}
        } else {
            for (var i in detached) {
                EventsControls.attach(detached[i])
            }
        }

    }
    this.attachEventsControls = function () {
        Players.get(color).getArmies().attachEventsControls()
        Players.get(color).getCastles().attachEventsControls()
    }
}