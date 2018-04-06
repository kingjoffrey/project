"use strict"
var WebSocketSendGame = new function () {
    var closed = true,
        ws

    this.setClosed = function (param) {
        closed = param
    }
    this.isClosed = function () {
        return closed
    }
    this.ruin = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        if (!Turn.isMy()) {
            return
        }

        if (!Me.getSelectedArmyId()) {
            return
        }

        var army = Me.getSelectedArmy()

        if (Ruins.get(Fields.get(army.getX(), army.getY()).getRuinId()).isEmpty()) {
            Message.error(translations.Theruinsarealreadysearched)
            return
        }

        var heroId = 0,
            movesLeftOk = 0

        for (heroId in army.getHeroes()) {
            if (army.getHeroes()[heroId].movesLeft > 0) {
                movesLeftOk = 1
                break
            }
        }

        if (!heroId) {
            Message.error(translations.OnlyHeroescansearchtheruins)
            return
        }

        if (!movesLeftOk) {
            Message.error(translations.Heroshasnotenoughmoves)
            return
        }

        Me.deselectArmy()

        var token = {
            'type': 'ruin',
            'armyId': Me.getDeselectedArmyId()
        }

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
        if (GameGui.lock) {
            return
        }

        var armyId

        if (!(armyId = Me.getSelectedArmyId())) {
            return
        }

        Me.setFortified(armyId, true)
        Me.deselectArmy()
        if (!isTouchDevice()) {
            Me.findNext()
        }

        var token = {
            'type': 'fortify',
            'armyId': armyId,
            'fortify': 1
        };

        ws.send(JSON.stringify(token));
    }

    this.unfortify = function (armyId) {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        if (!Turn.isMy()) {
            return
        }
        if (GameGui.lock) {
            return
        }

        Me.setFortified(armyId, false)

        var token = {
            'type': 'fortify',
            'armyId': armyId,
            'fortify': 0
        };

        ws.send(JSON.stringify(token));
    }

    this.join = function (armyId) {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        if (!Turn.isMy()) {
            return
        }

        Me.setParentArmyId(null)
        var token = {
            'type': 'join',
            'armyId': armyId
        }

        ws.send(JSON.stringify(token))
    }

    this.disband = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        if (!Turn.isMy()) {
            return
        }
        if (!Me.getSelectedArmyId()) {
            return
        }
        Me.deselectArmy(1)

        var token = {
            'type': 'disband',
            'armyId': Me.getDeselectedArmyId()
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

        GameGui.setLock()
        GameModels.movePathCircles()
        Me.deselectArmy(1)

        var token = {
            'type': 'move',
            'x': AStar.getX(),
            'y': AStar.getY(),
            'armyId': armyId
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
        var h = ''
        var s = ''

        $('.message input[type="checkbox"]:checked').each(function () {
            if ($(this).attr('name') == 'heroId') {
                if (h) {
                    h += ','
                }
                h += $(this).val()
            } else {
                if (s) {
                    s += ','
                }
                s += $(this).val()
            }
        })

        if (!s && !h) {
            return
        }

        var token = {
            'type': 'split',
            'armyId': Me.getSelectedArmyId(),
            's': s,
            'h': h
        }

        ws.send(JSON.stringify(token));
    }

    this.resurrection = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        // if (!Turn.isMy()) {
        //     return
        // }

        //if (Me.findHero()) {
        //    return;
        //}

        Me.deselectArmy()

        var token = {
            'type': 'resurrection'
        }

        ws.send(JSON.stringify(token));
    }

    this.hire = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        if (!Turn.isMy()) {
            return
        }

        Me.deselectArmy()

        var token = {
            'type': 'hire'
        }

        ws.send(JSON.stringify(token));
    }

    this.raze = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        var army = Me.getArmy(Me.getSelectedArmyId())
        var castleId = Fields.get(army.getX(), army.getY()).getCastleId()

        if (!castleId) {
            Message.error(translations.noCastleToDestroy);
            return;
        }

        var token = {
            'type': 'raze',
            'armyId': Me.getSelectedArmyId()
        }

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
            'type': 'defense',
            'castleId': castleId
        }

        ws.send(JSON.stringify(token));
    }

    this.startMyTurn = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        var token = {
            'type': 'startTurn'
        }

        ws.send(JSON.stringify(token))
    }

    this.nextTurn = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        var token = {
            'type': 'nextTurn'
        }

        ws.send(JSON.stringify(token))
    }

    this.statistics = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        var token = {
            'type': 'statistics'
        }

        ws.send(JSON.stringify(token))
    }

    this.battleAttack = function () {
        WebSocketSendGame.battleConfiguration(1)
    }

    this.battleDefence = function () {
        WebSocketSendGame.battleConfiguration(0)
    }

    this.battleConfiguration = function (attack) {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        var sequence = {},
            i = 0

        $('.battleUnit img').each(function () {
            i++
            sequence[i] = $(this).attr('id')
        })

        var token = {
            'type': 'bSequence',
            'attack': attack,
            'sequence': sequence
        }

        ws.send(JSON.stringify(token))
    }

    this.computer = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            'type': 'computer'
        }

        ws.send(JSON.stringify(token))
    }

    this.production = function (castleId, unitId) {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        if (!unitId) {
            Message.error('Error');
            return
        }

        var token = {
            'type': 'production',
            'castleId': castleId,
            'unitId': unitId
        };

        ws.send(JSON.stringify(token))
    }

    this.surrender = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        var token = {
            'type': 'surrender'
        };

        ws.send(JSON.stringify(token))
    }
    this.chat = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return
        }

        var msg = $('#msg').val()

        if (msg) {
            $('#msg').val('')

            var token = {
                'type': 'chat',
                'msg': msg
            }

            ws.send(JSON.stringify(token))
        }
    }
    this.open = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            'type': 'open',
            'gameId': Game.getGameId(),
            'playerId': id,
            'langId': langId,
            'accessKey': accessKey
        }

        ws.send(JSON.stringify(token))
    }

    this.init = function (param) {
        ws = param
    }
}
