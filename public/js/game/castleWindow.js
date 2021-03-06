/**
 * Created by bartek on 31.03.15.
 */
var CastleWindow = new function () {
    var center = function (i) {
        return function () {
            GameScene.centerOn(Me.getCastle(i).getX(), Me.getCastle(i).getY())
        }
    }

    this.show = function (castle) {
        $('#terrain').hide()
        castle.removeUnits()
        Players.hideArmies()
        PickerCommon.detachAll()
        GameModels.removeCursor()

        $('.gameButtons').fadeOut(300)


        GameScene.moveCameraVeryClose()
        GameScene.centerOn(castle.getX() + 1, castle.getY() + 1)

        if (castle.getCastleId() == Me.getCapitalId()) {
            $('#castleName').html(castle.getName() + '&nbsp;(' + translations.capitalCity + ')')
        } else {
            $('#castleName').html(castle.getName())
        }

        $('#castleDefense span').html(castle.getDefense())
        $('#castleIncome span').html(castle.getIncome())

        var i = 0, time = '0/0'

        for (var unitId in castle.getProduction()) {
            var unit = Units.get(unitId)

            if (unitId == castle.getProductionId()) {
                time = castle.getProductionTurn() + '/' + castle.getProduction()[unitId].time

            }

            castle.addUnit(i, Unit.convertName(unit.name))

            i++
        }

        $('#castleProduction span').html(time)

        PickerGame.setCastle(castle)

        var nextCastle = Me.findNextCastle(castle.getCastleId()),
            previousCastle = Me.findPreviousCastle(castle.getCastleId())

        if (nextCastle) {
            $('#nextCastle').removeClass('buttonOff').off().click(function () {
                PickerGame.getCastle().removeUnits()
                CastleWindow.show(nextCastle)
            })
        } else {
            $('#nextCastle').addClass('buttonOff').off()
        }

        if (previousCastle) {
            $('#previousCastle').removeClass('buttonOff').off().click(function () {
                PickerGame.getCastle().removeUnits()
                CastleWindow.show(previousCastle)
            })
        } else {
            $('#previousCastle').addClass('buttonOff').off()
        }

        if (castle.getCastleId() == Me.getCapitalId()) {
            if (!Me.findHero() && Me.getGold() >= 100) {
                $('#heroResurrection').removeClass('buttonOff').off().click(function () {
                    var id = Message.show(translations.resurrectHero, $('<div>').append(translations.doYouWantToResurrectHeroFor100Gold))
                    Message.addButton(id, 'resurrectHero', WebSocketSendGame.resurrection)
                    Message.addButton(id, 'cancel')
                })
                $('#heroHire').addClass('buttonOff').off()
            } else {
                $('#heroResurrection').addClass('buttonOff').off()
                if (Me.getGold() >= 1000) {
                    $('#heroHire').removeClass('buttonOff').off().click(function () {
                        var id = Message.show(translations.hireHero, $('<div>').html(translations.doYouWantToHireNewHeroFor1000Gold))
                        Message.addButton(id, 'hireHero', WebSocketSendGame.hire)
                        Message.addButton(id, 'cancel')
                    })
                } else {
                    $('#heroHire').addClass('buttonOff').off()
                }
            }

            $('#heroButtons').show()
        } else {
            $('#heroButtons').hide()
        }

        $('#castleButtons').fadeIn(300)
    }
    this.hide = function () {
        $('#terrain').show()
        PickerCommon.detachAll()
        $('#castleButtons').fadeOut(300)

        PickerGame.getCastle().removeUnits()
        PickerGame.setCastle(null)

        GameScene.moveCameraAwayNormal()
        Players.showArmies()
        PickerCommon.attach(Ground.getWaterMesh())

        GameModels.addCursor()

        $('.gameButtons').fadeIn(300)
    }
    this.raze = function () {
        if (!Me.getSelectedArmyId()) {
            return;
        }
        var id = Message.show(translations.destroyCastle, $('<div>').html(translations.areYouSure))
        Message.addButton(id, 'destroyCastle', WebSocketSendGame.raze);
        Message.addButton(id, 'cancel')
    }
    this.build = function () {
        if (!Me.getSelectedArmyId()) {
            return;
        }

        var army = Me.getArmy(Me.getSelectedArmyId())
        var castle = Me.getCastle(Fields.get(army.getX(), army.getY()).getCastleId())

        if (castle.getDefense() == 4) {
            var div = $('<div>')
                .append($('<h3>').html(translations.maximumCastleDefenceReached))
                .append($('<div>').html(translations.currentDefense + ': ' + castle.getDefense()))
            var id = Message.show(translations.buildCastleDefense, div);
        } else {
            var costBuildDefense = this.calculateBuildCost(castle.getDefense()),
                newDefense = castle.getDefense() + 1;

            var div = $('<div>')
                .append($('<h3>').html(translations.doYouWantToBuildCastleDefense))
                .append($('<div>').html(translations.currentDefense + ': ' + castle.getDefense()))
                .append($('<div>').html(translations.newDefense + ': ' + newDefense))
                .append($('<div>').html(translations.Cost + ': ' + costBuildDefense + ' ' + translations.gold))
            var id = Message.show(translations.buildCastleDefense, div);
            Message.addButton(id, 'buildCastleDefense', WebSocketSendGame.defense)
        }
        Message.addButton(id, 'cancel')
    }
    this.calculateBuildCost = function (defense) {
        var costBuildDefense = 0;
        for (i = 1; i <= defense; i++) {
            costBuildDefense += i * 100;
        }
        return costBuildDefense
    }
}
