"use strict"
var WebSocketSend = new function () {
    var closed = true,
        ws

    this.setClosed = function (param) {
        closed = param
    }
    this.isClosed = function () {
        return closed
    }
    this.open = function () {
        if (New.closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'open',
            playerId: id,
            name: playerName,
            langId: langId,
            accessKey: accessKey
        }

        if (typeof gameId !== 'undefined') {
            if (Setup.getGameMasterId() == id) {
                token.gameMasterId = id
                token.gameId = gameId
            }
        }
        ws.send(JSON.stringify(token))
    }
    this.removeGame = function (gameId) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'remove',
            gameId: gameId
        }

        ws.send(JSON.stringify(token))
    }
    this.chat = function (msg) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'chat',
            msg: msg,
            name: playerName
        }

        ws.send(JSON.stringify(token))
    }
    this.nop = function (mapId) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'nop',
            mapId: mapId
        }

        ws.send(JSON.stringify(token))
    }
    this.init = function (param) {
        ws = param
    }
}
