var Setup = new function () {
    var playersOutElement,
        gameMasterId,
        numberOfSelectedPlayers = 0,
        initButton = function (mapPlayerId) {
            $('#' + mapPlayerId + ' .td2').html($('<a>')
                .addClass('button')
                .html(translations.select)
                .attr('id', mapPlayerId)
                .click(function () {
                    var token = {
                        type: 'change',
                        mapPlayerId: this.id
                    }
                    ws.send(JSON.stringify(token))
                }))
        },
        initButtons = function () {
            for (var mapPlayerId in mapPlayers) {
                initButton(mapPlayerId)
            }
        },
        initTeams = function () {
            var click = function (i) {
                return function () {
                    Setup.team(i)
                }
            }

            for (var mapPlayerId in mapPlayers) {
                $('#' + mapPlayerId + ' .td4')
                    .html($(form).children('dl').children('dd').children('select'))
                    .append($('<img>').attr('src', '/img/game/heroes/' + mapPlayers[mapPlayerId].shortName + '.png'))
                $('#' + mapPlayerId + ' .td4 select')
                    .val(mapPlayerId)
                    .attr('id', mapPlayerId)
                    .change(click(mapPlayerId))
            }
        }

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

                        var token = {
                            type: 'start',
                            team: team
                        }

                        ws.send(JSON.stringify(token))
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
            initButton(tr.attr('id'))
            tr.find('.td3').html('')
            $('#' + playerId + '.td1').attr('id', '')
        }
        playersOutElement.find('#' + playerId).remove()
    }
    this.getPlayersOutElement = function () {
        return playersOutElement
    }
    this.getGameMasterId = function () {
        return gameMasterId
    }
    this.init = function () {
        PrivateChat.setType('setup')
        PrivateChat.enable()
        initButtons()
        initTeams()
        WebSocketSetup.init()
    }
}
