var Setup = {
    team: function (mapPlayerId) {
        var token = {
            type: 'team',
            mapPlayerId: mapPlayerId,
            teamId: $('tr#' + mapPlayerId + ' select').val()
        }

        ws.send(JSON.stringify(token));
    }
}

var start = 0,
    select = null

$(document).ready(function () {
    initWebSocket()
    prepareTeams()
});

function initWebSocket() {
    ws = new WebSocket(wsURL + '/public');

    ws.onopen = function () {
        wsClosed = false;
        wsRegister();
    };

    ws.onmessage = function (e) {
        var r = $.parseJSON(e.data);

        if (typeof r.type == 'undefined') {
            return;
        }

        switch (r.type) {
            case 'team':
                $('tr#' + r.mapPlayerId + ' select').val(r.teamId)
                break

            case 'start':
                top.location.replace(urlGemesetupStart);
                break;

            case 'update':
                if (typeof r.gameMasterId == 'undefined') {
                    return;
                }

                $('#playersout').html('');

                prepareButtons(r.gameMasterId);

                var playersReady = 0;

                for (i in r) {
                    if (typeof r[i].mapPlayerId == 'undefined') {
                        continue;
                    }

                    if (r[i].mapPlayerId) {
                        playersReady++;
                        $('#' + r[i].mapPlayerId + ' .td1 div.left').html(r[i].firstName + ' ' + r[i].lastName);


                        if (r[i].playerId == playerId) {
                            $('#' + r[i].mapPlayerId + ' .td2 a').html(deselect);
                        } else {
                            if (r.gameMasterId == playerId) {
                                $('#' + r[i].mapPlayerId + ' .td2 a').html(kick);
                            } else {
                                if (r[i].computer) {
                                    $('#' + r[i].mapPlayerId + ' .td2 a').html(select);
                                } else {
                                    $('#' + r[i].mapPlayerId + ' .td2 a').remove();
                                }
                            }
                        }

                        if (r[i].computer) {
                            $('#' + r[i].mapPlayerId + ' .td3').html(computer);
                        } else {
                            $('#' + r[i].mapPlayerId + ' .td3').html(human);
                        }
                    } else {
                        if (r[i].computer) {
                            continue;
                        }
                        $('#playersout').append('<tr><td># ' + r[i].firstName + ' ' + r[i].lastName + '</td></tr>');
                    }
                }

                prepareStartButton(r.gameMasterId, playersReady);
                break;

            default:
                console.log(r)
        }
    };

    ws.onclose = function () {
        wsClosed = true;
        setTimeout('initWebSocket()', 1000);
    };

}

function wsRegister() {
    var token = {
        type: 'register',
        gameId: gameId,
        playerId: playerId,
        accessKey: accessKey
    };

    ws.send(JSON.stringify(token));
}

function wsChange(mapPlayerId) {
    var token = {
        type: 'change',
        mapPlayerId: mapPlayerId
    };

    ws.send(JSON.stringify(token));
}

function wsComputer(mapPlayerId) {
    var token = {
        type: 'computer',
        mapPlayerId: mapPlayerId
    };

    ws.send(JSON.stringify(token));
}

function prepareButtons(gameMasterId) {
    for (i = 0; i < numberOfPlayers; i++) {
        $('#' + mapPlayers[i].mapPlayerId + ' .td1 div.left').html('');

        $('#' + mapPlayers[i].mapPlayerId + ' .td2').html(
            $('<a>')
                .addClass('button')
                .html(select)
                .attr('id', mapPlayers[i].mapPlayerId)
                .click(function () {
                    wsChange(this.id)
                }));

        if (gameMasterId == playerId) {
            $('#' + mapPlayers[i].mapPlayerId + ' .td3').html(
                $('<a>')
                    .addClass('button')
                    .html('Set computer')
                    .attr('id', mapPlayers[i].mapPlayerId)
                    .click(function () {
                        wsComputer(this.id)
                    }));
        } else {
            $('#' + mapPlayers[i].mapPlayerId + ' .td3').html('');
        }
    }
}

function prepareTeams() {
    var click = function (i) {
        return function () {
            Setup.team(i)
        }
    }

    for (i = 0; i < numberOfPlayers; i++) {
        $('#' + mapPlayers[i].mapPlayerId + ' .td4').html($(form).children('dl').children('dd').children('select'))
        $('#' + mapPlayers[i].mapPlayerId + ' .td4 select')
            .val(mapPlayers[i].mapPlayerId)
            .attr('id', mapPlayers[i].mapPlayerId)
            .change(click(mapPlayers[i].mapPlayerId))
    }
}

function prepareStartButton(gameMasterId, playersReady) {
    if (gameMasterId == playerId) {
        $('#start').html($('<a>').addClass('button').html('Start game'));
        $('#start a').click(function () {
            if (start) {
                wsStart();
            }
        });
        if (numberOfPlayers <= playersReady) {
            $('#start a').removeClass('buttonOff');
            start = 1;
        } else {
            $('#start a').addClass('buttonOff');
            start = 0;
        }
    }

}

function wsStart() {
    var team = {}

    $('#playersingame tr').each(function () {
        var id = $(this).attr('id')
        if (typeof id != 'undefined') {
            team[id] = $(this).find('select').val()
        }
    })

    var token = {
        type: 'start',
        team: team
    };

    ws.send(JSON.stringify(token));
}