var WebSocketGame = new function () {
    var closed = true,
        queue = {},
        executing = 0,
        i = 0,
        addQueue = function (r) {
            i++
            queue[i] = r
            WebSocketGame.wait()
        },
        execute = function (r) {
            executing = 1
            console.log(r)
            switch (r.type) {
                case 'move':
                    Move.start(r, i)
                    break

                case 'nextTurn':
                    Turn.change(r.color, r.nr)
                    if (Players.get(r.color).isComputer()) {
                        WebSocketGame.computer()
                    }
                    if (Players.get(r.color).isComputer() && !Gui.getShow()) {
                        WebSocketGame.setExecuting(0)
                    } else {
                        Players.showFirst(r.color, function () {
                            WebSocketGame.setExecuting(0)
                        })
                    }
                    break;

                case 'neutral':
                    var armies = Players.get('neutral').getArmies()
                    for (var armyId in r.armies) {
                        armies.handle(r.armies[armyId])
                    }
                    WebSocketGame.setExecuting(0)
                    break;

                case 'startTurn':
                    var armies = Players.get(r.color).getArmies()
                    for (var armyId in r.armies) {
                        armies.handle(r.armies[armyId])
                    }
                    WebSocketGame.setExecuting(0)
                    break;

                case 'ruin':
                    if (Players.get(r.color).isComputer() && !Gui.getShow()) {
                        Ruins.handle(r)
                        WebSocketGame.setExecuting(0)
                    } else {
                        Zoom.getLens().setcenter(r.army.x, r.army.y, function () {
                            Ruins.handle(r)
                            WebSocketGame.setExecuting(0)
                        })
                    }
                    break;

                case 'split':
                    if (Turn.isMy()) {
                        var armies = Players.get(r.color).getArmies()
                        armies.handle(r.parentArmy)
                        armies.handle(r.childArmy)
                        Message.remove()
                        Me.setParentArmyId(r.parentArmy.id)
                        Me.selectArmy(r.childArmy.id)
                        WebSocketGame.setExecuting(0)
                    } else if (Players.get(r.color).isComputer() && !Gui.getShow()) {
                        //zoomer.setCenterIfOutOfScreen(r.parentArmy.x * 40, r.parentArmy.y * 40);
                        var armies = Players.get(r.color).getArmies()
                        armies.handle(r.parentArmy)
                        armies.handle(r.childArmy)
                        WebSocketGame.setExecuting(0)

                    } else {
                        Zoom.getLens().setcenter(r.parentArmy.x, r.parentArmy.y, function () {
                            var armies = Players.get(r.color).getArmies()
                            armies.handle(r.parentArmy)
                            armies.handle(r.childArmy)
                            WebSocketGame.setExecuting(0)
                        })
                    }
                    break;

                case 'join':
                    if (Turn.isMy()) {
                        Message.remove()
                    }
                    //zoomer.setCenterIfOutOfScreen(r.army.x * 40, r.army.y * 40);
                    if (Players.get(r.color).isComputer() && !Gui.getShow()) {
                        var armies = Players.get(r.color).getArmies()
                        for (var i in r.deletedIds) {
                            armies.destroy(r.deletedIds[i])
                        }
                        armies.handle(r.army)
                        WebSocketGame.setExecuting(0)
                    } else {
                        Zoom.getLens().setcenter(r.army.x, r.army.y, function () {
                            var armies = Players.get(r.color).getArmies()
                            for (var i in r.deletedIds) {
                                armies.destroy(r.deletedIds[i])
                            }
                            armies.handle(r.army)
                            WebSocketGame.setExecuting(0)
                        })
                    }
                    break;

                case 'disband':
                    if (Turn.isMy()) {
                        Message.remove()
                        var upkeep = 0,
                            army = Me.getArmies().get(r.id)

                        for (var i in army.getWalkingSoldiers()) {
                            upkeep += Units.get(army.getWalkingSoldier(i).unitId).cost
                        }
                        for (var i in army.getSwimmingSoldiers()) {
                            upkeep += Units.get(army.getSwimmingSoldier(i).unitId).cost
                        }
                        for (var i in army.getFlyingSoldiers()) {
                            upkeep += Units.get(army.getFlyingSoldier(i).unitId).cost
                        }
                        Me.upkeepIncrement(-upkeep)
                    }
                    Players.get(r.color).getArmies().destroy(r.id)
                    if (Turn.isMy()) {
                        Me.handleHeroButtons()
                    }
                    WebSocketGame.setExecuting(0)
                    break;

                case 'resurrection':
                    Sound.play('resurrection');
                    if (Players.get(Turn.getColor()).isComputer() && !Gui.getShow()) {
                        Players.get(r.color).getArmies().handle(r.army)
                        WebSocketGame.setExecuting(0)
                    } else {
                        Zoom.getLens().setcenter(r.army.x, r.army.y, function () {
                            Players.get(r.color).getArmies().handle(r.army)
                            if (Turn.isMy()) {
                                Message.remove()
                                Me.setGold(r.gold)
                                Me.handleHeroButtons()
                            }
                            WebSocketGame.setExecuting(0)
                        })
                    }
                    break

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
                    WebSocketGame.setExecuting(0)
                    break;

                case 'defense':
                    Players.get(r.color).getCastles().get(r.castleId).setDefense(r.defense)
                    if (Turn.isMy()) {
                        Message.remove()
                        Me.setGold(r.gold)
                    }
                    WebSocketGame.setExecuting(0)
                    break;

                case 'surrender':
                    Me.deselectArmy()
                    var armies = Players.get(r.color).getArmies(),
                        castles = Players.get(r.color).getCastles()
                    for (var armyId in armies.toArray()) {
                        armies.destroy(armyId)
                    }
                    for (var castleId in castles.toArray()) {
                        castles.raze(castleId)
                    }
                    if (Turn.getColor() == r.color) {
                        WebSocketGame.nextTurn()
                    }
                    WebSocketGame.setExecuting(0)
                    break;

                case 'end':
                    if (Game.getLoading()) {
                        Gui.end()
                    } else {
                        Me.turnOff()
                        var id = Message.show(translations.gameOver, $('<div>').append($('<div>').html(translations.thisIsTheEnd)))
                        Message.ok(id, Gui.end)
                    }
                    break;

                case 'dead':
                    if (!GamePlayers.hasSkull(r.color)) {
                        GamePlayers.drawSkull(r.color)
                    }
                    WebSocketGame.setExecuting(0)
                    break;
            }
        },
        open = function () {
            if (closed) {
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

            ws.send(JSON.stringify(token))
        },
        message = function (r) {
            //console.log(r)
            switch (r.type) {
                case 'move':
                    addQueue(r)
                    break;

                case 'tower':
                    var field = Fields.get(r.x, r.y),
                        towerId = field.getTowerId(),
                        towerColor = field.getTowerColor(),
                        towers = Players.get(towerColor).getTowers(),
                        tower = towers.get(towerId)
                    Players.get(r.color).getTowers().add(towerId, tower)
                    towers.delete(towerId)
                    if (Me.colorEquals(towerColor)) {
                        Me.incomeIncrement(-5)
                    }
                    if (Me.colorEquals(r.color)) {
                        Me.incomeIncrement(5)
                    }
                    break

                case 'update':
                    Me.setSelectedCastleId(0)
                    Me.resetSkippedArmies()

                    var castles = Me.getCastles()
                    for (var castleId in r.productionTurns) {
                        castles.get(castleId).setProductionTurn(r.productionTurns[castleId])
                    }
                    Sound.play('startturn')

                    Me.setUpkeep(r.upkeep)
                    Me.setGold(r.gold)
                    Me.setIncome(r.income)
                    Gui.unlock()
                    break

                case 'nextTurn':
                    addQueue(r)
                    break;

                case 'neutral':
                    addQueue(r)
                    break;

                case 'startTurn':
                    addQueue(r)
                    break;

                case 'ruin':
                    addQueue(r)
                    break;

                case 'split':
                    addQueue(r)
                    break;

                case 'join':
                    addQueue(r)
                    break;

                case 'disband':
                    addQueue(r)
                    break;

                case 'resurrection':
                    addQueue(r)
                    break;

                case 'raze':
                    addQueue(r)
                    break;

                case 'defense':
                    addQueue(r)
                    break;

                case 'surrender':
                    addQueue(r)
                    break;

                case 'end':
                    addQueue(r)
                    break;

                case 'dead':
                    addQueue(r)
                    break;

                case 'error':
                    Message.error(r.msg);
                    Gui.unlock();
                    break;

                case 'open':
                    Game.init(r)
                    break;

                case 'close':
                    GamePlayers.setOnline(r.color, 0)
                    break;

                case 'online':
                    if (!Me.colorEquals(r.color)) {
                        GamePlayers.setOnline(r.color, 1)
                    }
                    break;

                case 'chat':
                    Chat.message(r.color, r.msg, makeTime())
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
                    StatisticsWindow.show(r)
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
    this.wait = function () {
        if (executing) {
            setTimeout('WebSocketGame.wait()', 500)
        } else {
            for (var ii in queue) {
                execute(queue[ii])
                delete queue[ii]
                return
            }
        }
    }
    this.setExecuting = function (value) {
        executing = value
    }
    this.getI = function () {
        return i
    }
    this.isClosed = function () {
        return closed
    }
    this.init = function () {
        ws = new WebSocket(wsURL + '/game')

        ws.onopen = function () {
            closed = false
            open()
        }

        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data);
            message(r)
        }

        ws.onclose = function () {
            closed = true
            setTimeout('WebSocketGame.init()', 1000)
        }
    }

    this.ruin = function () {
        if (closed) {
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
    }

    this.fortify = function () {
        if (closed) {
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
    }

    this.unfortify = function (armyId) {
        if (closed) {
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
    }

    this.join = function (armyId) {
        if (closed) {
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
    }

    this.disband = function () {
        if (closed) {
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
    }

    this.move = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        if (!Turn.isMy()) {
            Message.error(translations.itIsNotYourTurn);
            return;
        }

        var armyId = Me.getSelectedArmyId()

        Gui.setLock()
        Me.deselectArmy(1)

        var token = {
            type: 'move',
            x: AStar.getX(),
            y: AStar.getY(),
            armyId: armyId
        }

        ws.send(JSON.stringify(token))
    }

    this.split = function () {
        if (closed) {
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
    }

    this.resurrection = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        //if (!Turn.isMy()) {
        //    return;
        //}

        //if (Me.findHero()) {
        //    return;
        //}

        Me.deselectArmy()

        var token = {
            type: 'resurrection'
        };

        ws.send(JSON.stringify(token));
    }

    this.hire = function () {
        if (closed) {
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
    }

    this.raze = function () {
        if (closed) {
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
    }

    this.defense = function () {
        if (closed) {
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
    }

    this.startMyTurn = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'startTurn'
        };

        ws.send(JSON.stringify(token));
    }

    this.nextTurn = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'nextTurn'
        };

        ws.send(JSON.stringify(token));
    }

    this.statistics = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'statistics'
        };

        ws.send(JSON.stringify(token));
    }

    this.battleAttack = function () {
        WebSocketGame.battleConfiguration(1)
    }

    this.battleDefence = function () {
        WebSocketGame.battleConfiguration(0)
    }

    this.battleConfiguration = function (attack) {
        if (closed) {
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

    this.computer = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'computer'
        }

        ws.send(JSON.stringify(token));
    }

    this.production = function (castleId, unitId, relocationToCastleId) {
        if (closed) {
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
    }

    this.surrender = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'surrender'
        };

        ws.send(JSON.stringify(token));
    }
    this.chat = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var msg = $('#msg').val();

        if (msg) {
            $('#msg').val('');

            var token = {
                type: 'chat',
                msg: msg
            }

            ws.send(JSON.stringify(token));
        }
    }
}
