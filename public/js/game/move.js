var Move = {
    stepTime: 0,
    moving: 0,
    start: function (r, ii) {
        if (notSet(r.color)) {
            Gui.unlock();
            Websocket.executing = 0
            Message.simple(translations.army, translations.noMoreMoves)
            return
        }
        this.moving = 1
        console.log(' ')
        console.log('move.start(' + ii + ') 0')
        console.log(r)
        switch (game.players[r.color].armies[r.army.armyId].movementType) {
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

        if (Turn.isMy() || (!game.players[r.color].computer || Gui.show)) {
            Message.remove()
        }

        if (notSet(r.path[1])) {
            zoom.lens.setcenter(r.army.x * 40, r.army.y * 40);
        } else {
            Army.fields(game.players[r.color].armies[r.army.armyId]);
            zoom.lens.setcenter(r.path[1].x * 40, r.path[1].y * 40);
        }

        Army.unfortify(r.army.armyId);

        if (game.players[r.color].computer) {
            this.stepTime = 100
        } else {
            this.stepTime = 200
        }

        this.loop(r, ii);
        console.log('move.start(' + ii + ') 1')
    },
    loop: function (r, ii) {
        console.log('move.loop(' + ii + ') 0')
        var step
        for (step in r.path) {
            break;
        }

        if (isSet(r.path[step])) {
            if (!game.players[r.color].computer || Gui.show) {
                //zoomer.setCenterIfOutOfScreen(r.path[step].x * 40, r.path[step].y * 40);
                zoom.lens.setcenter(r.path[step].x * 40, r.path[step].y * 40);

                $('#army' + r.army.armyId)
                    .animate({
                        left: (r.path[step].x * 40) + 'px',
                        top: (r.path[step].y * 40) + 'px'
                    }, Move.stepTime, function () {
                        if (typeof r.path[step] == 'undefined') {
                            throw(r)
                        }
                        delete r.path[step];
                        Move.loop(r, ii);
                    })
            } else {
                delete r.path[step];
                Move.loop(r, ii);
            }
        } else {
            if (isTruthful(r.battle) && (!game.players[r.color].computer || Gui.show)) {
                Sound.play('fight');

                //if (isTruthful(r.battle.castleId)) {
                //    board.append($('<div>')
                //        .addClass('war')
                //        .css({
                //            top: 40 * game.players[r.color].castles[r.battle.castleId].y - 12 + 'px',
                //            left: 40 * game.players[r.color].castles[r.battle.castleId].x - 11 + 'px'
                //        }));
                //} else {
                board.append($('<div>')
                    .addClass('war')
                    .css({
                        top: 40 * r.army.y - 42 + 'px',
                        left: 40 * r.army.x - 41 + 'px'
                    }));
                //}

                Message.battle(r, ii);
            } else {
                Move.end(r, ii);
            }
            console.log('move.loop(' + ii + ') 1')
        }
    },
    end: function (r, ii) {
        console.log('move.end(' + ii + ') 0')

        AStar.x = game.players[r.color].armies[r.army.armyId].x;
        AStar.y = game.players[r.color].armies[r.army.armyId].y;

        //if (game.players[r.color].computer && !Gui.show) {
        //    $('#army' + r.army.armyId)
        //        .css({
        //            left: (AStar.x * 40) + 'px',
        //            top: (AStar.y * 40) + 'px'
        //        })
        //}

        Army.init(r.army, r.color);

        if (isDigit(r.ruinId)) {
            Ruin.update(r.ruinId, 1);
        }

        if (r.battle) {
            if (r.battle.victory) {
                for (color in r.battle.defenders) {
                    if (color == 'neutral') {
                        continue
                    }
                    for (armyId in r.battle.defenders[color]) {
                        Army.delete(armyId, color, 1);
                    }
                }
                if (isDigit(r.battle.castleId)) {
                    Castle.owner(r.battle.castleId, r.color)
                }
                if (isDigit(r.battle.towerId)) {
                    Tower.change(r.battle.towerId, r.color)
                }
                if (r.color == game.me.color) {
                    if (!r.battle.castleId && game.players[r.color].armies[r.army.armyId].moves) {
                        Gui.unlock()
                        Army.select(game.players[r.color].armies[r.army.armyId])
                    } else {
                        Army.deselect()
                        Gui.unlock()
                    }
                } else {
                    for (color in r.battle.defenders) {
                        if (color == 'neutral') {
                            continue
                        }
                        for (armyId in r.battle.defenders[color]) {
                            Army.update(r.battle.defenders[color][armyId]);
                        }
                    }
                    if (r.color == game.me.color) {
                        if (!Hero.findMy()) {
                            $('#heroResurrection').removeClass('buttonOff')
                        }
                    }
                }
            }
        }

        for (i in r.deletedIds) {
            Army.delete(r.deletedIds[i].armyId, r.color, 1);
        }

        if (game.players[r.color].computer) {
            this.moving = 0
            Websocket.computer();
        }

        setTimeout('$(".war").remove()', 100);
        console.log('move.end(' + ii + ') 1')
        this.moving = 0
        Websocket.executing = 0
    }
}
