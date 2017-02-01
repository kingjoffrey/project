"use strict"
var CreateController = new function () {
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
        $('form').submit(function (e) {
            e.preventDefault()
            WebSocketSendMain.controller('create', 'index', {'mapId': $('#mapId').val()})
        })
        $('#mapId').change(function () {
            WebSocketSendMain.controller('create', 'map', {'mapId': $('#mapId').val()})
        })

        WebSocketSendMain.controller('create', 'map', {'mapId': $('#mapId').val()})
    }
    this.map = function (r) {
        var tmpCanvas = document.createElement('canvas'),
            ctx = tmpCanvas.getContext('2d'),
            canvas = document.createElement('canvas'),
            context = canvas.getContext('2d'),
            multiplier = 5,
            setPixel = function (ctx, x, y, color) {
                ctx.fillStyle = color;
                ctx.fillRect(x * multiplier, y * multiplier, multiplier, multiplier);
            }

        var w = r.fields[0].length * multiplier
        var h = r.fields.length * multiplier

        canvas.width = w
        canvas.height = h
        tmpCanvas.width = w
        tmpCanvas.height = h

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
                        // setPixel(ctx, x, y, '#c1c1c1')
                        setPixel(ctx, x, y, '#009900')
                        break
                    case 'b':
                        // setPixel(ctx, x, y, '#c1c1c1')
                        setPixel(ctx, x, y, '#009900')
                        break
                    case 's':
                        setPixel(ctx, x, y, '#39723E')
                        break
                }
            }
        }

        context.drawImage(tmpCanvas, 0, 0, w, h, 0, 0, w, h)

        $('#img').html(canvas)
    }
}