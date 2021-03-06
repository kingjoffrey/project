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

                case 'yourTurn':
                    Me.setSelectedCastleId(0)

                    var castles = Me.getCastles()
                    for (var castleId in r.productionTurns) {
                        castles.get(castleId).setProductionTurn(r.productionTurns[castleId])
                    }

                    Me.setGold(r.gold)

                    Execute.setExecuting(0)

                    if (r.seven) {
                        Message.simple(translations.ItisSunday, translations.Allruinsarefull)
                        for(var ruinId in Ruins.toArray()){
                            Ruins.get(ruinId).update(false)
                        }
                    }

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
                    if (isSet(r.bonus)) {
                        switch (r.bonus) {
                            case 1:
                                var txt = translations.Attack
                                break
                            case 2:
                                var txt = translations.Defense
                                break
                            case 3:
                                var txt = translations.Moves
                                break
                        }
                        Message.simple(translations.Bonus, txt + ' +1')
                        Execute.setExecuting(0)
                    } else {
                        if (Players.get(r.color).isComputer() && !GameGui.getShow()) {
                            Ruins.handle(r)
                            Execute.setExecuting(0)
                            if (Turn.isMy()) {
                                GameGui.unlock()
                            }
                        } else {
                            GameScene.centerOn(r.army.x, r.army.y, function () {
                                setTimeout(function () {
                                    Ruins.handle(r)
                                    Execute.setExecuting(0)
                                    if (Turn.isMy()) {
                                        GameGui.unlock()
                                    }
                                }, 2000)
                            })
                        }
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
                        GameGui.unlock()
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
                        GameGui.unlock()
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
                        GameGui.unlock()
                    }
                    Players.get(r.color).getArmies().destroy(r.id)
                    Execute.setExecuting(0)
                    break;

                case 'bulbul':
                    if (Turn.isMy()) {
                        Message.remove()
                        GameGui.unlock()
                        Message.simple(translations.Moves, translations.Yourshipsank)
                    }
                    Players.get(r.color).getArmies().destroy(r.id)
                    Execute.setExecuting(0)
                    break;

                case 'resurrection':
                    Sound.play('resurrection')

                    if (Me.colorEquals(r.color)) {
                        CastleWindow.hide()
                        Me.goldIncrement(-r.gold)
                    }

                    if (Players.get(Turn.getColor()).isComputer() && !GameGui.getShow()) {
                        Players.get(r.color).getArmies().handle(r.army)
                        Execute.setExecuting(0)
                    } else {
                        GameScene.centerOn(r.army.x, r.army.y, function () {
                            Players.get(r.color).getArmies().handle(r.army)
                            if (Me.colorEquals(r.color)) {
                                Message.remove()
                                GameGui.unlock()
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
                        GameGui.unlock()
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
                        GameGui.unlock()
                        Me.goldIncrement(-r.gold)
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
                    var armies = Players.get(r.color).getArmies(),
                        castles = Players.get(r.color).getCastles()

                    if (Me.getColor() == r.color) {
                        Me.deselectArmy()
                        WebSocketSendGame.nextTurn()
                    }

                    for (var armyId in armies.toArray()) {
                        armies.destroy(armyId)
                    }

                    for (var castleId in castles.toArray()) {
                        castles.raze(castleId)
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
    }
}
