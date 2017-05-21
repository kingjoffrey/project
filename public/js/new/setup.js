"use strict"
var Setup = new function () {
    var numberOfSelectedPlayers = 0,
        gameId,
        gameMasterId,
        numberOfMapPlayers

    this.update = function (r) {
        if (notSet(r.close)) {
            Setup.removePlayer(r.player.playerId)
            if (r.player.sideId) {
                $('tr#' + r.player.sideId + ' .td3').html(r.player.name)

                if (r.player.playerId == id) {
                    $('tr#' + r.player.sideId + ' .td2 a').html(translations.deselect)
                    $('tr#' + r.player.sideId + ' .td2').parent().addClass('selected')
                } else {
                    if (Setup.getGameMasterId() == id) {
                        $('tr#' + r.player.sideId + ' .td2 a').html(translations.deselect);
                    } else {
                        $('tr#' + r.player.sideId + ' .td2 a').addClass('buttonOff');
                    }
                }
                $('tr#' + r.player.sideId + ' .td1').attr('id', r.player.playerId)
            } else {
                $('#playersout').append(
                    $('<tr>')
                        .html($('<td>').html(r.player.name))
                        .attr('id', r.player.playerId)
                )
            }
            Setup.updateStartButton()
        } else {
            WebSocketSendMain.controller('new', 'index')
        }

    }
    this.updateStartButton = function () {
        $('.td3').each(function () {
            if ($(this).html()) {
                numberOfSelectedPlayers++
            }
        })
        if (gameMasterId == id) {
            if (numberOfSelectedPlayers >= numberOfMapPlayers) {
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
        var tr = $('tr #' + playerId + '.td1').parent()
        if (tr.length) {
            tr.removeClass('selected')
            $('tr#' + tr.attr('id') + ' .td2 a').html(translations.select).removeClass('buttonOff');
            tr.find('.td3').html('')
            $('tr #' + playerId + '.td1').attr('id', '')
        }
        $('#playersout').find('#' + playerId).remove()
    }
    this.getGameMasterId = function () {
        return gameMasterId
    }
    this.getGameId = function () {
        return gameId
    }
    this.init = function (gId, gMasterId, nOfMapPlayers) {
        gameId = gId
        gameMasterId = gMasterId
        numberOfMapPlayers = nOfMapPlayers
    }
}
