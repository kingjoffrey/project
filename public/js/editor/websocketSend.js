"use strict"
var WebSocketSendEditor = new function () {
    var closed = true,
        ws

    this.setClosed = function (param) {
        closed = param
    }
    this.isClosed = function () {
        return closed
    }
    this.add = function (itemName, x, y) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        EditorGui.lock()

        var token = {
            type: 'add',
            mapId: EditorController.getMapId(),
            itemName: itemName,
            x: x,
            y: y
        }

        ws.send(JSON.stringify(token))
    }
    this.up = function (x, y) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        EditorGui.lock()

        var token = {
            type: 'up',
            mapId: EditorController.getMapId(),
            x: x,
            y: y
        }

        ws.send(JSON.stringify(token))
    }
    this.down = function (x, y) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        EditorGui.lock()

        var token = {
            type: 'down',
            mapId: EditorController.getMapId(),
            x: x,
            y: y
        }

        ws.send(JSON.stringify(token))
    }
    this.editRuin = function (mapRuinId) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        EditorGui.lock()

        var token = {
            type: 'editRuin',
            mapId: EditorController.getMapId(),
            mapRuinId: mapRuinId,
            ruinId: $('select[name=type]').val(),
        }

        ws.send(JSON.stringify(token))
    }
    this.editCastle = function (castleId) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        EditorGui.lock()

        var capital = Boolean($('input[name=capital]').is(':checked')),
            color = $('select[name=color]').val()

        if (color == 'neutral' && capital) {
            capital = false
        }

        var token = {
            type: 'editCastle',
            mapId: EditorController.getMapId(),
            castleId: castleId,
            // name: $('input[name=name]').val(),
            // income: $('input[name=income]').val(),
            enclaveNumber: $('input[name=enclaveNumber]').val(),
            color: color,
            defense: $('select[name=defence]').val(),
            capital: capital,
            production: {
                0: {'unitId': $('select[name=unitId0]').val(), 'time': $('select[name=time0]').val()},
                1: {'unitId': $('select[name=unitId1]').val(), 'time': $('select[name=time1]').val()},
                2: {'unitId': $('select[name=unitId2]').val(), 'time': $('select[name=time2]').val()},
                3: {'unitId': $('select[name=unitId3]').val(), 'time': $('select[name=time3]').val()}
            }
        }

        ws.send(JSON.stringify(token))
    }
    this.remove = function (x, y) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        EditorGui.lock()

        var token = {
            type: 'remove',
            mapId: EditorController.getMapId(),
            x: x,
            y: y
        }

        ws.send(JSON.stringify(token))
    }
    this.open = function () {
        if (closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'open',
            mapId: EditorController.getMapId(),
            playerId: id,
            langId: langId,
            accessKey: accessKey
        }

        ws.send(JSON.stringify(token))
    }
    this.init = function (param) {
        ws = param
    }
}
