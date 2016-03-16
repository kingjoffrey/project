"use strict"
var New = new function () {
    var table,
        empty,
        setPixel = function (ctx, x, y, color) {
            ctx.fillStyle = color;
            ctx.fillRect(x, y, 1, 1);
        },
        maxWidth = 500

    this.changeMap = function (fields) {
        var x, y,
            tmpCanvas = document.createElement('canvas'),
            ctx = tmpCanvas.getContext('2d'),
            canvas = document.createElement('canvas'),
            context = canvas.getContext('2d')

        for (y in fields) {
            for (x in fields[y]) {
                switch (fields[y][x]) {
                    case 'g':
                        setPixel(ctx, x, y, '#009900')
                        break
                    case 'f':
                        setPixel(ctx, x, y, '#004e00')
                        break
                    case 'w':
                        setPixel(ctx, x, y, '#0000cd')
                        break
                    case 'h':
                        setPixel(ctx, x, y, '#505200')
                        break
                    case 'm':
                        setPixel(ctx, x, y, '#262728')
                        break
                    case 'r':
                        setPixel(ctx, x, y, '#c1c1c1')
                        break
                    case 'b':
                        setPixel(ctx, x, y, '#c1c1c1')
                        break
                    case 's':
                        setPixel(ctx, x, y, '#39723E')
                        break
                }
            }
        }
        x++
        y++
        if (x > y) {
            var ratio = maxWidth / x
        } else {
            var ratio = maxWidth / y
        }
        var width = x * ratio,
            height = y * ratio

        canvas.width = width
        canvas.height = height

        context.drawImage(tmpCanvas, 0, 0, x, y, 0, 0, width, height)

        $('#img').html(canvas)
    }
    this.removeGame = function (gameId) {
        $('tr#' + gameId).remove()
        if (!$('.trlink').length) {
            table.append(empty)
        }
    }
    this.addGames = function (games) {
        for (var i in games) {
            this.addGame(games[i])
        }
        if (!$('.trlink').length && !$('tr#0').length) {
            table.append(empty)
        }
    }
    this.addGame = function (game) {
        if ($('tr#' + game.id).length) {
            return
        }
        var numberOfPlayersInGame = countProperties(game.players)
        $('tr#0').remove()
        table.append(
            $('<tr>')
                .addClass('trlink')
                .attr('id', game.id)
                .append($('<td>').html(game.name))
                .append($('<td>').html(game.gameMasterName))
                .append($('<td>').append($('<span>').html(numberOfPlayersInGame)).append('/' + game.numberOfPlayers))
                .append($('<td>').html(game.begin.split('.')[0]))
                .click(function () {
                    top.location.replace('/' + lang + '/setup/index/gameId/' + $(this).attr('id'))
                })
        )
    }
    this.init = function () {
        PrivateChat.setType('new')
        table = $('#join.table table')
        empty = $('<tr id="0">').append($('<td colspan="4">').html(info).css('padding', '15px'))

        $('#mapId').change(function () {
            WebSocketSend.map($('#mapId').val())
        })

        WebSocketNew.init()
        PrivateChat.prepare()
    }
}
