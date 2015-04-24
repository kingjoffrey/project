$().ready(function () {
    if (typeof gameId === 'undefined') {
        New.init()
    }
})

var New = new function () {
    var table,
        games = {},
        ws,
        closed = true,
        changeMap = function () {
            $('#map').attr('src', '/img/maps/' + $('#mapId').children(':selected').attr('value') + '.png');
        },
        getNumberOfPlayersForm = function () {
            var mapId = $('#mapId').val()
            $.getJSON('/' + lang + '/newajax/nop/mapId/' + mapId, function (result) {
                var html = $.parseHTML(result);
                console.log($($(html)[0][0]).val())
                $('#x').val($($(html)[0][0]).val())
                $('#numberOfPlayers').val($($(html)[0][1]).val())
            })
        },
        addGames = function (games) {
            table.append(th)
            var j = 0;
            for (var i in games) {
                addGame(games[i])
                j++
            }
            if (j == 0) {
                table.append(
                    $('<tr>').append($('<td colspan="3">').html(info).css('padding', '15px')).attr('id', 0)
                )
            }

        },
        addGame = function (game, players) {
            if ($('tr#' + game.gameId).length) {
                return
            }
            games[game.gameId] = players
            var numberOfPlayersInGame = countProperties(players)
            $('tr#0').remove()
            table.append(
                $('<tr>')
                    .addClass('trlink')
                    .attr('id', game.gameId)
                    .append($('<td>').append($('<a>').html(game.name)))
                    .append($('<td>').append($('<a>').append($('<span>').html(numberOfPlayersInGame)).append('/' + game.numberOfPlayers)))
                    .append($('<td>').append($('<a>').html(game.begin.split('.')[0])))
                    .click(function () {
                        top.location.replace('/' + lang + '/setup/index/gameId/' + $(this).attr('id'))
                    })
            )
        },
        removeGame = function (gameId) {
            $('tr#' + gameId).remove()
        }

    this.init = function () {
        table = $('#join.table table')
        changeMap()
        $('#mapId').change(function () {
            changeMap()
            getNumberOfPlayersForm()
        })
        New.webSocketInit()
    }
    this.webSocketInit = function () {
        ws = new WebSocket(wsURL + '/new')

        ws.onopen = function () {
            closed = false
            New.open()
        }
        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data);
            if (typeof gameId === 'undefined') {
                New.messageNew(r)
            }
        }
        ws.onclose = function () {
            closed = true
            setTimeout('New.init()', 1000);
        }
    }
    this.messageNew = function (r) {
        console.log(r)
        switch (r.type) {
            case 'games':
                //add all games
                addGames(r.games)
                break
            case 'add':
                //add new game
                addGame(r.game)
                break
            case 'remove':
                //remove game
                removeGame(r.gameId)
                break;
            case 'open':
                //add player
                break
            case 'close':
                //remove player
                break
            case 'chat':
                //incoming chat
                break
            default:
                console.log(r)
        }
    }
    this.open = function () {
        if (New.closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'open',
            playerId: id,
            langId: langId,
            accessKey: accessKey
        }

        if (typeof gameId !== 'undefined') {
            if (Setup.getGameMasterId() == id) {
                token.gameMasterId = id
                token.gameId = gameId
            }
        }
        console.log(token)
        ws.send(JSON.stringify(token))
    }
    this.removeGame = function (gameId) {
        if (New.closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'remove',
            gameId: gameId
        }

        ws.send(JSON.stringify(token))
    }
}
