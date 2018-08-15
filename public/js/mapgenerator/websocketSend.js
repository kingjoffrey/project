"use strict"
var WebSocketSendMapgenerator = new function () {
    var closed = true,
        ws

    this.setClosed = function (param) {
        closed = param
    }
    this.isClosed = function () {
        return closed
    }
    this.open = function () {
        var token = {
            type: 'open',
            playerId: id,
            langId: langId,
            accessKey: accessKey
        }

        ws.send(JSON.stringify(token))
    }
    this.publish = function (mapId) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'publish',
            mapId: mapId
        }

        ws.send(JSON.stringify(token))
    }
    this.mirror = function (mapId, mirror) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'mirror',
            mapId: mapId,
            mirror: mirror
        }

        ws.send(JSON.stringify(token))
    }
    this.create = function (name, maxPlayers) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'create',
            name: name,
            maxPlayers: maxPlayers
        }

        ws.send(JSON.stringify(token))
    }
    this.init = function (param) {
        ws = param
    }
}
