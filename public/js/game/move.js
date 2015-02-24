var Move = function () {
    var stepTime = 200,
        player = null,
        army = null
    this.start = function (r, ii) {
        if (notSet(r.color)) {
            Gui.unlock()
            Websocket.executing = 0
            if (Me.colorEquals(r.color)) {
                Message.simple(translations.army, translations.noMoreMoves)
            }
            return
        }
        player = Players.get(r.color)
        army = player.getArmies().get(r.army.id)

        console.log(' ')
        console.log('move.start(' + ii + ') start')
        console.log(r)

        switch (army.getMovementType()) {
            case 'flying':
                Sound.play('fly');
                break;
            case 'swimming':
                Sound.play('swim');
                break;
            default:
                Sound.play('walk');
                break;
        }

        if (Turn.isMy() || (!player.isComputer() || Gui.show)) {
            Message.remove()
        }

        //if (notSet(r.path[1])) {
        //    Zoom.lens.setcenter(r.army.x, r.army.y)
        //} else {
        //    Fields.get(army.getX(), army.getY()).removeArmyId(army.getArmyId())
        Zoom.lens.setcenter(r.path[0].x, r.path[0].y)
        //}

        if (player.isComputer()) {
            stepTime = 100
        }

        stepLoop(r, ii)
        console.log('move.start(' + ii + ') end')
    }
    var stepLoop = function (r, ii) {
        console.log('move.stepLoop(' + ii + ') start')
        for (var step in r.path) {
            break
        }

        if (isSet(r.path[step])) {
            if (!player.isComputer() || Gui.show) {
                //zoomer.setCenterIfOutOfScreen(r.path[step].x * 40, r.path[step].y * 40);

                $('#' + army.getArmyId() + '.a')
                    .animate({
                        left: r.path[step].x * 2 + 'px',
                        top: r.path[step].y * 2 + 'px'
                    }, Move.stepTime, function () {
                        if (typeof r.path[step] == 'undefined') {
                            console.log(r)
                            console.log('step: ' + step)
                            console.log('path: ' + r.path)
                            throw('error20150224')
                        }
                        army.setPosition(r.path[step].x, r.path[step].y)
                        delete r.path[step]
                        stepLoop(r, ii);
                    })
            } else {
                delete r.path[step];
                stepLoop(r, ii);
            }
        } else {
            if (isTruthful(r.battle) && (!player.isComputer() || Gui.show)) {
                Sound.play('fight');

                //if (isTruthful(r.battle.castleId)) {
                //    board.append($('<div>')
                //        .addClass('war')
                //        .css({
                //            top: 40 * game.players[r.color].castles[r.battle.castleId].y - 12 + 'px',
                //            left: 40 * game.players[r.color].castles[r.battle.castleId].x - 11 + 'px'
                //        }));
                //} else {
                //board.append($('<div>')
                //    .addClass('war')
                //    .css({
                //        top: 40 * r.army.y - 42 + 'px',
                //        left: 40 * r.army.x - 41 + 'px'
                //    }));
                //}

                Message.battle(r, ii);
            } else {
                Move.end(r, ii)
            }
        }
        console.log('move.stepLoop(' + ii + ') end')
    }
    this.end = function (r, ii) {
        console.log('move.end(' + ii + ') start')

        army.update(r.army)
        if (player.isComputer() && !Gui.show) {
            army.setPosition(army.getX(), army.getY())
        }
        Zoom.lens.setcenter(army.getX(), army.getY())


        if (r.battle) {
            if (r.battle.victory) {
                for (var color in r.battle.defenders) {
                    if (color == 'neutral') {
                        continue
                    }
                    for (var armyId in r.battle.defenders[color]) {
                        Players.get(color).getArmies().delete(armyId, 1)
                    }
                }
                if (isDigit(r.battle.castleId)) {
                    var castles = Players.get(Fields.get(army.getX(), army.getY()).getCastleColor()).getCastles()
                    Players.get(r.color).getCastles().add(r.battle.castleId, castles.get(r.battle.castleId))
                    castles.remove(r.battle.castleId)
                }
                if (Me.colorEquals(r.color)) {
                    if (r.battle.castleId) {
                        Message.castle(Me.getCastle(r.battle.castleId))
                    } else if (Me.getArmy(army.getArmyId()).getMoves()) {
                        Me.selectArmy(army.getArmyId())
                    }
                }
            } else {
                Players.get(r.color).getArmies().delete(army.getArmyId(), 1)
                for (var color in r.battle.defenders) {
                    if (color == 'neutral') {
                        continue
                    }
                    for (var armyId in r.battle.defenders[color]) {
                        Players.get(color).getArmies().get(armyId).update(r.battle.defenders[color][armyId])
                    }
                }
                if (Me.colorEquals(r.color)) {
                    if (!Hero.findMy()) {
                        $('#heroResurrection').removeClass('buttonOff')
                    }
                }
            }
        }

        for (var i in r.deletedIds) {
            Players.get(r.color).getArmies().delete(r.deletedIds[i], 1)
        }

        if (player.isComputer()) {
            Websocket.computer()
        }

        //setTimeout('$(".war").remove()', 100);
        console.log('move.end(' + ii + ') end')
        Websocket.executing = 0
        if (Me.colorEquals(r.color)) {
            Gui.unlock()
        }
    }
}
