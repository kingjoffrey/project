/**
 * Created by bartek on 31.03.15.
 */
var CastleWindow = new function () {
    var center = function (i) {
            return function () {
                GameScene.centerOn(Me.getCastle(i).getX(), Me.getCastle(i).getY())
            }
        },
        click = function (i, id) {
            return function () {
                CastleWindow.show(Me.getCastle(i))
                Message.remove(id)
            }
        }


    this.show = function (castle) {
        var time = '',
            checked,
            messageId,
            castleWindow = $('<div>').addClass('showCastle'),
            createProductionTD = function () {
                var array = $('<div>')

                for (var unitId in castle.getProduction()) {
                    var travelBy = '',
                        unit = Units.get(unitId)

                    if (unitId == castle.getProductionId()) {
                        checked = ' checked'
                        time = castle.getProductionTurn() + '/';
                    } else {
                        checked = ''
                        time = ''
                    }

                    if (unit.canFly) {
                        travelBy = translations.ground + ' / ' + translations.air;
                    } else if (unit.canSwim) {
                        travelBy = translations.water;
                    } else {
                        travelBy = translations.ground;
                    }

                    if (unit.name_lang) {
                        var unitName = unit.name_lang;
                    } else {
                        var unitName = unit.name;
                    }

                    var unitElement = $('<div>')
                        .addClass('unit' + checked)
                        .attr('id', unitId)
                        .append(
                            $('<div>').addClass('name').html('<b>' + unitName + '</b>' + '<br/> (' + travelBy + ')')
                        )
                        .append(
                            $('<div>')
                                .append($('<div>').addClass('buttons'))
                                .append($('<div>').addClass('attributes')
                                    .append($('<div>').html(translations.productionTime + ': ' + time + '<span>' + castle.getProduction()[unitId].time + '</span>'))
                                    .append($('<div>').html(translations.costOfLiving + ': ' + '<span>' + unit.cost + '</span>'))
                                    .append($('<div>').html(translations.movementPoints + ': ' + '<span>' + unit.moves + '</span>'))
                                    .append($('<div>').html(translations.attackPoints + ': ' + '<span>' + unit.a + '</span>'))
                                    .append($('<div>').html(translations.defencePoints + ': ' + '<span>' + unit.d + '</span>'))
                                )
                        )

                    if (checked) { // jest produkcja
                        if (castle.getRelocationCastleId() && Me.getCastles().has(castle.getRelocationCastleId())) {
                            unitElement.append($('<div>').addClass('relocatingTo').html(translations.relocatingTo + ' ' + Me.getCastle(castle.getRelocationCastleId()).getName()))
                        } else {
                            unitElement.append($('<div>').addClass('relocatingTo').html(translations.production))
                        }

                        unitElement.find('.buttons')
                            .append(
                                $('<div>') // stop
                                    .html(translations.stop)
                                    .addClass('button buttonColors')
                                    .attr('id', 'stop')
                                    .click(function () {
                                        castle.handle($(this).parent().parent().parent().attr('id'), 1, 0)
                                        Message.remove(messageId)
                                    }))
                    } else { // nie ma produkcji
                        unitElement.find('.buttons')
                            .append(
                                $('<div>') // start
                                    .addClass('button buttonColors')
                                    .attr('id', 'go')
                                    .html(translations.start)
                                    .click(function () {
                                        castle.handle($(this).parent().parent().parent().attr('id'))
                                        Message.remove(messageId)
                                    }))
                        unitElement.find('.buttons')
                            .append(
                                $('<div>') // relokacja
                                    .html(translations.relocation)
                                    .addClass('button buttonColors')
                                    .attr('id', 'relocation')
                                    .click(function () {
                                        castle.handle($(this).parent().parent().parent().attr('id'), 0, 1)
                                        Message.remove(messageId)
                                    }))
                    }
                    array.append(unitElement)
                }
                return array
            }

        var next = $('<div>'),
            previous = $('<div>'),
            nextCastle = Me.findNextCastle(castle.getCastleId()),
            previousCastle = Me.findPreviousCastle(castle.getCastleId())

        if (nextCastle) {
            next = $('<div>')
                .attr('id', 'next')
                .addClass('button buttonColors')
                .html('>>')
                .click(function () {
                    CastleWindow.show(nextCastle)
                    GameScene.centerOn(nextCastle.getX(), nextCastle.getY())
                    Message.remove(messageId)
                })
        }

        if (previousCastle) {
            previous = $('<div>')
                .attr('id', 'previous')
                .addClass('button buttonColors')
                .html('<<')
                .click(function () {
                    CastleWindow.show(previousCastle)
                    GameScene.centerOn(previousCastle.getX(), previousCastle.getY())
                    Message.remove(messageId)
                })
        }

        castleWindow
            .append($('<div>').html(translations.incomeFromCastle + ': ' + castle.getIncome() + ' ' + translations.gold_turn))
            .append(
                $('<div>').addClass('text-right')
                    .append(previous)
                    .append($('<div>')
                        .addClass('button buttonColors')
                        .html(translations.close)
                        .click(function () {
                            Message.remove(messageId)
                        }))
                    .append(next)
            )
            .append($('<div>').addClass('production').append($('<div>').html(translations.availableUnits).addClass('title')).append(createProductionTD()).attr('id', castle.getCastleId()))

        if (castle.getCastleId() == Me.getCapitalId()) {
            var resurrect = $('<div>').addClass('button buttonColors buttonOff').attr('id', 'heroResurrection')
                .html(translations.resurrectHero)
                .click(function () {
                    Message.remove(messageId)
                })

            var hire = $('<div>').addClass('button buttonColors buttonOff').attr('id', 'heroHire')
                .html(translations.hireHero)
                .click(function () {
                    Message.remove(messageId)
                })

            if (castle.getCastleId() == Me.getCapitalId()) {
                if (!Me.findHero() && Me.getGold() >= 100) {
                    resurrect.removeClass('buttonOff').click(function () {
                        var id = Message.show(translations.resurrectHero, $('<div>').append(translations.doYouWantToResurrectHeroFor100Gold))
                        Message.ok(id, WebSocketSendGame.resurrection)
                        Message.cancel(id)
                    })
                } else if (Me.getGold() >= 1000) {
                    hire.removeClass('buttonOff').click(function () {
                        Message.remove(messageId)
                        var id = Message.show(translations.hireHero, $('<div>').html(translations.doYouWantToHireNewHeroFor1000Gold))
                        Message.ok(id, WebSocketSendGame.hire)
                        Message.cancel(id)
                    })
                }
            }

            castleWindow
                .append(resurrect)
                .append(hire)
        }

        // relocation from

        var relocatedProduction = Me.getCastles().getRelocatedProduction(castle.getCastleId())
        if (relocatedProduction.length) {
            var relocatingFrom = $('<table>')

            for (var i in relocatedProduction) {
                var castleIdFrom = relocatedProduction[i],
                    castleFrom = Me.getCastle(castleIdFrom),
                    productionId = castleFrom.getProductionId()

                relocatingFrom.append(
                    $('<tr>')
                        .append($('<td>').html(Units.get(productionId).name_lang))
                        .append($('<td>')
                            .html(castleFrom.getProductionTurn() + '/' + castleFrom.getProduction()[productionId].time))
                        .append($('<td>').append($('<div>').html(castleFrom.getName())
                            .addClass('button buttonColors')
                            .click(click(castleIdFrom, messageId)))
                        )
                        .append(
                            $('<td>')
                                .append(
                                    $('<div>')
                                        .append(
                                            $('<img>').attr('src', '/img/game/center.png')
                                                .addClass('iconButton buttonColors')
                                                .click(center(castleIdFrom))
                                        )
                                )
                        )
                )
            }
            castleWindow
                .append($('<div>').addClass('relocatedProduction').append($('<div>').html(translations.relocatingFrom).addClass('title')).append(relocatingFrom))
        }

        if (castle.getCastleId() == Me.getCapitalId()) {
            var title = castle.getName() + '&nbsp;(' + translations.capitalCity + ')'

        } else {
            var title = castle.getName()
        }

        setTimeout(function () {
            messageId = Message.show(title, castleWindow)
            Message.adjust(messageId)
        }, 300)
    }
    this.raze = function () {
        if (!Me.getSelectedArmyId()) {
            return;
        }
        var id = Message.show(translations.destroyCastle, $('<div>').html(translations.areYouSure))
        Message.ok(id, WebSocketSendGame.raze);
        Message.cancel(id)
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
            var costBuildDefense = 0;
            for (i = 1; i <= castle.getDefense(); i++) {
                costBuildDefense += i * 100;
            }
            var newDefense = castle.getDefense() + 1;

            var div = $('<div>')
                .append($('<h3>').html(translations.doYouWantToBuildCastleDefense))
                .append($('<div>').html(translations.currentDefense + ': ' + castle.getDefense()))
                .append($('<div>').html(translations.newDefense + ': ' + newDefense))
                .append($('<div>').html(translations.Cost + ': ' + costBuildDefense + ' ' + translations.gold))
            var id = Message.show(translations.buildCastleDefense, div);
            Message.ok(id, WebSocketSendGame.defense);
        }
        Message.cancel(id)
    }
}
