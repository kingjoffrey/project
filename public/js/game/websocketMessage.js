"use strict"
var WebSocketMessageGame = new function () {
    this.switch = function (r) {
        setTimeout(function () {
            console.log(r)
            switch (r.type) {
                case 'move':
                    Execute.addQueue(r)
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
                    Execute.addQueue(r)
                    break

                case 'nextTurn':
                    Execute.addQueue(r)
                    break

                case 'neutral':
                    Execute.addQueue(r)
                    break

                case 'startTurn':
                    Execute.addQueue(r)
                    break

                case 'ruin':
                    Execute.addQueue(r)
                    break

                case 'split':
                    Execute.addQueue(r)
                    break

                case 'join':
                    Execute.addQueue(r)
                    break

                case 'disband':
                    Execute.addQueue(r)
                    break

                case 'resurrection':
                    Execute.addQueue(r)
                    break

                case 'raze':
                    Execute.addQueue(r)
                    break

                case 'defense':
                    Execute.addQueue(r)
                    break

                case 'surrender':
                    Execute.addQueue(r)
                    break;

                case 'end':
                    Execute.addQueue(r)
                    break

                case 'dead':
                    Execute.addQueue(r)
                    break

                case 'error':
                    Message.error(r.msg);
                    GameGui.unlock();
                    break

                case 'open':
                    GameInit.init(r)
                    break

                case 'production':
                    var castle = Me.getCastle(r.castleId)
                    castle.setProductionId(r.unitId)
                    castle.setProductionTurn(0)

                    CastleWindow.show(castle)
                    break

                case 'statistics':
                    StatisticsWindow.show(r)
                    break

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
                    console.log(r)
            }
        }, 500)
    }
}
