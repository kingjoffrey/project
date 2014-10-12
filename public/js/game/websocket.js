Websocket = {
    closed: true,
    i: 0,
    queue: {},
    executing: 0,
    addQueue: function (r) {
        this.i++
        this.queue[this.i] = r
        this.wait()
    },
    wait: function () {
        if (this.executing) {
            setTimeout('Websocket.wait()', 500);
        } else {
            var ii
            for (ii in this.queue) {
                this.execute(this.queue[ii])
                delete this.queue[ii]
                return
            }
        }
    },
    execute: function (r) {
        this.executing = 1

        switch (r.type) {
            case 'move':
                Move.start(r, Websocket.i);
                break;

            case 'computer':
                if (isTruthful(r.path)) {
                    Move.start(r, this.i)
                } else {
                    this.computer()
                    this.executing = 0
                }
                break;

            case 'computerStart':
                var s = Army.computerLoop(r.armies, r.color)
                this.executing = 0
                break;

            case 'nextTurn':
                Army.deselect()
                Turn.change(r.color, r.nr)
                this.computer()
                this.executing = 0
                break;

            case 'startTurn':
                if (r.color == my.color) {
                    Army.quitedArmies = {};
                    Sound.play('startturn');
                    goldUpdate(r.gold)
                    costsUpdate(r.costs)
                    incomeUpdate(r.income)
                    Gui.unlock();
                }

                for (i in r.armies) {
                    var status = Army.init(r.armies[i], r.color);
                }
                for (i in r.castles) {
                    var status = Castle.updateCurrentProductionTurn(i, r.castles[i].productionTurn);
                }
                this.executing = 0
                break;

            case 'ruin':
                var X = 40 * r.army.x,
                    Y = 40 * r.army.y
                board.append($('<div>').addClass('ruinSearch').css({'top': Y + 'px', 'left': X + 'px'}));
                zoomer.lensSetCenter(X, Y);
                Army.init(r.army, r.color);
                Ruin.update(r.ruin.ruinId, r.ruin.empty);
                if (my.color == r.color) {
                    switch (r.find[0]) {
                        case 'gold':
                            Sound.play('gold1');
                            goldIncrement(r.find[1]);
                            Message.simple(translations.ruins, translations.youHaveFound + ' ' + r.find[1] + ' ' + translations.gold);
                            break;
                        case 'death':
                            Sound.play('death');
                            Message.simple(translations.ruins, translations.youHaveFound + ' ' + translations.death);
                            if (!Hero.findMy()) {
                                $('#heroResurrection').removeClass('buttonOff')
                            }
                            break
                        case 'allies':
                            Sound.play('allies');
                            Message.simple(translations.ruins, r.find[1] + ' ' + translations.alliesJoinedYourArmy);
                            break
                        case 'null':
                            Sound.play('click');
                            Message.simple(translations.ruins, translations.youHaveFoundNothing);
                            break
                        case 'artifact':
                            Message.simple(translations.ruins, translations.youHaveFound + ' ' + translations.anAncientArtifact + ' - "' + artifacts[r.find[1]].name + '".');
                            Chest.update(r.color, r.find[1]);
                            break
                        case 'empty':
                            Sound.play('error');
                            Message.simple(translations.ruins, translations.ruinsAreEmpty);
                            break;

                    }
                }
                $('.ruinSearch').animate({'display': 'none'}, 1000, function () {
                    $('.ruinSearch').remove()
                    Websocket.executing = 0
                })
                break;

            case 'split':
                Army.init(r.parentArmy, r.color);
                Army.init(r.childArmy, r.color);

                Army.parent = players[r.color].armies[r.parentArmy.armyId];

                if (my.turn) {
                    Message.remove()
                    Army.select(players[r.color].armies[r.childArmy.armyId], 0);
                } else {
                    zoomer.setCenterIfOutOfScreen(r.parentArmy.x * 40, r.parentArmy.y * 40);
                }
                this.executing = 0
                break;

            case 'join':
                if (my.turn) {
                    Message.remove()
                }
                zoomer.setCenterIfOutOfScreen(r.army.x * 40, r.army.y * 40);
                for (i in r.deletedIds) {
                    Army.delete(r.deletedIds[i].armyId, r.color);
                }
                Army.init(r.army, r.color);
                this.executing = 0
                break;

            case 'disband':
                if (my.turn) {
                    Message.remove()
                    var upkeep = 0;
                    for (i in players[my.color].armies[r.armyId].soldiers) {
                        upkeep += units[players[my.color].armies[r.armyId].soldiers[i].unitId].cost
                    }

                    costIncrement(-upkeep)

                    if (!Hero.findMy()) {
                        $('#heroResurrection').removeClass('buttonOff')
                    }
                }
                Army.delete(r.armyId, r.color);
                this.executing = 0
                break;

            case 'resurrection':
                Sound.play('resurrection');
                zoomer.lensSetCenter(r.data.army.x * 40, r.data.army.y * 40);
                Army.init(r.data.army, r.color);
                if (my.turn) {
                    Message.remove()
                    goldUpdate(r.data.gold);
                    if (Hero.findMy()) {
                        $('#heroResurrection').addClass('buttonOff')
                    }
                } else if (players[r.color].computer) {
                    Websocket.computer()
                }
                this.executing = 0
                break;

            case 'raze':
                $('#razeCastle').addClass('buttonOff');
                Castle.raze(r.castleId);
                if (my.turn) {
                    Sound.play('gold1');
                    Message.remove()
                    goldUpdate(r.gold);
                } else {
                    Sound.play('raze');
                }
                this.executing = 0
                break;

            case 'defense':
                Castle.updateDefense(r.castleId, r.defenseMod);
                if (my.turn) {
                    Message.remove();
                    goldUpdate(r.gold);
                }
                this.executing = 0
                break;

            case 'surrender':
                Army.deselect()
                for (i in players[r.color].armies) {
                    var s = Army.delete(i, r.color, 1)
                }
                for (i in players[r.color].castles) {
                    var s = Castle.raze(i)
                }
                this.nextTurn()
                this.executing = 0
                break;

            case 'end':
                Turn.off()
                Message.end()
                this.executing = 0
                break;

            case 'dead':
                if (notSet(Players.wedges[r.color].skull)) {
                    Players.drawSkull(r.color)
                }
                this.executing = 0
                break;
        }
    },
    init: function () {
        ws = new WebSocket(wsURL + '/game');

        ws.onopen = function () {
            Websocket.closed = false;
            Websocket.open();
        };

        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data);

            if (isSet(r['type'])) {

                switch (r.type) {

                    case 'move':
                        Websocket.addQueue(r)
                        break;

                    case 'computer':
                        Websocket.addQueue(r)
                        break;

                    case 'computerStart':
                        Websocket.addQueue(r)
                        break;

                    case 'nextTurn':
                        Websocket.addQueue(r)
                        break;

                    case 'startTurn':
                        Websocket.addQueue(r)
                        break;

                    case 'ruin':
                        Websocket.addQueue(r)
                        break;

                    case 'split':
                        Websocket.addQueue(r)
                        break;

                    case 'join':
                        Websocket.addQueue(r)
                        break;

                    case 'disband':
                        Websocket.addQueue(r)
                        break;

                    case 'resurrection':
                        Websocket.addQueue(r)
                        break;

                    case 'raze':
                        Websocket.addQueue(r)
                        break;

                    case 'defense':
                        Websocket.addQueue(r)
                        break;

                    case 'surrender':
                        Websocket.addQueue(r)
                        break;

                    case 'end':
                        Websocket.addQueue(r)
                        break;

                    case 'dead':
                        Websocket.addQueue(r)
                        break;

                    case 'error':
                        Message.error(r.msg);
                        Gui.unlock();
                        break;

                    case 'open':
                        for (color in r.online) {
                            online[color] = r.online[color]
                        }
                        Players.updateOnline()
                        if (r.color == my.color) {
                            Gui.lock = false;
                            if (loading) {
                                startGame();
                                loading = false;
                            } else if (Players.isMy() && players[Turn.color].computer) {
                                setTimeout('Websocket.computer()', 1000);
                            }
                        }
                        break;

                    case 'close':
                        online[r.color] = 0
                        Players.updateOnline()
                        break;

                    case 'chat':
                        chat(r.color, r.msg, makeTime());
                        break;

                    case 'production':
                        Castle.updateMyProduction(r.unitId, r.castleId, r.relocationToCastleId);
                        break;

                    case 'statistics':
                        castlesConquered = r.castlesConquered;
                        castlesDestroyed = r.castlesDestroyed;
                        heroesKilled = r.heroesKilled;
                        soldiersCreated = r.soldiersCreated;
                        soldiersKilled = r.soldiersKilled;
                        Message.statistics();
                        break;

                    case 'bSequence':
                        if (r.attack == 'true') {
                            Message.simple(translations.battleSequence, translations.attackSequenceSuccessfullyUpdated)
                            my.battleSequence['attack'] = r.sequence
                        } else {
                            Message.simple(translations.battleSequence, translations.defenceSequenceSuccessfullyUpdated)
                            my.battleSequence['defence'] = r.sequence
                        }
                        break

                    default:
                        console.log(r);

                }
            }
        }

        ws.onclose = function () {
            Websocket.closed = true
            setTimeout('Websocket.init()', 1000)
        }

    },
    open: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'open',
            gameId: gameId,
            playerId: my.id,
            langId: langId,
            accessKey: accessKey
        }

        ws.send(JSON.stringify(token));
    },
    inventoryAdd: function (heroId, artifactId) {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'inventoryAdd',
            heroId: heroId,
            artifactId: artifactId
        }

        ws.send(JSON.stringify(token));
    },
    inventoryDel: function (heroId) {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'inventoryDel',
            heroId: heroId
        };

        ws.send(JSON.stringify(token));
    },
    production: function (castleId, unitId, relocationToCastleId) {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        if (!unitId) {
            Message.error('Error');
            return;
        }

        var token = {
            type: 'production',
            castleId: castleId,
            unitId: unitId,
            relocationToCastleId: relocationToCastleId
        };

        ws.send(JSON.stringify(token));
    },
    tower: function (towerId) {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'tower',
            towerId: towerId
        };

        ws.send(JSON.stringify(token));
    },
    surrender: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'surrender'
        };

        ws.send(JSON.stringify(token));
    },
    chat: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var msg = $('#msg').val();

        if (msg) {
            $('#msg').val('');

            var token = {
                type: 'chat',
                msg: msg
            };

            ws.send(JSON.stringify(token));
        }
    },
    computer: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        if (!my.game) {
            return
        }

        if (!players[Turn.color].computer) {
            return
        }

        if (stop) {
            return
        }

        if (Move.moving) {
            return
        }

        var token = {
            type: 'computer'
        };

        ws.send(JSON.stringify(token));
    },
    ruin: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        if (!my.turn) {
            return;
        }
        if (Army.selected == null) {
            return;
        }

        Army.deselect();

        var token = {
            type: 'ruin',
            armyId: Army.deselected.armyId
        };

        ws.send(JSON.stringify(token));
    },
    fortify: function (armyId) {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        if (!my.turn) {
            return;
        }

        var token = {
            type: 'fortify',
            armyId: armyId,
            fortify: 1
        };

        ws.send(JSON.stringify(token));
    },
    unfortify: function (armyId) {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        if (!my.turn) {
            return;
        }

        var token = {
            type: 'fortify',
            armyId: armyId,
            fortify: 0
        };

        ws.send(JSON.stringify(token));
    },
    join: function (armyId) {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }


        if (!my.turn) {
            return;
        }

        var token = {
            type: 'join',
            armyId: armyId
        };

        ws.send(JSON.stringify(token));
    },
    disband: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        if (!my.turn) {
            return;
        }
        if (Army.selected == null) {
            return;
        }
        Army.deselect(1);

        var token = {
            type: 'disband',
            armyId: Army.deselected.armyId
        };

        ws.send(JSON.stringify(token));
    },
    move: function (x, y) {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        if (!my.turn) {
            Message.error(translations.itIsNotYourTurn);
            return;
        }

        var h = '';
        var s = '';

        if (Army.selected.soldierSplitKey !== null) {
//            console.log(Army.selected.soldiers[Army.selected.soldierSplitKey])
            s = Army.selected.soldiers[Army.selected.soldierSplitKey].soldierId
        }
        if (Army.selected.heroSplitKey !== null) {
//            console.log(Army.selected.heroes[Army.selected.heroSplitKey])
            h = Army.selected.heroes[Army.selected.heroSplitKey].heroId
        }

        Army.halfDeselect();

        Gui.setLock();

        var token = {
            type: 'move',
            x: x,
            y: y,
            armyId: Army.deselected.armyId,
            s: s,
            h: h
        };


        ws.send(JSON.stringify(token));
    },
    split: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        if (!my.turn) {
            Message.error(translations.itIsNotYourTurn);
            return;
        }
        var h = '';
        var s = '';

        $('.message input[type="checkbox"]:checked').each(function () {
            if ($(this).attr('name') == 'heroId') {
                if (h) {
                    h += ',';
                }
                h += $(this).val();
            } else {
                if (s) {
                    s += ',';
                }
                s += $(this).val();
            }
        });

        if (!s && !h) {
            return;
        }

        var token = {
            type: 'split',
            armyId: Army.selected.armyId,
            s: s,
            h: h
        };

        ws.send(JSON.stringify(token));
    },
    resurrection: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        if (!my.turn) {
            return;
        }

        if (Hero.findMy()) {
            return;
        }

        Army.deselect();

        var token = {
            type: 'resurrection'
        };

        ws.send(JSON.stringify(token));
    },
    hire: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        if (!my.turn) {
            return;
        }

        Army.deselect();

        var token = {
            type: 'hire'
        };

        ws.send(JSON.stringify(token));
    },
    raze: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var castleId = Castle.getMy(Army.selected.x, Army.selected.y);

        if (!castleId) {
            Message.error(translations.noCastleToDestroy);
            return;
        }

        var token = {
            type: 'raze',
            armyId: Army.selected.armyId
        };

        ws.send(JSON.stringify(token));
    },
    defense: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var castleId = Castle.getMy(Army.selected.x, Army.selected.y);

        if (!castleId) {
            Message.error(translations.noCastleToBuildDefense);
            return;
        }

        var token = {
            type: 'defense',
            castleId: castleId
        };

        ws.send(JSON.stringify(token));
    },
    startMyTurn: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'startTurn'
        };

        ws.send(JSON.stringify(token));
    },
    nextTurn: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'nextTurn'
        };

        ws.send(JSON.stringify(token));
    },
    statistics: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'statistics'
        };

        ws.send(JSON.stringify(token));
    },
    battleAttack: function () {
        Websocket.battleConfiguration(1)
    },
    battleDefence: function () {
        Websocket.battleConfiguration(0)
    },
    battleConfiguration: function (attack) {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var sequence = {},
            i = 0

        $('.battleUnit img').each(function () {
            i++
            sequence[i] = $(this).attr('id')
        })

        var token = {
            type: 'bSequence',
            attack: attack,
            sequence: sequence
        }

        ws.send(JSON.stringify(token));
    }
}
