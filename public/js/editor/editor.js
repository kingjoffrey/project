var Editor = {
    DATA_SIZE: 1025,
    group: null,
    map: null,
    pixels: [],
    water: 10,
    grass: 60,
    hills: 25,
    mountains: 4,
    snow: 1,
    pixelCanvas: null,
    brush: null,
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
                    ctx.drawImage(img, 0, 0, this.width, this.height, 0, 0, mapWidth, mapHeight)
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

        this.group.on('mouseup touchend', function (e) {
            Editor.click(e)
        })

        this.map = new Kinetic.Image({
            x: 0,
            y: 0,
            image: this.pixelCanvas
        })

        this.group.add(this.map)

        for (var castleId in mapCastles) {
            Castle.create(mapCastles[castleId].x * 40, mapCastles[castleId].y * 40)
        }

        layer.add(this.group)
        stage.add(layer)
    },
    click: function (e) {
        if (!this.brush) {
            return
        }

        if (e.which == 3) {
            this.brush = null
            return
        }

        switch (this.brush) {
            case 'castle':
                var x = parseInt((e.x - this.group.getPosition().x) / 40)
                var X = x * 40
                var y = parseInt((e.y - this.group.getPosition().y) / 40)
                var Y = y * 40

                Castle.create(X, Y, x, y)
                break;
        }
    },
    generate: function () {
        DiamondSquare.pixels = DiamondSquare.make(this.DATA_SIZE)
        var keys = this.splitTerrain(DiamondSquare.pixels)
        DiamondSquare.pixels = this.clearBorders(DiamondSquare.pixels, keys)
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
    splitTerrain: function (data) {
        var valueCountMappings = [],
            keys = {},
            counter = 0

        for (var i in data) {
            for (var j in data[i]) {
                var v = data[i][j]
                if (typeof valueCountMappings[v] == 'undefined') {
                    valueCountMappings[v] = 1
                } else {
                    valueCountMappings[v]++
                }
                counter++
            }
        }

//        console.log(valueCountMappings)

        var summation = 0,
            water = counter * (this.water / 100),
            grass = water + counter * (this.grass / 100),
            hills = grass + counter * (this.hills / 100),
            mountains = hills + counter * (this.mountains / 100),
            snow = mountains + counter * (this.snow / 100)

        for (var i in valueCountMappings) {
            if (typeof keys['min'] == 'undefined') {
                keys['min'] = i
            }
            summation += valueCountMappings[i]
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
    clearBorders: function (data, keys) {
        if (keys['max'] < 0) {
            return
        }

//        var beach = parseInt(keys['water']) + 1

        for (var i in data) {
            for (var j in data[i]) {
                if (data[i][j] < keys['water']) { // water
                    data[i][j] = 1
//                } else if (data[i][j] < beach) { // beach
//                    data[i][j] = 2
                } else if (data[i][j] < keys['grass']) { // grass
                    data[i][j] = 3
                } else if (data[i][j] < keys['hills']) { // hills
                    data[i][j] = 4
                } else if (data[i][j] < keys['mountains']) {// mountains
                    data[i][j] = 5
                } else { // snow
                    data[i][j] = 6
                }
            }
        }

        for (var i in data) {
            for (var j in data[i]) {
                data = this.removeDots(i, j, data)
            }
        }
        for (var i in data) {
            for (var j in data[i]) {
                data = this.removeDots(i, j, data)
            }
        }
        for (var i in data) {
            for (var j in data[i]) {
                data = this.replacePixelsBetween(i, j, data)
            }
        }
//        this.replacePixelsBetween(1024, 1024, data)
        return data
    },
    removeDots: function (x, y, data) {
        var terrainType = data[x][y],
            matchCount = 0,
            otherTerrainType = 0,
            x = parseInt(x),
            y = parseInt(y)

        for (var i = -1; i <= 1; i++) {
            for (var j = -1; j <= 1; j++) {

                var checkedX = x + i
                if (checkedX > this.DATA_SIZE - 1) {
                    checkedX = 0
                } else if (checkedX < 0) {
                    checkedX = this.DATA_SIZE - 1
                }

                var checkedY = y + j
                if (checkedY > this.DATA_SIZE - 1) {
                    checkedY = 0
                } else if (checkedY < 0) {
                    checkedY = this.DATA_SIZE - 1
                }

                if (data[checkedX][checkedY] == terrainType) {
                    matchCount++
                } else {
                    otherTerrainType = data[checkedX][checkedY]
                }
            }
        }

        if (matchCount < 6) {
            data[x][y] = otherTerrainType
        }

        return data
    },
    replacePixelsBetween: function (x, y, data) {
        var terrainType = data[x][y],
            x = parseInt(x),
            y = parseInt(y)

        var xMinusOne = x - 1,
            xPlusOne = x + 1,
            yMinusOne = y - 1,
            yPlusOne = y + 1

        if (xMinusOne < 0) {
            xMinusOne = this.DATA_SIZE - 1
        }
        if (xPlusOne > this.DATA_SIZE - 1) {
            xPlusOne = 0
        }

        if (yMinusOne < 0) {
            yMinusOne = this.DATA_SIZE - 1
        }
        if (yPlusOne > this.DATA_SIZE - 1) {
            yPlusOne = 0
        }

        if (data[xMinusOne][y] != terrainType && data[xPlusOne][y] != terrainType) {
            data[x][y] = data[xMinusOne][y]
            return data
        }

        if (data[x][yMinusOne] != terrainType && data[x][yPlusOne] != terrainType) {
            data[x][y] = data[x][yMinusOne]
            return data
        }

        return data
    }
}
