$(document).ready(function () {
    Setup.init()
})

var Setup = new function () {
    var closed = true,
        ws = false,
        playersOutElement = null,
        gameMasterId = null

    this.team = function (mapPlayerId) {
        var token = {
            type: 'team',
            mapPlayerId: mapPlayerId,
            teamId: $('tr#' + mapPlayerId + ' select').val()
        }

        ws.send(JSON.stringify(token));
    }
    this.open = function () {
        if (Setup.closed) {
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
    }
    this.init = function () {
        Setup.initButtons()
        Setup.prepareTeams()
        ws = new WebSocket(wsURL + '/setup')
        playersOutElement = $('#playersout')

        ws.onopen = function () {
            closed = false
            Setup.open()
        }
        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data);
            if (notSet(r.type)) {
                return
            }
            Setup.message(r)
        }
        ws.onclose = function () {
            closed = true;
            setTimeout('Setup.init()', 1000);
        }
    }
    this.message = function (r) {
        console.log(r)
        switch (r.type) {
            case 'team':
                $('tr#' + r.mapPlayerId + ' select').val(r.teamId)
                $('tr#' + r.mapPlayerId + ' .td4 img').attr('src', '/img/game/heroes/' + mapPlayers[r.teamId].shortName + '.png')
                break

            case 'start':
                top.location.replace('/' + lang + '/game/index/id/' + gameId)
                break;

            case 'update':
                if (notSet(r.gameMasterId)) {
                    return;
                }

                gameMasterId = r.gameMasterId
                playersOutElement.html('')

// undecided
                if (!(r.player.computer || r.player.mapPlayerId)) {
                    playersOutElement.append($('<tr>')
                        .html($('<td>').html(r.players[i].firstName + ' ' + r.players[i].lastName)))
                        .attr('id', r.player.playerId)
                }

                if (r.player.mapPlayerId) {
                    $('#' + r.player.mapPlayerId + ' .td3 div.longName').html(r.player.firstName + ' ' + r.player.lastName)
                    if (r.player.playerId == id) {
                        $('#' + r.player.mapPlayerId + ' .td2 a').html(translations.deselect)
                        $('#' + r.player.mapPlayerId).addClass('selected')
                    } else {
                        if (r.gameMasterId == id) {
                            $('#' + r.player.mapPlayerId + ' .td2 a').html(translations.select);
                        } else {
                            $('#' + mapPlayerId + ' .td2 a').remove();
                        }
                    }
                }

                Setup.prepareStartButton()
                break;

            default:
                console.log(r)
        }
    }
    this.wsChange = function (mapPlayerId) {
        var token = {
            type: 'change',
            mapPlayerId: mapPlayerId
        };

        ws.send(JSON.stringify(token));
    }

    this.initButtons = function () {
        for (var mapPlayerId in mapPlayers) {
            $('#' + mapPlayerId + ' .td1 div.longName').html('');
            $('#' + mapPlayerId + ' .td2').html($('<a>')
                .addClass('button')
                .html(translations.select)
                .attr('id', mapPlayerId)
                .click(function () {
                    Setup.wsChange(this.id)
                }))
        }
    }

    this.prepareTeams = function () {
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

    this.prepareStartButton = function () {
        if (gameMasterId == id) {
            $('#start')
                .html(translations.startGame)
                .addClass('button')
                .unbind()
                .click(function () {
                    Setup.wsStart()
                })
        } else {
            $('#start').css('display', 'none')
        }
    }

    this.wsStart = function () {
        if (Setup.gameMasterId != id) {
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

        ws.send(JSON.stringify(token));
    }
}
