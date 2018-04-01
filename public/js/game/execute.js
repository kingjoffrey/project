"use strict"
var Execute = new function () {
    var queue = {},
        executing = 0,
        i = 0,
        execute = function (r) {
            Execute.setExecuting(1)

            // console.log(r)
            switch (r.type) {
                case 'move':
                    Move.start(r, i)
                    break

                case 'nextTurn':
                    Turn.change(r.color, r.nr)
                    break

                case 'update':
                    Me.setSelectedCastleId(0)

                    var castles = Me.getCastles()
                    for (var castleId in r.productionTurns) {
                        castles.get(castleId).setProductionTurn(r.productionTurns[castleId])
                    }
                    Sound.play('startturn')

                    Me.setUpkeep(r.upkeep)
                    Me.setGold(r.gold)
                    Me.setIncome(r.income)
                    GameGui.unlock()

                    Execute.setExecuting(0)
                    break

                case 'neutral':
                    var armies = Players.get('neutral').getArmies()
                    for (var armyId in r.armies) {
                        armies.handle(r.armies[armyId])
                    }

                    Execute.setExecuting(0)
                    break

                case 'startTurn':
                    var armies = Players.get(r.color).getArmies()
                    for (var armyId in r.armies) {
                        armies.handle(r.armies[armyId])
                    }

                    Turn.start(r.color)

                    break

                case 'ruin':
                    if (Players.get(r.color).isComputer() && !GameGui.getShow()) {
                        Ruins.handle(r)
                        Execute.setExecuting(0)
                    } else {
                        GameScene.centerOn(r.army.x, r.army.y, function () {
                            Ruins.handle(r)
                            Execute.setExecuting(0)
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
                        Execute.setExecuting(0)
                    } else if (Players.get(r.color).isComputer() && !GameGui.getShow()) {
                        //zoomer.setCenterIfOutOfScreen(r.parentArmy.x * 40, r.parentArmy.y * 40);
                        var armies = Players.get(r.color).getArmies()
                        armies.handle(r.parentArmy)
                        armies.handle(r.childArmy)
                        Execute.setExecuting(0)

                    } else {
                        GameScene.centerOn(r.parentArmy.x, r.parentArmy.y, function () {
                            var armies = Players.get(r.color).getArmies()
                            armies.handle(r.parentArmy)
                            armies.handle(r.childArmy)
                            Execute.setExecuting(0)
                        })
                    }
                    break;

                case 'join':
                    if (Turn.isMy()) {
                        Message.remove()
                    }
                    //zoomer.setCenterIfOutOfScreen(r.army.x * 40, r.army.y * 40);
                    if (Players.get(r.color).isComputer() && !GameGui.getShow()) {
                        var armies = Players.get(r.color).getArmies()
                        for (var i in r.deletedIds) {
                            armies.destroy(r.deletedIds[i])
                        }
                        armies.handle(r.army)
                        Execute.setExecuting(0)
                    } else {
                        GameScene.centerOn(r.army.x, r.army.y, function () {
                            var armies = Players.get(r.color).getArmies()
                            for (var i in r.deletedIds) {
                                armies.destroy(r.deletedIds[i])
                            }
                            armies.handle(r.army)
                            Execute.setExecuting(0)
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
                    Execute.setExecuting(0)
                    break;

                case 'resurrection':
                    Sound.play('resurrection');
                    if (Players.get(Turn.getColor()).isComputer() && !GameGui.getShow()) {
                        Players.get(r.color).getArmies().handle(r.army)
                        Execute.setExecuting(0)
                    } else {
                        GameScene.centerOn(r.army.x, r.army.y, function () {
                            Players.get(r.color).getArmies().handle(r.army)
                            if (Me.colorEquals(r.color)) {
                                Message.remove()
                                Me.goldIncrement(-r.gold)
                            }
                            Execute.setExecuting(0)
                        })
                    }
                    break

                case 'raze':
                    Players.get(r.color).getCastles().raze(r.castleId)
                    if (Me.colorEquals(r.color)) {
                        Sound.play('gold1');
                        Message.remove()
                        Me.goldIncrement(r.gold)
                        Execute.setExecuting(0)
                    } else {
                        if (Players.get(Turn.getColor()).isComputer() && !GameGui.getShow()) {
                            Execute.setExecuting(0)
                        } else {
                            Sound.play('raze');
                            var castle = Players.get(r.color).getCastles().get(r.castleId)
                            GameScene.centerOn(castle.getX(), castle.getY(), function () {
                                Execute.setExecuting(0)
                            })

                        }
                    }
                    break

                case 'defense':
                    var castle = Players.get(r.color).getCastles().get(r.castleId)
                    castle.setDefense(r.defense)
                    if (Me.colorEquals(r.color)) {
                        Message.remove()
                        Me.goldIncrement(-r.gold)
                        Me.incomeIncrement(Me.getCastle(r.castleId).getIncome() / r.defense)
                        Execute.setExecuting(0)
                    } else {
                        if (Players.get(Turn.getColor()).isComputer() && !GameGui.getShow()) {
                            Execute.setExecuting(0)
                        } else {
                            GameScene.centerOn(castle.getX(), castle.getY(), function () {
                                Execute.setExecuting(0)
                            })
                        }
                    }
                    break

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
                        WebSocketSendGame.nextTurn()
                    }

                    Execute.setExecuting(0)
                    break

                case 'end':
                    if (Game.getLoading()) {
                        GameGui.end()
                    } else {
                        GameRenderer.start()
                        $('#gameMenu').hide()
                        $('#turnInfo').hide()
                        Me.turnOff()
                        var id = Message.show(translations.gameOver, $('<div>').append($('<div>').html(translations.thisIsTheEnd)))
                        Message.addButton(id, 'ok', GameGui.end)
                    }
                    break

                case 'dead':
                    console.log(r)
                    Execute.setExecuting(0)
                    break
            }
        }
    this.addQueue = function (r) {
        i++
        queue[i] = r
        Execute.wait()
    }
    this.wait = function () {
        if (executing) {
            setTimeout('Execute.wait()', 500)
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

        PickerCommon.setCursorLock(executing)
        PickerCommon.cursor(0)
    }
}
