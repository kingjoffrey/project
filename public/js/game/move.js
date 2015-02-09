var Move = new function () {
    var stepTime = 200,
        moving = 0,
        player = null,
        army = null
    this.getMoving = function () {
        return moving
    }
    this.start = function (r, ii) {
        if (notSet(r.color)) {
            Gui.unlock()
            Websocket.executing = 0
            Message.simple(translations.army, translations.noMoreMoves)
            return
        }
        moving = 1
        player = Players.get(r.color)
        army = player.getArmies().get(r.army.armyId)
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

        if (notSet(r.path[1])) {
            Zoom.lens.setcenter(r.army.x, r.army.y)
        } else {
            //Army.fields(game.players[r.color].armies[r.army.armyId])
            Fields.get(army.getX(), army.getY()).removeArmyId(r.army.armyId)
            Zoom.lens.setcenter(r.path[1].x, r.path[1].y)
        }

        //Me.unfortify(r.army.armyId)

        if (player.isComputer()) {
            stepTime = 100
        }

        loop(r, ii)
        console.log('move.start(' + ii + ') end')
    }
    var loop = function (r, ii) {
        console.log('move.loop(' + ii + ') start')
        for (var step in r.path) {
            break
        }

        if (isSet(r.path[step])) {
            if (!player.isComputer() || Gui.show) {
                //zoomer.setCenterIfOutOfScreen(r.path[step].x * 40, r.path[step].y * 40);
                Zoom.lens.setcenter(r.path[step].x, r.path[step].y)

                $('#' + r.army.armyId + '.a')
                    .animate({
                        left: r.path[step].x * 2 + 'px',
                        top: r.path[step].y * 2 + 'px'
                    }, Move.stepTime, function () {
                        if (typeof r.path[step] == 'undefined') {
                            throw(r)
                        }
                        Three.getScene().getObjectById(army.getMeshId()).position.set(r.path[step].x * 4 - 218, 0, r.path[step].y * 4 - 312)
                        delete r.path[step];
                        loop(r, ii);
                    })
            } else {
                delete r.path[step];
                loop(r, ii);
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
                end(r, ii)
            }
        }
        console.log('move.loop(' + ii + ') end')
    }
    var end = function (r, ii) {
        console.log('move.end(' + ii + ') start')

        army.update(r.army)

        AStar.x = army.getX()
        AStar.y = army.getY()

        //if (game.players[r.color].computer && !Gui.show) {
        //    $('#army' + r.army.armyId)
        //        .css({
        //            left: (AStar.x * 40) + 'px',
        //            top: (AStar.y * 40) + 'px'
        //        })
        //}

        //if (isDigit(r.ruinId)) {
        //    Ruins.update(r.ruinId, 1);
        //}

        if (r.battle) {
            if (r.battle.victory) {
                for (var color in r.battle.defenders) {
                    if (color == 'neutral') {
                        continue
                    }
                    for (var armyId in r.battle.defenders[color]) {
                        Army.delete(armyId, color, 1);
                    }
                }
                if (isDigit(r.battle.castleId)) {
                    for (var color in game.players) {
                        if (isSet(game.players[color].castles[r.battle.castleId])) {
                            Castle.owner(r.battle.castleId, r.color, color)
                            break;
                        }
                    }
                }
                if (isDigit(r.battle.towerId)) {
                    Tower.change(r.battle.towerId, r.color)
                }
                if (r.color == game.me.color) {
                    if (!r.battle.castleId && game.players[r.color].armies[r.army.armyId].moves) {
                        Army.select(game.players[r.color].armies[r.army.armyId])
                    } else {
                        Army.deselect()
                    }
                } else {
                    for (var color in r.battle.defenders) {
                        if (color == 'neutral') {
                            continue
                        }
                        //for (var armyId in r.battle.defenders[color]) {
                        //    Army.update(r.battle.defenders[color][armyId]);
                        //}
                    }
                    if (r.color == game.me.color) {
                        if (!Hero.findMy()) {
                            $('#heroResurrection').removeClass('buttonOff')
                        }
                    }
                }
            }
        }

        for (var i in r.deletedIds) {
            Army.delete(r.deletedIds[i], r.color, 1);
        }

        if (player.isComputer()) {
            Websocket.computer()
        }

        //setTimeout('$(".war").remove()', 100);
        console.log('move.end(' + ii + ') end')
        moving = 0
        Websocket.executing = 0
        if (Me.colorEquals(r.color)) {
            Gui.unlock()
        }
    }
}
