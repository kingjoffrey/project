var Websocket = {
    closed: null,
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
                Move.start(r, Websocket.i)
                break

            case 'startTurn':
                if (Me.colorEquals(r.color)) {
                    Me.setSelectedCastleId(0)
                    var castles = Players.get(r.color).getCastles()
                    for (var castleId in r.castles) {
                        castles.get(castleId).setProductionTurn(r.castles[castleId].productionTurn)
                    }
                    Me.resetSkippedArmies()
                    Sound.play('startturn')
                    Me.setGold(r.gold)
                    Gui.unlock()
                } else {
                    Players.showFirst(r.color)
                }

                var armies = Players.get(r.color).getArmies()
                for (var armyId in r.armies) {
                    armies.handle(r.armies[armyId])
                }
                this.executing = 0
                break;

            case 'nextTurn':
                Me.deselectArmy()
                Turn.change(r.color, r.nr)
                if (Players.get(r.color).isComputer()) {
                    this.computer()
                }
                this.executing = 0
                break;

            case 'ruin':
                //board.append($('<div>').addClass('ruinSearch').css({'top': Y + 'px', 'left': X + 'px'}));
                Zoom.lens.setcenter(r.army.x, r.army.y);
                Ruins.get(r.ruin.ruinId).update(r.ruin.empty)
                if (Me.colorEquals(r.color)) {
                    switch (r.find[0]) {
                        case 'gold':
                            Sound.play('gold1');
                            Me.goldIncrement(r.find[1])
                            Message.simple(translations.ruins, translations.youHaveFound + ' ' + r.find[1] + ' ' + translations.gold);
                            break;
                        case 'death':
                            Sound.play('death');
                            Message.simple(translations.ruins, translations.youHaveFound + ' ' + translations.death);
                            Players.get(r.color).getArmies().delete(r.army.id)
                            if (!Hero.findMy()) {
                                $('#heroResurrection').removeClass('buttonOff')
                            }
                            break
                        case 'allies':
                            Sound.play('allies');
                            Players.get(r.color).getArmies().get(r.army.id).update(r.army)
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
                //$('.ruinSearch').animate({'display': 'none'}, 1000, function () {
                //    $('.ruinSearch').remove()
                //})
                Websocket.executing = 0
                break;

            case 'split':
                var armies = Players.get(r.color).getArmies()
                armies.handle(r.parentArmy)
                armies.handle(r.childArmy)

                if (Turn.isMy()) {
                    Message.remove()
                    Me.setParentArmyId(r.parentArmy.id)
                    Me.selectArmy(r.childArmy.id)
                } else {
                    //zoomer.setCenterIfOutOfScreen(r.parentArmy.x * 40, r.parentArmy.y * 40);
                    Zoom.lens.setcenter(r.parentArmy.x, r.parentArmy.y);
                }
                this.executing = 0
                break;

            case 'join':
                if (Turn.isMy()) {
                    Message.remove()
                }
                //zoomer.setCenterIfOutOfScreen(r.army.x * 40, r.army.y * 40);
                Zoom.lens.setcenter(r.army.x, r.army.y)
                var armies = Players.get(r.color).getArmies()
                for (var i in r.deletedIds) {
                    armies.delete(r.deletedIds[i])
                }
                armies.handle(r.army)
                this.executing = 0
                break;

            case 'disband':
                if (Turn.isMy()) {
                    Message.remove()
                    var upkeep = 0,
                        soldiers = Players.get(r.color).getArmies().get(r.id).getWalkingSoldiers()
                    for (var i in soldiers) {
                        upkeep += Units.get(soldiers[i].unitId).cost
                    }
                    Me.costIncrement(-upkeep)

                    if (!Hero.findMy()) {
                        $('#heroResurrection').removeClass('buttonOff')
                    }
                }
                Players.get(r.color).getArmies().delete(r.id)

                this.executing = 0
                break;

            case 'resurrection':
                Sound.play('resurrection');
                Zoom.lens.setcenter(r.army.x, r.army.y)
                Players.get(r.color).getArmies().handle(r.army)
                if (Turn.isMy()) {
                    Message.remove()
                    Me.goldIncrement(-r.gold)
                    if (Hero.findMy()) {
                        $('#heroResurrection').addClass('buttonOff')
                    }
                }
                this.executing = 0
                break;

            case 'raze':
                $('#razeCastle').addClass('buttonOff');
                Players.get(r.color).getCastles().raze(r.castleId)
                if (Turn.isMy()) {
                    Sound.play('gold1');
                    Message.remove()
                    Me.setGold(r.gold)
                } else {
                    Sound.play('raze');
                }
                this.executing = 0
                break;

            case 'defense':
                Players.get(r.color).getCastles().get(r.castleId).setDefense(r.defense)
                if (Turn.isMy()) {
                    Message.remove()
                    Me.setGold(r.gold)
                }
                this.executing = 0
                break;

            case 'surrender':
                Me.deselectArmy()
                var armies = Players.get(r.color).getArmies(),
                    castles = Players.get(r.color).getCastles()
                for (var armyId in armies.toArray()) {
                    armies.delete(armyId)
                }
                for (var castleId in castles.toArray()) {
                    castles.raze(castleId)
                }
                if (Turn.getColor() == r.color) {
                    this.nextTurn()
                }
                this.executing = 0
                break;

            case 'end':
                Turn.off()
                Message.end()
                this.executing = 0
                break;

            case 'dead':
                if (!Players.hasSkull(r.color)) {
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
                console.log(r)

                switch (r.type) {

                    case 'move':
                        Websocket.addQueue(r)
                        break;

                    case 'tower':
                        var field = Fields.get(r.x, r.y),
                            towerId = field.getTowerId(),
                            towerColor = field.getTowerColor(),
                            towers = Players.get(towerColor).getTowers(),
                            tower = towers.get(towerId)
                        Players.get(r.color).getTowers().add(towerId, tower)
                        towers.remove(towerId)
                        if (Me.colorEquals(towerColor)) {
                            Me.incomeIncrement(-5)
                        }
                        if (Me.colorEquals(r.color)) {
                            Me.incomeIncrement(5)
                        }
                        break

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
                        Game.init(r)
                        break;

                    case 'close':
                        Players.setOnline(r.color, 0)
                        break;

                    case 'chat':
                        Chat.message(r.color, r.msg, makeTime());
                        break;

                    case 'production':
                        var castle = Me.getCastle(r.castleId)
                        castle.setProductionId(r.unitId)
                        castle.setProductionTurn(0)
                        castle.setRelocationCastleId(r.relocationToCastleId)
                        if (isTruthful(r.relocationToCastleId)) {
                            Message.simple(translations.production, translations.productionRelocated)
                        } else {
                            if (r.unitId === null) {
                                Message.simple(translations.production, translations.productionStopped)
                            } else {
                                Message.simple(translations.production, translations.productionSet)
                            }
                        }
                        break

                    case 'statistics':
                        Message.statistics(r);
                        break;

                    case 'bSequence':
                        if (r.attack == 'true') {
                            Message.simple(translations.battleSequence, translations.attackSequenceSuccessfullyUpdated)
                            Me.setAttackBattleSequence(r.sequence)
                        } else {
                            Message.simple(translations.battleSequence, translations.defenceSequenceSuccessfullyUpdated)
                            Me.setDefenseBattleSequence(r.sequence)
                        }
                        break

                    default:
                        console.log(r);

                }
            }
        }

        ws.onclose = function () {
            if (!Websocket.closed) {
                Message.error('Connection error. Can\'t connect to WebSocket server')
                Websocket.closed = true
            }
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
            playerId: id,
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
    ruin: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        if (!Turn.isMy()) {
            return;
        }
        if (!Me.getSelectedArmyId()) {
            return;
        }

        Me.deselectArmy()

        var token = {
            type: 'ruin',
            armyId: Me.getDeselectedArmyId()
        };

        ws.send(JSON.stringify(token));
    },
    fortify: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        if (!Turn.isMy()) {
            return
        }
        if (Gui.lock) {
            return
        }

        var armyId

        if (!(armyId = Me.getSelectedArmyId())) {
            return
        }

        Me.addQuited(armyId)
        Me.deselectArmy()
        Me.findNext()

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

        if (!Turn.isMy()) {
            return
        }
        if (Gui.lock) {
            return
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

        if (!Turn.isMy()) {
            return;
        }

        Me.setParentArmyId(null)
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

        if (!Turn.isMy()) {
            return;
        }
        if (!Me.getSelectedArmyId()) {
            return
        }
        Me.deselectArmy(1)

        var token = {
            type: 'disband',
            armyId: Me.getDeselectedArmyId()
        };

        ws.send(JSON.stringify(token));
    },
    move: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        if (!Turn.isMy()) {
            Message.error(translations.itIsNotYourTurn);
            return;
        }

        var s = Me.getSelectedSoldierSplitKey()
        var h = Me.getSelectedHeroSplitKey()
        var armyId = Me.getSelectedArmyId()

        Gui.setLock()
        Me.deselectArmy(1)

        var token = {
            type: 'move',
            x: AStar.getX(),
            y: AStar.getY(),
            armyId: armyId,
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

        if (!Turn.isMy()) {
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
            armyId: Me.getSelectedArmyId(),
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

        if (!Turn.isMy()) {
            return;
        }

        if (Hero.findMy()) {
            return;
        }

        Me.deselectArmy()

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

        if (!Turn.isMy()) {
            return;
        }

        Me.deselectArmy()

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

        var army = Me.getArmy(Me.getSelectedArmyId())
        var castleId = Fields.get(army.getX(), army.getY()).getCastleId()

        if (!castleId) {
            Message.error(translations.noCastleToDestroy);
            return;
        }

        var token = {
            type: 'raze',
            armyId: Me.getSelectedArmyId()
        };

        ws.send(JSON.stringify(token));
    },
    defense: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var army = Me.getArmy(Me.getSelectedArmyId())
        var castleId = Fields.get(army.getX(), army.getY()).getCastleId()

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
    },
    computer: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }


        //if (Game.getStop()) {
        //    return
        //}


        var token = {
            type: 'computer'
        }

        ws.send(JSON.stringify(token));
    }
}
