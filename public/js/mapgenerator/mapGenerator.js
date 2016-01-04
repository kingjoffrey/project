var MapGenerator = new function () {
    var DATA_SIZE = 1025,
        group = null,
        map = null,
        pixels = [],
        water = 10,
        grass = 60,
        hills = 25,
        mountains = 4,
        snow = 1,
        pixelCanvas = document.createElement("canvas"),
        brush = null,
        init = 0

    this.getImage = function () {
        return pixelCanvas.toDataURL('image/png')
    }
    this.getInit = function () {
        return init
    }
    this.init = function () {
        init = 1
        //var stage = new Kinetic.Stage({
        //    container: 'board',
        //    width: $(window).width(),
        //    height: $(window).height()
        //})

        var layer = new Kinetic.Layer(),
            ctx = pixelCanvas.getContext("2d")

        this.resetPixelCanvas()
        pixelCanvas.setPixel = function (x, y, color) {
            ctx.fillStyle = color;
            ctx.fillRect(x, y, 1, 1);
        }
        this.generate()

        //var imgURL = '/img/maps/' + mapId + '.png'

        //$.get(imgURL)
        //    .done(function () {
        //        console.log('aaa')
        //        var img = new Image()
        //        img.src = imgURL
        //        img.onload = function () {
        //            console.log('bbb')
        //            ctx.drawImage(img, 0, 0, this.width, this.height, 0, 0, DATA_SIZE, DATA_SIZE)
        //            //Editor.group.draw()
        //        }
        //    }).fail(function () {
        //    console.log('Image doesn\'t exist - do something else.')
        //})

        //group = new Kinetic.Group({
        //    x: 0,
        //    y: 0,
        //    width: DATA_SIZE,
        //    height: DATA_SIZE,
        //    draggable: true
        //})

        //map = new Kinetic.Image({
        //   x: 0,
        //   y: 0,
        //   image: pixelCanvas
        //})

        //group.add(map)

        //layer.add(group)
        //stage.add(layer)
    }
    this.resetPixelCanvas = function () {
        pixelCanvas.width = DATA_SIZE
        pixelCanvas.height = DATA_SIZE
        pixelCanvas.pixels = []
    }
    this.generate = function () {
        var pixels = DiamondSquare.make(DATA_SIZE)
        var keys = this.splitTerrain(pixels)
        if (keys['max'] < 0) {
            console.log('max < 0')
            return
        }

        this.render(pixels, keys)
    }
    this.render = function (pixels, keys) {
        var data = this.clearBorders(pixels, keys),
            minus = {
                water: 139 - parseInt(keys.water),
                grass: 220 - parseInt(keys.grass),
                hills: 240 - parseInt(keys.hills),
                mountains: 45 - parseInt(keys.mountains),
                snow: 190 - parseInt(keys.snow)
            }
        //var shadow = 0

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

                pixelCanvas.setPixel(i, j, color)
            }
        }

        WebSocketEditor.save()
    }
    this.grid = function (size) {
        var grid = []
        for (var y = 0; y < size; y++) {
            data[y] = []
            for (var x = 0; x < size; x++) {
                if (size)
                    data[y][x]
            }
        }
    }
    this.splitTerrain = function (data) {
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
    }
    this.clearBorders = function (pixels, keys) {
        var data = {}

        for (var i in pixels) {
            data[i] = {}
            for (var j in pixels[i]) {
                if (pixels[i][j] < keys['water']) { // water
                    data[i][j] = 1
                } else if (pixels[i][j] < keys['grass']) { // grass
                    data[i][j] = 3
                } else if (pixels[i][j] < keys['hills']) { // hills
                    data[i][j] = 4
                } else if (pixels[i][j] < keys['mountains']) {// mountains
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

        return data
    }
    this.removeDots = function (x, y, data) {
        var terrainType = data[x][y],
            matchCount = 0,
            otherTerrainType = 0,
            x = parseInt(x),
            y = parseInt(y)

        for (var i = -1; i <= 1; i++) {
            for (var j = -1; j <= 1; j++) {

                var checkedX = x + i
                if (checkedX > DATA_SIZE - 1) {
                    checkedX = 0
                } else if (checkedX < 0) {
                    checkedX = DATA_SIZE - 1
                }

                var checkedY = y + j
                if (checkedY > DATA_SIZE - 1) {
                    checkedY = 0
                } else if (checkedY < 0) {
                    checkedY = DATA_SIZE - 1
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
    }
    this.replacePixelsBetween = function (x, y, data) {
        var terrainType = data[x][y],
            x = parseInt(x),
            y = parseInt(y)

        var xMinusOne = x - 1,
            xPlusOne = x + 1,
            yMinusOne = y - 1,
            yPlusOne = y + 1

        if (xMinusOne < 0) {
            xMinusOne = DATA_SIZE - 1
        }
        if (xPlusOne > DATA_SIZE - 1) {
            xPlusOne = 0
        }

        if (yMinusOne < 0) {
            yMinusOne = DATA_SIZE - 1
        }
        if (yPlusOne > DATA_SIZE - 1) {
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
