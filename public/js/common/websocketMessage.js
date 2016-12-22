"use strict"
var WebSocketMessageCommon = new function () {
    this.switch = function (r) {
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
                if (CommonMe.colorEquals(towerColor)) {
                    CommonMe.incomeIncrement(-5)
                }
                if (CommonMe.colorEquals(r.color)) {
                    CommonMe.incomeIncrement(5)
                }
                break

            case 'update':
                CommonMe.setSelectedCastleId(0)
                CommonMe.resetSkippedArmies()

                var castles = CommonMe.getCastles()
                for (var castleId in r.productionTurns) {
                    castles.get(castleId).setProductionTurn(r.productionTurns[castleId])
                }
                Sound.play('startturn')

                CommonMe.setUpkeep(r.upkeep)
                CommonMe.setGold(r.gold)
                CommonMe.setIncome(r.income)
                Gui.unlock()
                break

            case 'nextTurn':
                Execute.addQueue(r)
                break;

            case 'neutral':
                Execute.addQueue(r)
                break;

            case 'startTurn':
                Execute.addQueue(r)
                break;

            case 'ruin':
                Execute.addQueue(r)
                break;

            case 'split':
                Execute.addQueue(r)
                break;

            case 'join':
                Execute.addQueue(r)
                break;

            case 'disband':
                Execute.addQueue(r)
                break;

            case 'resurrection':
                Execute.addQueue(r)
                break;

            case 'raze':
                Execute.addQueue(r)
                break;

            case 'defense':
                Execute.addQueue(r)
                break;

            case 'surrender':
                Execute.addQueue(r)
                break;

            case 'end':
                Execute.addQueue(r)
                break;

            case 'dead':
                Execute.addQueue(r)
                break;

            case 'error':
                Message.error(r.msg);
                Gui.unlock();
                break;

            case 'open':
                CommonInit.init(r)
                break;

            case 'close':
                GamePlayers.setOnline(r.color, 0)
                break;

            case 'online':
                //if (!CommonMe.colorEquals(r.color)) {
                    GamePlayers.setOnline(r.color, 1)
                //}
                break;

            case 'chat':
                Chat.message(r.color, r.msg, makeTime())
                break;

            case 'production':
                var castle = CommonMe.getCastle(r.castleId)
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
                    CommonMe.setAttackBattleSequence(r.sequence)
                } else {
                    Message.simple(translations.battleSequence, translations.defenceSequenceSuccessfullyUpdated)
                    CommonMe.setDefenseBattleSequence(r.sequence)
                }
                break

            default:
                console.log(r);
        }
    }
}
