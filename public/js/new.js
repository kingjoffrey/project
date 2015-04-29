$().ready(function () {
    if (typeof gameId === 'undefined') {
        New.init()
    }
})

var New = new function () {
    var table,
        empty,
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
            for (var i in games) {
                addGame(games[i])
            }
            if (!$('.trlink').length && !$('tr#0').length) {
                table.append(empty)
            }
        },
        addGame = function (game) {
            if ($('tr#' + game.id).length) {
                return
            }
            var numberOfPlayersInGame = countProperties(game.players)
            $('tr#0').remove()
            table.append(
                $('<tr>')
                    .addClass('trlink')
                    .attr('id', game.id)
                    .append($('<td>').append($('<a>').html(game.name)))
                    .append($('<td>').append($('<a>').append($('<span>').html(numberOfPlayersInGame)).append('/' + game.numberOfPlayers)))
                    .append($('<td>').append($('<a>').html(game.begin.split('.')[0])))
                    .click(function () {
                        top.location.replace('/' + lang + '/setup/index/gameId/' + $(this).attr('id'))
                    })
            )
        }

    this.init = function () {
        type = 'new'
        $('#chatBox input').prop('disabled', false)
        table = $('#join.table table')
        empty = $('<tr id="0">').append($('<td colspan="3">').html(info).css('padding', '15px'))
        changeMap()
        $('#mapId').change(function () {
            changeMap()
            getNumberOfPlayersForm()
        })
        New.webSocketInit()
        PrivateChat.prepare()
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
            case 'addGame':
                addGame(r.game)
                break
            case 'addPlayer':
                var numberOfPlayersInGame = $('tr#' + r.gameId + ' span').html()
                $('tr#' + r.gameId + ' span').html(numberOfPlayersInGame++)
                break
            case 'removeGame':
                $('tr#' + r.gameId).remove()
                if (!$('.trlink').length) {
                    table.append(empty)
                }
                break;
            case 'removePlayer':
                var numberOfPlayersInGame = $('tr#' + r.gameId + ' span').html()
                $('tr#' + r.gameId + ' span').html(numberOfPlayersInGame--)
                break
            case 'open':
                //add player
                break
            case 'close':
                //remove player
                break
            case 'chat':
                PrivateChat.message(2, r.name, r.msg)
                $('#chatWindow').animate({scrollTop: $('#chatWindow div')[0].scrollHeight}, 1000)
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
    this.chat = function (msg) {
        if (New.closed) {
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
