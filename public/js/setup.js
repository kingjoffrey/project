$().ready(function () {
    Setup.init()
})

var Setup = new function () {
    var closed = true,
        ws,
        playersOutElement,
        gameMasterId,
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

                case 'close':
                    Setup.removePlayer(r.playerId)
                    break;

                case 'update':
                    gameMasterId = r.gameMasterId
                    Setup.removePlayer(r.player.playerId)

                    if (r.player.mapPlayerId) {
                        $('#' + r.player.mapPlayerId + ' .td3').html(r.player.firstName + ' ' + r.player.lastName)

                        if (r.player.playerId == id) {
                            $('#' + r.player.mapPlayerId + ' .td2 a').html(translations.deselect)
                            $('#' + r.player.mapPlayerId).addClass('selected')
                        } else {
                            if (r.gameMasterId == id) {
                                $('#' + r.player.mapPlayerId + ' .td2 a').html(translations.select);
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

                    Setup.updateStartButton()
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
                langId: langId,
                accessKey: accessKey
            }

            ws.send(JSON.stringify(token));
        },
        initButtons = function () {
            for (var mapPlayerId in mapPlayers) {
                $('#' + mapPlayerId + ' .td1 div.longName').html('');
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
    this.removePlayer = function (playerId) {
        playersOutElement.find('#' + playerId).remove()
        $('#' + playerId + '.td1').parent().removeClass('selected')
        $('#' + playerId + '.td1').parent().find('.td3').html('')
        if (playerId == id) {
            $('#' + playerId + '.td1').parent().find('.td2 a').html(translations.select)
        } else {
            if (gameMasterId == id) {
                $('#' + playerId + '.td1').parent().find('.td2 a').html(translations.deselect)
            } else {
                $('#' + playerId + '.td2').parent().find('.td2 a').remove()
            }
        }
        $('#' + playerId + '.td1').attr('id', '')
    }
    this.updateStartButton = function () {
        if (gameMasterId == id) {
            $('#start')
                .html(translations.startGame)
                .addClass('button')
                .unbind()
                .click(function () {
                    if (Setup.getGameMasterId() != id) {
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
            $('#start').css('display', 'none')
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
            msg: msg,
            name: playerName
        }

        ws.send(JSON.stringify(token))
    }
}
