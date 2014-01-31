$(document).ready(function () {
    Editor.init()
})

var Editor = {
    layer: new Kinetic.Layer(),
    map: null,
    pixels: [],
    water: 10,
    grass: 70,
    hills: 95,
    mountains: 99,
    snow: 100,
    pixelCanvas: null,
    init: function () {
//        mapHeight = mapWidth = 1025
//        mapHeight = mapWidth = 2049
        mapHeight = mapWidth = 8193
        var stage = new Kinetic.Stage({
            container: 'board',
            width: $(window).width(),
            height: $(window).height()
        })

        this.pixelCanvas = document.createElement("canvas");
        var ctx = this.pixelCanvas.getContext("2d");
        this.pixelCanvas.width = mapWidth;
        this.pixelCanvas.height = mapHeight;
        this.pixelCanvas.pixels = []
        this.pixelCanvas.setPixel = function (x, y, color) {
            ctx.fillStyle = color;
            ctx.fillRect(x, y, 1, 1);
        };

        this.map = new Kinetic.Image({
            x: 0,
            y: 0,
            image: this.pixelCanvas,
            draggable: true
        });

        this.layer.add(this.map);

        stage.add(this.layer)

        this.pixels = this.diamondSquare(mapWidth)
        var keys = this.findMinMax(this.pixels)
        this.render(this.pixels, keys)
    },
    findMinMax: function (data) {
        var values = [],
            keys = {}
        for (var i in data) {
            for (var j in data[i]) {
                var v = data[i][j]
                if (typeof values[v] == 'undefined') {
                    values[v] = 1
                } else {
                    values[v]++
                }
            }
        }

        var summation = 0,
            all = mapWidth * mapHeight,
            water = all * (this.water / 100),
            grass = all * (this.grass / 100),
            hills = all * (this.hills / 100),
            mountains = all * (this.mountains / 100),
            snow = all * (this.snow / 100)

        for (var i in values) {
            if (typeof keys['min'] == 'undefined') {
                keys['min'] = i
            }
            summation += values[i]
            if (summation < water) {
                keys['water'] = i
            } else if (summation < grass) {
                keys['grass'] = i
            } else if (summation < hills) {
                keys['hills'] = i
            } else if (summation < mountains) {
                keys['mountains'] = i
            } else if (summation < snow) {
                keys['snow'] = i
            } else {
                keys['max'] = i
            }
        }
        return keys
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
                } else
                if (data[i][j] < keys['mountains'])
                {// mountains
                    var rgb = (parseInt(data[i][j]) + shadow + minus.mountains).toString(16)
                    var color = '#' + rgb + rgb + rgb
                }
        else { // snow
                    var rgb = (parseInt(data[i][j]) + shadow + minus.snow).toString(16)
                    var color = '#' + rgb + rgb + rgb
                }

                this.pixelCanvas.setPixel(i, j, color)
            }
        }
        this.map.draw()
    },
    diamondSquare: function (DATA_SIZE) {
        var SEED = 128.0;
        var data = [];
        for (var i = 0; i < DATA_SIZE; ++i) {
            data[i] = [];
        }
        data[0][0] = data[0][DATA_SIZE - 1] = data[DATA_SIZE - 1][0] = data[DATA_SIZE - 1][DATA_SIZE - 1] = SEED;

        var h = 128.0;//the range (-h -> +h) for the average offset

        for (var sideLength = DATA_SIZE - 1; sideLength >= 2; sideLength /= 2, h /= 2.0) {
            var halfSide = sideLength / 2;

            for (var x = 0; x < DATA_SIZE - 1; x += sideLength) {
                for (var y = 0; y < DATA_SIZE - 1; y += sideLength) {
                    var avg = data[x][y] + //top left
                        data[x + sideLength][y] +//top right
                        data[x][y + sideLength] + //lower left
                        data[x + sideLength][y + sideLength];//lower right
                    avg /= 4.0;

                    data[x + halfSide][y + halfSide] = parseInt(avg + (Math.random() * 2 * h) - h)
                }
            }

            for (var x = 0; x < DATA_SIZE - 1; x += halfSide) {
                for (var y = (x + halfSide) % sideLength; y < DATA_SIZE - 1; y += sideLength) {
                    var avg =
                        data[(x - halfSide + DATA_SIZE - 1) % (DATA_SIZE - 1)][y] + //left of center
                            data[(x + halfSide) % (DATA_SIZE - 1)][y] + //right of center
                            data[x][(y + halfSide) % (DATA_SIZE - 1)] + //below center
                            data[x][(y - halfSide + DATA_SIZE - 1) % (DATA_SIZE - 1)]; //above center

                    avg /= 4.0;

                    avg = parseInt(avg + (Math.random() * 2 * h) - h)
                    data[x][y] = avg;

                    if (x == 0) {
                        data[DATA_SIZE - 1][y] = avg;
                    }

                    if (y == 0) {
                        data[x][DATA_SIZE - 1] = avg;
                    }
                }
            }
        }

        return data;
    }
}
