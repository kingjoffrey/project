$(document).ready(function () {
    Editor.init()
})

var Editor = {
    layer: new Kinetic.Layer(),
    map: null,
    ctx: null,
    init: function () {
        mapHeight = mapWidth = 1025
//        mapHeight = mapWidth = 2049
//        mapHeight = mapWidth = 8193
        var stage = new Kinetic.Stage({
            container: 'board',
            width: $(window).width(),
            height: $(window).height()
        })

        var pixelCanvas = document.createElement("canvas");
        var ctx = pixelCanvas.getContext("2d");
        pixelCanvas.width = mapWidth;
        pixelCanvas.height = mapHeight;
        pixelCanvas.pixels = []
        pixelCanvas.setPixel = function (x, y, color) {
            ctx.fillStyle = color;
            ctx.fillRect(x, y, 1, 1);
        };

        var pixels = new Kinetic.Image({
            x: 0,
            y: 0,
            image: pixelCanvas,
            draggable: true
        });

        var drugi = new Kinetic.Image({
            x: mapWidth,
            y: 0,
            image: pixelCanvas,
            draggable: true
        });

        this.layer.add(pixels);
        this.layer.add(drugi)
        stage.add(this.layer)

        var data = this.diamondSquare(mapWidth)

        for (var i in data) {
            for (var j in data[i]) {
                if (data[i][parseInt(j) + 1] < data[i][j]) {
                    var shadow = 10
                } else {
                    var shadow = 0
                }
                if (data[i][j] < 120) { // water
                    var rgb = (parseInt(data[i][j]) + shadow).toString(16)
                    var color = '#0000' + rgb + ''
                } else { // land
                    if (data[i][j] < 121) { // beach
                        var rgb = (parseInt(data[i][j]) + 50 + shadow).toString(16)
                        var color = '#' + rgb + rgb + '00'
                    } else {
                        if (data[i][j] < 175) { // grass
                            var rgb = (256 - parseInt(data[i][j]) + shadow).toString(16)
                            var color = '#00' + rgb + '00'
                        } else {
                            if (data[i][j] < 210) { // hills
                                var rgb = (256 - parseInt(data[i][j]) + shadow).toString(16)
                                var color = '#' + rgb + rgb + '00'
                            } else { // mountains
                                if (data[i][j] < 220) {
                                    var rgb = (parseInt(data[i][j]) - 150 + shadow).toString(16)
                                    var color = '#' + rgb + rgb + rgb
                                } else { // snow
                                    var rgb = (parseInt(data[i][j]) + shadow).toString(16)
                                    var color = '#' + rgb + rgb + rgb
                                }
                            }
                        }
                    }
                }
//                var color = '#' + rgb + rgb + rgb
                pixelCanvas.setPixel(i, j, color)
            }
        }
        pixels.draw();
        drugi.draw()
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

                    data[x + halfSide][y + halfSide] = avg + (Math.random() * 2 * h) - h;
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

                    avg = avg + (Math.random() * 2 * h) - h;
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
