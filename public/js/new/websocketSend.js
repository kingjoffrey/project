"use strict"
var WebSocketSendNew = new function () {
    var closed = true,
        ws

    this.setClosed = function (param) {
        closed = param
    }
    this.open = function () {
        var token = {
            'type': 'open',
            'playerId': id,
            'name': playerName,
            'langId': langId,
            'accessKey': accessKey
        }

        ws.send(JSON.stringify(token))
    }
    this.setup = function () {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            'type': 'setup',
            'gameId': New.getGameId()
        }

        ws.send(JSON.stringify(token));
    }
    this.team = function (mapPlayerId) {
        var token = {
            'type': 'team',
            'mapPlayerId': mapPlayerId,
            'teamId': $('tr#' + mapPlayerId + ' select').val()
        }

        ws.send(JSON.stringify(token));
    }
    this.change = function (id) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            'type': 'change',
            'mapPlayerId': id
        }

        ws.send(JSON.stringify(token))
    }
    this.start = function (team) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            'type': 'start',
            'team': team
        }

        ws.send(JSON.stringify(token))
    }
    this.removeGame = function (gameId) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            'type': 'remove',
            'gameId': gameId
        }

        ws.send(JSON.stringify(token))
    }
    this.init = function (param) {
        ws = param
    }
}
