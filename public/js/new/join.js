"use strict"
var Join = new function () {
    var empty

    this.removeGame = function (gameId) {
        $('tr#' + gameId).remove()
        if (!$('.trlink').length) {
            $('#join.table table').append(empty)
        }
    }
    this.addGames = function (games) {
        for (var i in games) {
            this.addGame(games[i])
        }
        if (!$('.trlink').length && !$('tr#0').length) {
            $('#join.table table').append(empty)
        }
    }
    this.addGame = function (game) {
        if ($('tr#' + game.id).length) {
            return
        }
        var numberOfPlayersInGame = countProperties(game.players)
        $('tr#0').remove()
        $('#join.table table').append(
            $('<tr>')
                .addClass('trlink')
                .attr('id', game.id)
                .append($('<td>').html(game.mapName))
                .append($('<td>').html(game.gameMasterName))
                .append($('<td>').append($('<span>').html(numberOfPlayersInGame)).append('/' + game.numberOfPlayers))
                .append($('<td>').html(game.begin.split('.')[0]))
                .click(function () {
                    WebSocketSendMain.controller('setup', 'index', {'gameId': $(this).attr('id')})
                })
        )
    }
    this.init = function () {
        empty = $('<tr id="0">').append($('<td colspan="4">').html(translations.Therearenoopengames).css('padding', '15px'))
    }
}
