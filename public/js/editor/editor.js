var Editor = {
    group: null,
    map: null,
    pixels: [],
    water: 10,
    grass: 70,
    hills: 95,
    mountains: 99,
    snow: 100,
    pixelCanvas: null,
    init: function () {
        var stage = new Kinetic.Stage({
            container: 'board',
            width: $(window).width(),
            height: $(window).height()
        })

        var layer = new Kinetic.Layer()

        this.pixelCanvas = document.createElement("canvas");
        var ctx = this.pixelCanvas.getContext("2d");
        this.pixelCanvas.width = mapWidth
        this.pixelCanvas.height = mapHeight
        this.pixelCanvas.pixels = []
        this.pixelCanvas.setPixel = function (x, y, color) {
            ctx.fillStyle = color;
            ctx.fillRect(x, y, 1, 1);
        }

        var imgURL = '/img/maps/' + mapId + '.png'

        $.get(imgURL)
            .done(function () {
                var img = new Image()
                img.src = imgURL
                img.onload = function () {
                    ctx.drawImage(img, 0, 0)
                    Editor.group.draw()
                }
            }).fail(function () {
                console.log('Image doesn\'t exist - do something else.')
            })

        this.group = new Kinetic.Group({
            x: 0,
            y: 0,
            width: mapWidth,
            height: mapHeight,
            draggable: true
        })

        this.map = new Kinetic.Image({
            x: 0,
            y: 0,
            image: this.pixelCanvas
        })

        this.group.add(this.map)
        layer.add(this.group)
        stage.add(layer)
    },
    generate: function () {
        DiamondSquare.pixels = DiamondSquare.make(1025)
        var keys = this.findMinMax(DiamondSquare.pixels)
        Gui.render(DiamondSquare.pixels, keys)
    },
    grid: function (size) {
        var grid = []
        for (var y = 0; y < size; y++) {
            data[y] = []
            for (var x = 0; x < size; x++) {
                if (size)
                    data[y][x]
            }
        }
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
    }
}
