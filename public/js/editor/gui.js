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
    render: function (data, keys) {
        if (keys['max'] < 0) {
            return
        }

        var beach = parseInt(keys['water']) + 1
        console.log(keys)

        var minus = {
            water: 139 - parseInt(keys['water']),
            grass: 220 - parseInt(keys['grass']),
            hills: 240 - parseInt(keys['hills']),
            mountains: 45 - parseInt(keys['mountains']),
            snow: 190 - parseInt(keys['snow'])
        }


        for (var i in data) {
            i = parseInt(i)
            for (var j in data[i]) {
                j = parseInt(j)
//                if (data[i][parseInt(j) + 1] < data[i][j]) {
//                    var shadow = 30
//                } else {
                var shadow = 0
//                }

                if (data[i][j] < keys['water']) { // water
                    var rgb = (parseInt(data[i][j]) + minus.water).toString(16)
                    var color = '#0000' + rgb
                } else if (data[i][j] < beach) { // beach
                    if (typeof data[i - 1] == 'undefined' || typeof data[i + 1] == 'undefined' || typeof data[i - 1][j - 1] == 'undefined' || typeof data[i - 1][j] == 'undefined' || typeof data[i - 1][j + 1] == 'undefined' || typeof data[i][j - 1] == 'undefined' || typeof data[i][j + 1] == 'undefined' || typeof data[i + 1][j - 1] == 'undefined' || typeof data[i + 1][j] == 'undefined' || typeof data[i + 1][j + 1] == 'undefined') {
                        var color = '#d9d900'
                    } else if (data[i - 1][j - 1] < keys['water'] && data[i - 1][j] < keys['water'] && data[i - 1][j + 1] < keys['water'] && data[i][j - 1] < keys['water'] && data[i][j + 1] < keys['water'] && data[i + 1][j - 1] < keys['water'] && data[i + 1][j] < keys['water'] && data[i + 1][j + 1] < keys['water']) {
                        var rgb = (parseInt(data[i][j]) + minus.water).toString(16)
                        var color = '#0000' + rgb
                    } else if (data[i - 1][j - 1] > keys['grass'] && data[i - 1][j] > keys['grass'] && data[i - 1][j + 1] > keys['grass'] && data[i][j - 1] > keys['grass'] && data[i][j + 1] > keys['grass'] && data[i + 1][j - 1] > keys['grass'] && data[i + 1][j] > keys['grass'] && data[i + 1][j + 1] > keys['grass']) {
                        var rgb = (256 - parseInt(data[i][j]) - minus.grass).toString(16)
                        var color = '#00' + rgb + '00'
                    } else {
                        var color = '#d9d900'
                    }
                } else if (data[i][j] < keys['grass']) { // grass
                    var rgb = (256 - parseInt(data[i][j]) + shadow - minus.grass).toString(16)
                    var color = '#00' + rgb + '00'
                } else if (data[i][j] < keys['hills']) { // hills
                    var rgb = (256 - parseInt(data[i][j]) + shadow - minus.hills).toString(16)
                    var color = '#' + rgb + rgb + '00'
                } else if (data[i][j] < keys['mountains']) {// mountains
                    var rgb = (parseInt(data[i][j]) + shadow + minus.mountains).toString(16)
                    var color = '#' + rgb + rgb + rgb
                } else { // snow
                    var rgb = (parseInt(data[i][j]) + shadow + minus.snow).toString(16)
                    var color = '#' + rgb + rgb + rgb
                }

                Editor.pixelCanvas.setPixel(i, j, color)
            }
        }

        Editor.map.draw()
    }
}
