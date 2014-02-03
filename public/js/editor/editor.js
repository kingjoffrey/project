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
        mapHeight = mapWidth = 1025
//        mapHeight = mapWidth = 2049
//        mapHeight = mapWidth = 8193
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
    },
    generate:function(){
        DiamondSquare.pixels = DiamondSquare.make(mapWidth)
        var keys = this.findMinMax(DiamondSquare.pixels)
        Gui.render(DiamondSquare.pixels, keys)
    },
    grid:function(){

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
