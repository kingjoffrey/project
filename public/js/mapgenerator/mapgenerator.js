var MapGenerator = new function () {
    var DATA_SIZE = 1025,
        canvas = document.createElement('canvas'),
        ctx = canvas.getContext('2d'),
        init = 0,
        fields = [],
        fieldsNumber

    this.getFields = function () {
        return fields
    }
    this.getImage = function () {
        return canvas.toDataURL('image/png')
    }
    this.getInit = function () {
        return init
    }
    this.init = function (mapSize) {
        init = 1
        fieldsNumber = mapSize

        resetCanvas()
        generate()
    }
    var resetCanvas = function () {
            canvas.width = DATA_SIZE - 1
            canvas.height = DATA_SIZE - 1
            canvas.pixels = []
        },
        setPixel = function (x, y, color) {
            ctx.fillStyle = color;
            ctx.fillRect(x, y, 1, 1);
        },
        generate = function () {
            var pixels = DiamondSquare.make(DATA_SIZE)
            var keys = splitTerrain(pixels)
            if (keys['max'] < 0) {
                console.log('max < 0')
                generate()
                return
            }

            createImage(pixels, keys)
        },
        createImage = function (pixels, keys) {
            var data = clearBorders(pixels, keys),
                minus = {
                    water: 139 - parseInt(keys.water),
                    grass: 220 - parseInt(keys.grass),
                    hills: 240 - parseInt(keys.hills),
                    mountains: 45 - parseInt(keys.mountains),
                    snow: 190 - parseInt(keys.snow)
                },
                x = 0,
                y = 0,
                fieldSize = (DATA_SIZE - 1) / (fieldsNumber - 1),
                imageData = [],
                maxI = 0, maxJ = 0

            ctx.translate(0, canvas.height)
            ctx.rotate(270 * Math.PI / 180)

            for (var maxI in data) {
                imageData[maxI] = {}
                for (var maxJ in data[maxI]) {
                    switch (data[maxI][maxJ]) { //OLD
                        case 1:
                            //var color = '#0000' + (parseInt(pixels[maxI][maxJ]) + minus.water).toString(16),
                            var rgb = (256 - parseInt(pixels[maxI][maxJ]) - minus.grass).toString(16),
                                color = '#00' + rgb + '00',
                                type = 'w'
                            break
                        case 3:
                            var rgb = (256 - parseInt(pixels[maxI][maxJ]) - minus.grass).toString(16),
                                color = '#00' + rgb + '00',
                                type = 'g'
                            break
                        case 4:
                            var rgb = (256 - parseInt(pixels[maxI][maxJ]) - minus.hills).toString(16),
                                color = '#' + rgb + rgb + '00',
                                type = 'h'
                            break
                        case 5:
                            var rgb = (parseInt(pixels[maxI][maxJ]) + minus.mountains).toString(16),
                                color = '#' + rgb + rgb + rgb,
                                type = 'm'
                            break
                        case 6:
                            var rgb = (parseInt(pixels[maxI][maxJ]) + minus.snow).toString(16),
                                color = '#' + rgb + rgb + rgb,
                                type = 'm'
                            break
                    }

                    if (maxI % fieldSize == 0 && maxJ % fieldSize == 0) {
                        if (notSet(fields[y])) {
                            fields[y] = []
                        }
                        fields[y][x] = type
                        x++
                    }
                    imageData[maxI][maxJ] = color
                }
                if (maxI % fieldSize == 0) {
                    x = 0
                    y++
                }
            }
            var cutLength = Math.floor(fieldSize / 2), offset = DATA_SIZE - cutLength
            for (i = 0; i < maxI; i++) {
                if (i < offset) {
                    for (j = 0; j < maxJ; j++) {
                        if (j < offset) {
                            setPixel(i + cutLength, j + cutLength, imageData[i][j])
                        } else {
                            setPixel(i + cutLength, j - offset + 1, imageData[i][j])
                        }
                    }
                } else {
                    for (j = 0; j < maxJ; j++) {
                        if (j < offset) {
                            setPixel(i - offset + 1, j + cutLength, imageData[i][j])
                        } else {
                            setPixel(i - offset + 1, j - offset + 1, imageData[i][j])
                        }
                    }
                }
            }
            WebSocketEditor.save()
        },
        grid = function (size) {
            var grid = []
            for (var y = 0; y < size; y++) {
                data[y] = []
                for (var x = 0; x < size; x++) {
                    if (size)
                        data[y][x]
                }
            }
        },
        splitTerrain = function (data) {
            var valueCountMappings = [],
                keys = {},
                counter = 0,
                water = 10,
                grass = 60,
                hills = 25,
                mountains = 4,
                snow = 1

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
                water = counter * (water / 100),
                grass = water + counter * (grass / 100),
                hills = grass + counter * (hills / 100),
                mountains = hills + counter * (mountains / 100),
                snow = mountains + counter * (snow / 100)

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
        clearBorders = function (pixels, keys) {
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
                    data = removeDots(i, j, data)
                }
            }
            for (var i in data) {
                for (var j in data[i]) {
                    data = removeDots(i, j, data)
                }
            }
            for (var i in data) {
                for (var j in data[i]) {
                    data = replacePixelsBetween(i, j, data)
                }
            }

            return data
        },
        removeDots = function (x, y, data) {
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
        },
        replacePixelsBetween = function (x, y, data) {
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
