$(document).ready(function () {
    Setup.init()
})

var Setup = {
    closed: true,
    ws: false,
    playersOutElement: null,
    gameMasterId: null,
    team: function (mapPlayerId) {
        var token = {
            type: 'team',
            mapPlayerId: mapPlayerId,
            teamId: $('tr#' + mapPlayerId + ' select').val()
        }

        this.ws.send(JSON.stringify(token));
    },
    open: function () {
        if (Setup.closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'open',
            gameId: gameId,
            playerId: myId,
            langId: langId,
            accessKey: accessKey
        }

        this.ws.send(JSON.stringify(token));
    },
    init: function () {
        prepareTeams()
        this.ws = new WebSocket(wsURL + '/public')
        this.playersOutElement = $('#playersout')

        this.ws.onopen = function () {
            Setup.closed = false
            Setup.open()
        };

        this.ws.onmessage = function (e) {
            var r = $.parseJSON(e.data);

            if (typeof r.type == 'undefined') {
                return;
            }

            switch (r.type) {
                case 'team':
                    $('tr#' + r.mapPlayerId + ' select').val(r.teamId)
                    break

                case 'start':
                    top.location.replace('/' + lang + '/game/index/id/' + gameId)
                    break;

                case 'update':
                    if (typeof r.gameMasterId == 'undefined') {
                        console.log('aaa')
                        return;
                    }

                    Setup.gameMasterId = r.gameMasterId
                    Setup.playersOutElement.html('')
                    prepareButtons(r.gameMasterId)

                    var playersReady = 0,
                        i = null,
                        j = null

                    for (i in r.players) { // undecided
                        if (r.players[i].computer || r.players[i].mapPlayerId) {
                            continue
                        }

                        var firstName = r.players[i].firstName,
                            lastName = r.players[i].lastName

                        Setup.playersOutElement.append('<tr><td>' + firstName + ' ' + lastName + '</td></tr>');
                    }

                    for (j in mapPlayers) {
                        var mapPlayerId = mapPlayers[j].mapPlayerId

                        $('#' + mapPlayerId + ' .td3').html(translations.computer)

                        for (i in r.players) {
                            if (r.players[i].mapPlayerId != mapPlayerId) {
                                continue
                            }

                            var firstName = r.players[i].firstName,
                                lastName = r.players[i].lastName,
                                computer = r.players[i].computer,
                                playerId = r.players[i].playerId

                            if (r.players[i].mapPlayerId) {
                                playersReady++;
                                if (!computer) {
                                    $('#' + mapPlayerId + ' .td1 div.longName').html(firstName + ' ' + lastName);
                                    $('#' + mapPlayerId + ' .td3').html(translations.human);
                                }

                                if (playerId == myId) {
                                    $('#' + mapPlayerId + ' .td2 a').html(translations.deselect);
                                } else {
                                    if (r.gameMasterId == myId) {
                                        $('#' + mapPlayerId + ' .td2 a').html(translations.select);
                                    } else {
                                        if (computer) {
                                            $('#' + mapPlayerId + ' .td2 a').html(translations.select);
                                        } else {
                                            $('#' + mapPlayerId + ' .td2 a').remove();
                                        }
                                    }
                                }
                            }
                            delete r.players[i]
                        }
                    }

                    prepareStartButton(r.gameMasterId, playersReady);
                    break;

                default:
                    console.log(r)
            }
        };

        this.ws.onclose = function () {
            Setup.closed = true;
            setTimeout('Setup.init()', 1000);
        };

    }
}

function wsChange(mapPlayerId) {
    var token = {
        type: 'change',
        mapPlayerId: mapPlayerId
    };

    Setup.ws.send(JSON.stringify(token));
}

function prepareButtons(gameMasterId) {
    for (i = 0; i < numberOfPlayers; i++) {
        var mapPlayerId = mapPlayers[i].mapPlayerId

        $('#' + mapPlayerId + ' .td1 div.longName').html('');

        $('#' + mapPlayerId + ' .td2')
            .html(
                $('<a>')
                    .addClass('button')
                    .html(translations.select)
                    .attr('id', mapPlayerId)
                    .click(function () {
                        wsChange(this.id)
                    })
            )
    }
}

function prepareTeams() {
    var click = function (i) {
        return function () {
            Setup.team(i)
        }
    }

    for (i = 0; i < numberOfPlayers; i++) {
        var mapPlayerId = mapPlayers[i].mapPlayerId

        $('#' + mapPlayerId + ' .td4').html($(form).children('dl').children('dd').children('select'))
        $('#' + mapPlayerId + ' .td4 select')
            .val(mapPlayerId)
            .attr('id', mapPlayerId)
            .change(click(mapPlayerId))
    }
}

function prepareStartButton(gameMasterId, playersReady) {
    if (gameMasterId == myId) {
        $('#start')
            .html(translations.startGame)
            .addClass('button')
            .click(function () {
                wsStart()
            })
    } else {
        $('#start').css('display', 'none')
    }
}

function wsStart() {
    if (Setup.gameMasterId != myId) {
        return
    }

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

    Setup.ws.send(JSON.stringify(token));
}