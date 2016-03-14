"use strict"
var Execute = new function () {
    var queue = {},
        executing = 0,
        i = 0,
        execute = function (r) {
            executing = 1
            //console.log(r)
            switch (r.type) {
                case 'move':
                    Move.start(r, i)
                    break

                case 'nextTurn':
                    Turn.change(r.color, r.nr)
                    if (Players.get(r.color).isComputer()) {
                        WebSocketSend.computer()
                    }
                    if (Players.get(r.color).isComputer() && !Gui.getShow()) {
                        Execute.setExecuting(0)
                    } else {
                        Players.showFirst(r.color, function () {
                            Execute.setExecuting(0)
                        })
                    }
                    break;

                case 'neutral':
                    var armies = Players.get('neutral').getArmies()
                    for (var armyId in r.armies) {
                        armies.handle(r.armies[armyId])
                    }
                    Execute.setExecuting(0)
                    break;

                case 'startTurn':
                    var armies = Players.get(r.color).getArmies()
                    for (var armyId in r.armies) {
                        armies.handle(r.armies[armyId])
                    }
                    Execute.setExecuting(0)
                    break;

                case 'ruin':
                    if (Players.get(r.color).isComputer() && !Gui.getShow()) {
                        Ruins.handle(r)
                        Execute.setExecuting(0)
                    } else {
                        Zoom.getLens().setcenter(r.army.x, r.army.y, function () {
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
                        CommonMe.setParentArmyId(r.parentArmy.id)
                        CommonMe.selectArmy(r.childArmy.id)
                        Execute.setExecuting(0)
                    } else if (Players.get(r.color).isComputer() && !Gui.getShow()) {
                        //zoomer.setCenterIfOutOfScreen(r.parentArmy.x * 40, r.parentArmy.y * 40);
                        var armies = Players.get(r.color).getArmies()
                        armies.handle(r.parentArmy)
                        armies.handle(r.childArmy)
                        Execute.setExecuting(0)

                    } else {
                        Zoom.getLens().setcenter(r.parentArmy.x, r.parentArmy.y, function () {
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
                    if (Players.get(r.color).isComputer() && !Gui.getShow()) {
                        var armies = Players.get(r.color).getArmies()
                        for (var i in r.deletedIds) {
                            armies.destroy(r.deletedIds[i])
                        }
                        armies.handle(r.army)
                        Execute.setExecuting(0)
                    } else {
                        Zoom.getLens().setcenter(r.army.x, r.army.y, function () {
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
                            army = CommonMe.getArmies().get(r.id)

                        for (var i in army.getWalkingSoldiers()) {
                            upkeep += Units.get(army.getWalkingSoldier(i).unitId).cost
                        }
                        for (var i in army.getSwimmingSoldiers()) {
                            upkeep += Units.get(army.getSwimmingSoldier(i).unitId).cost
                        }
                        for (var i in army.getFlyingSoldiers()) {
                            upkeep += Units.get(army.getFlyingSoldier(i).unitId).cost
                        }
                        CommonMe.upkeepIncrement(-upkeep)
                    }
                    Players.get(r.color).getArmies().destroy(r.id)
                    if (Turn.isMy()) {
                        CommonMe.handleHeroButtons()
                    }
                    Execute.setExecuting(0)
                    break;

                case 'resurrection':
                    Sound.play('resurrection');
                    if (Players.get(Turn.getColor()).isComputer() && !Gui.getShow()) {
                        Players.get(r.color).getArmies().handle(r.army)
                        Execute.setExecuting(0)
                    } else {
                        Zoom.getLens().setcenter(r.army.x, r.army.y, function () {
                            Players.get(r.color).getArmies().handle(r.army)
                            if (Turn.isMy()) {
                                Message.remove()
                                CommonMe.setGold(r.gold)
                                CommonMe.handleHeroButtons()
                            }
                            Execute.setExecuting(0)
                        })
                    }
                    break

                case 'raze':
                    $('#razeCastle').addClass('buttonOff');
                    Players.get(r.color).getCastles().raze(r.castleId)
                    if (Turn.isMy()) {
                        Sound.play('gold1');
                        Message.remove()
                        CommonMe.setGold(r.gold)
                    } else {
                        Sound.play('raze');
                    }
                    Execute.setExecuting(0)
                    break;

                case 'defense':
                    Players.get(r.color).getCastles().get(r.castleId).setDefense(r.defense)
                    if (Turn.isMy()) {
                        Message.remove()
                        CommonMe.setGold(r.gold)
                    }
                    Execute.setExecuting(0)
                    break;

                case 'surrender':
                    CommonMe.deselectArmy()
                    var armies = Players.get(r.color).getArmies(),
                        castles = Players.get(r.color).getCastles()
                    for (var armyId in armies.toArray()) {
                        armies.destroy(armyId)
                    }
                    for (var castleId in castles.toArray()) {
                        castles.raze(castleId)
                    }
                    if (Turn.getColor() == r.color) {
                        WebSocketSend.nextTurn()
                    }
                    Execute.setExecuting(0)
                    break;

                case 'end':
                    if (Game.getLoading()) {
                        Gui.end()
                    } else {
                        CommonMe.turnOff()
                        var id = Message.show(translations.gameOver, $('<div>').append($('<div>').html(translations.thisIsTheEnd)))
                        Message.ok(id, Gui.end)
                    }
                    break;

                case 'dead':
                    if (!GamePlayers.hasSkull(r.color)) {
                        GamePlayers.drawSkull(r.color)
                    }
                    Execute.setExecuting(0)
                    break;
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
    this.getI = function () {
        return i
    }
}
