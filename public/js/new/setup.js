"use strict"
var Setup = new function () {
    var numberOfSelectedPlayers = 0,
        gameId,
        gameMasterId,
        numberOfMapPlayers


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
