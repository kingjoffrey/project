var Gui = {
    init: function () {
        $('#generate').click(function () {
            Editor.generate()
        })
        $('#save').click(function () {
            EditorWS.save()
        })
        $('#castle').click(function (e) {
            Editor.brush = 'castle'
        })
        $('#eraser').click(function () {
            Editor.brush = 'eraser'
        })
    },
    showGrid: function (size) {
        var max = size / 40

        var rect = new Kinetic.Rect({
            x: 0,
            y: 0,
            width: 38,
            height: 38,
            stroke: 'black',
            strokeWidth: 1
        })

        Editor.group.add(rect)
    },
    hideGrid: function () {

    },
    render: function (pixels, data, keys) {
        Editor.resetPixelCanvas()

        var minus = {
            water: 139 - parseInt(keys.water),
            grass: 220 - parseInt(keys.grass),
            hills: 240 - parseInt(keys.hills),
            mountains: 45 - parseInt(keys.mountains),
            snow: 190 - parseInt(keys.snow)
        }
        var shadow = 0

        for (var i in data) {
            for (var j in data[i]) {
                switch (data[i][j]) {
                    case 1:
                        var color = '#0000' + (parseInt(pixels[i][j]) + minus.water).toString(16)
                        break
                    case 3:
                        var rgb = (256 - parseInt(pixels[i][j]) - minus.grass).toString(16)
                        var color = '#00' + rgb + '00'
                        break
                    case 4:
                        var rgb = (256 - parseInt(pixels[i][j]) - minus.hills).toString(16)
                        var color = '#' + rgb + rgb + '00'
                        break
                    case 5:
                        var rgb = (parseInt(pixels[i][j]) + minus.mountains).toString(16)
                        var color = '#' + rgb + rgb + rgb
                        break
                    case 6:
                        var rgb = (parseInt(pixels[i][j]) + minus.snow).toString(16)
                        var color = '#' + rgb + rgb + rgb
                        break
                }

                Editor.pixelCanvas.setPixel(i, j, color)
            }
        }
        Editor.map.setSize({width: mapWidth, height: mapHeight})
        Editor.map.draw()
    }
}
