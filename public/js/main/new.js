"use strict"
var NewController = new function () {
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
        $('form').submit(function (e) {
            e.preventDefault()
            WebSocketSendMain.controller('new', 'index', {
                'mapId': $('select#mapId').val(),
                'timeLimit': $('select#timeLimit').val(),
                'turnsLimit': $('input#turnsLimit').val(),
                'turnTimeLimit': $('select#turnTimeLimit').val()
            })
        })

        New.init()
    }
    this.setup = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })

        New.setup(r.mapPlayers, r.form, r.gameId, r.gameMasterId)
    }
    this.map = function (r) {
        var tmpCanvas = document.createElement('canvas'),
            ctx = tmpCanvas.getContext('2d'),
            canvas = document.createElement('canvas'),
            context = canvas.getContext('2d'),
            maxWidth = 500,
            setPixel = function (ctx, x, y, color) {
                ctx.fillStyle = color;
                ctx.fillRect(x, y, 1, 1);
            }

        $('#numberOfPlayers').val(r.number)

        for (var y in r.fields) {
            for (var x in r.fields[y]) {
                switch (r.fields[y][x]) {
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
}