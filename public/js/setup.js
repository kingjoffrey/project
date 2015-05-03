$().ready(function () {
    Setup.init()
})

var Setup = new function () {
    var closed = true,
        ws,
        playersOutElement,
        gameMasterId,
        numberOfSelectedPlayers = 0,
        updateStartButton = function () {
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
        },
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
        removePlayer = function (playerId) {
            var tr = $('#' + playerId + '.td1').parent()
            if (tr.length) {
                tr.removeClass('selected')
                initButton(tr.attr('id'))
                tr.find('.td3').html('')
                $('#' + playerId + '.td1').attr('id', '')
            }
            playersOutElement.find('#' + playerId).remove()
        },
        onMessage = function (r) {
            console.log(r)
            switch (r.type) {
                case 'chat':
                    PrivateChat.message(2, r.name, r.id, r.msg)
                    break

                case 'open':
                    gameMasterId = r.gameMasterId
                    New.webSocketInit()
                    break

                case 'team':
                    $('tr#' + r.mapPlayerId + ' select').val(r.teamId)
                    $('tr#' + r.mapPlayerId + ' .td4 img').attr('src', '/img/game/heroes/' + mapPlayers[r.teamId].shortName + '.png')
                    break

                case 'start':
                    New.removeGame(gameId)
                    top.location.replace('/' + lang + '/game/index/id/' + gameId)
                    break;

                case 'update':
                    gameMasterId = r.gameMasterId
                    removePlayer(r.player.playerId)

                    if (notSet(r.close)) {
                        if (r.player.mapPlayerId) {
                            $('#' + r.player.mapPlayerId + ' .td3').html(r.player.firstName + ' ' + r.player.lastName)

                            if (r.player.playerId == id) {
                                $('#' + r.player.mapPlayerId + ' .td2 a').html(translations.deselect)
                                $('#' + r.player.mapPlayerId).addClass('selected')
                            } else {
                                if (r.gameMasterId == id) {
                                    $('#' + r.player.mapPlayerId + ' .td2 a').html(translations.deselect);
                                } else {
                                    $('#' + r.player.mapPlayerId + ' .td2 a').remove();
                                }
                            }
                            $('#' + r.player.mapPlayerId + ' .td1').attr('id', r.player.playerId)
                        } else {
                            playersOutElement.append(
                                $('<tr>')
                                    .html($('<td>').html(r.player.firstName + ' ' + r.player.lastName))
                                    .attr('id', r.player.playerId)
                            )
                        }
                    }
                    updateStartButton()
                    break;

                default:
                    console.log(r)
            }
        },
        open = function () {
            if (closed) {
                console.log(translations.sorryServerIsDisconnected)
                return;
            }

            var token = {
                type: 'open',
                gameId: gameId,
                playerId: id,
                name: playerName,
                langId: langId,
                accessKey: accessKey
            }

            ws.send(JSON.stringify(token));
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

    this.team = function (mapPlayerId) {
        var token = {
            type: 'team',
            mapPlayerId: mapPlayerId,
            teamId: $('tr#' + mapPlayerId + ' select').val()
        }

        ws.send(JSON.stringify(token));
    }
    this.init = function () {
        type = 'setup'
        PrivateChat.enable()
        initButtons()
        initTeams()
        ws = new WebSocket(wsURL + '/setup')
        playersOutElement = $('#playersout')

        ws.onopen = function () {
            closed = false
            open()
        }
        ws.onmessage = function (e) {
            onMessage($.parseJSON(e.data))
        }
        ws.onclose = function () {
            closed = true;
            setTimeout('Setup.init()', 1000);
        }
    }
    this.getGameMasterId = function () {
        return gameMasterId
    }
    this.chat = function (msg) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'chat',
            msg: msg
        }

        ws.send(JSON.stringify(token))
    }
}
