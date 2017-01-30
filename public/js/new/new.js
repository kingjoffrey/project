"use strict"
var New = new function () {
    var table,
        empty,
        gameMasterId,
        gameId,
        numberOfSelectedPlayers = 0

    this.updateStartButton = function () {
        $('.td3').each(function () {
            if ($(this).html()) {
                numberOfSelectedPlayers++
            }
        })
        if (gameMasterId == id) {
            if (numberOfSelectedPlayers > 0) {
                $('#start')
                    .html(translations.startGame)
                    .removeClass('buttonOff')
                    .css('display', 'inline-block')
                    .unbind()
                    .click(function () {
                        if (gameMasterId != id) {
                            return
                        }
                        var team = {}
                        $('#playersingame tr').each(function () {
                            var id = $(this).attr('id')
                            if (isSet(id)) {
                                team[id] = $(this).find('select').val()
                            }
                        })
                        WebSocketSendNew.start(team)
                    })
            } else {
                $('#start')
                    .html(translations.startGame)
                    .addClass('buttonOff')
                    .css('display', 'inline-block')
                    .unbind()
            }
        } else {
            $('#start').css('display', 'none')
        }
        numberOfSelectedPlayers = 0
    }
    this.removePlayer = function (playerId) {
        var tr = $('#' + playerId + '.td1').parent()
        if (tr.length) {
            tr.removeClass('selected')
            $('#' + tr.attr('id') + ' .td2 a').html(translations.select)
            tr.find('.td3').html('')
            $('#' + playerId + '.td1').attr('id', '')
        }
        $('#playersout').find('#' + playerId).remove()
    }
    this.getGameMasterId = function () {
        return gameMasterId
    }
    this.getGameId = function () {
        return gameId
    }
    this.removeGame = function (gameId) {
        $('tr#' + gameId).remove()
        if (!$('.trlink').length) {
            table.append(empty)
        }
    }
    this.addGames = function (games) {
        for (var i in games) {
            this.addGame(games[i])
        }
        if (!$('.trlink').length && !$('tr#0').length) {
            table.append(empty)
        }
    }
    this.addGame = function (game) {
        if ($('tr#' + game.id).length) {
            return
        }
        var numberOfPlayersInGame = countProperties(game.players)
        $('tr#0').remove()
        table.append(
            $('<tr>')
                .addClass('trlink')
                .attr('id', game.id)
                .append($('<td>').html(game.mapName))
                .append($('<td>').html(game.gameMasterName))
                .append($('<td>').append($('<span>').html(numberOfPlayersInGame)).append('/' + game.numberOfPlayers))
                .append($('<td>').html(game.begin.split('.')[0]))
                .click(function () {
                    WebSocketSendMain.controller('new', 'setup', {'gameId': $(this).attr('id')})
                })
        )
    }
    this.setup = function (id, gmId) {
        gameId = id
        gameMasterId = gmId

        WebSocketSendNew.setup()
    }
    this.init = function () {
        table = $('#join.table table')
        empty = $('<tr id="0">').append($('<td colspan="4">').html(translations.Therearenoopengames).css('padding', '15px'))


        WebSocketNew.init()
    }
}
