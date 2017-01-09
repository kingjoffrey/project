"use strict"
var Fields = new function () {
    var fields = {},
        maxX,
        maxY,
        width,
        height,
        initRoads = function () {
            for (var y = 0; y <= maxY; y++) {
                for (var x = 0; x <= maxX; x++) {
                    var type = fields[y][x].getType()
                    if (type == 'r' || type == 'b') {
                        GameModels.addRoad(x, y)
                    }
                }
            }
        },
        paintMapField = function (x, y, color) {
            mapContext.fillStyle = color;
            mapContext.fillRect(x * 3, y * 3, 3, 3);
        },
        paintTextureField = function (x, y, color) {
            tmpTextureContext.fillStyle = color;
            tmpTextureContext.fillRect(x * 8, y * 8, 8, 8);
        },
        mapCanvas = document.createElement('canvas'),
        mapContext = mapCanvas.getContext('2d'),
        tmpTextureCanvas = document.createElement('canvas'),
        tmpTextureContext = tmpTextureCanvas.getContext('2d'),
        textureCanvas = document.createElement('canvas'),
        textureContext = textureCanvas.getContext('2d')

    this.initGround = function () {
        for (var y in fields) {
            for (var x in fields[y]) {
                switch (fields[y][x].type) {
                    case 'g':
                        paintMapField(x, y, '#009900')
                        paintTextureField(x, y, '#009900')
                        break
                    case 'f':
                        paintMapField(x, y, '#004e00')
                        paintTextureField(x, y, '#004e00')
                        GameModels.addTree(x, y)
                        break
                    case 'w':
                        paintMapField(x, y, '#0000cd')
                        paintTextureField(x, y, '#009900')
                        break
                    case 'h':
                        paintMapField(x, y, '#505200')
                        paintTextureField(x, y, '#505200')
                        break
                    case 'm':
                        paintMapField(x, y, '#262728')
                        paintTextureField(x, y, '#262728')
                        break
                    case 'r':
                        paintMapField(x, y, '#c1c1c1')
                        paintTextureField(x, y, '#009900')
                        break
                    case 'b':
                        paintMapField(x, y, '#c1c1c1')
                        paintTextureField(x, y, '#009900')
                        break
                    case 's':
                        paintMapField(x, y, '#39723E')
                        paintTextureField(x, y, '#009900')
                        GameModels.addSwamp(x, y)
                        break
                }
                this.add(x, y, fields[y][x])
            }
        }
        maxX = x
        maxY = y

        textureContext.translate(0, height * 8)
        textureContext.scale(1, -1)

        textureContext.drawImage(tmpTextureCanvas, 0, 0, width * 8, height * 8, 0, 0, width * 8, height * 8)

        $('#map').append(mapCanvas)

        Ground.init(maxX, maxY, textureCanvas)
    }
    this.initCastle = function (x, y, castleId, color) {
        for (var i = y; i <= y + 1; i++) {
            for (var j = x; j <= x + 1; j++) {
                fields[i][j].setCastle(castleId, color);
            }
        }
    }
    this.getWidth = function () {
        return width
    }
    this.getHeight = function () {
        return height
    }
    this.getMaxX = function () {
        return maxX
    }
    this.getMaxY = function () {
        return maxY
    }
    this.add = function (x, y, field) {
        if (typeof fields[y] == 'undefined') {
            fields[y] = {}
        }
        fields[y][x] = new Field(field)
    }
    /**
     *
     * @param x
     * @param y
     * @returns {Field}
     */
    this.get = function (x, y) {
        if (isSet(fields[y]) && isSet(fields[y][x])) {
            return fields[y][x]
        } else {
            console.log('no field at x=' + x + ' y=' + y)
        }
    }
    this.getAStarType = function (x, y, destX, destY) {
        if (isSet(fields[y]) && isSet(fields[y][x])) {
            var castleId,
                field = fields[y][x],
                armies

            if (castleId = field.getCastleId()) {
                if (CommonMe.sameTeam(field.getCastleColor())) {
                    return 'c'
                } else {
                    if (destX == x && destY == y) {
                        return 'E'
                    } else if (castleId == this.get(destX, destY).getCastleId()) {
                        return 'E'
                    } else {
                        return 'e'
                    }
                }
            } else if (field.hasArmies()) {
                armies = field.getArmies()
                for (var armyId in armies) {
                    if (CommonMe.sameTeam(armies[armyId])) {
                        if (CommonMe.colorEquals(armies[armyId]) && field.getType() == 'w' && CommonMe.getArmy(armyId).canSwim()) {
                            return 'S'
                        } else {
                            return field.getType()
                        }
                    } else {
                        if (destX == x && destY == y) {
                            if (field.getType() == 'w') {
                                if (CommonMe.getSelectedArmy().canSwim() || CommonMe.getSelectedArmy().canFly()) {
                                    return 'E'
                                } else {
                                    return 'e'
                                }
                            } else if (field.getType() == 'm') {
                                if (CommonMe.getSelectedArmy().canFly()) {
                                    return 'E'
                                } else {
                                    return 'e'
                                }
                            } else {
                                return 'E'
                            }
                        } else {
                            return 'e'
                        }
                    }
                }
            } else {
                return field.getType()
            }
        }
    }
    this.init = function (f) {
        fields = f

        width = fields[0].length
        height = fields.length

        mapCanvas.width = width * 3
        mapCanvas.height = height * 3
        tmpTextureCanvas.width = width * 8
        tmpTextureCanvas.height = height * 8
        textureCanvas.width = width * 8
        textureCanvas.height = height * 8

        this.initGround()
        initRoads()
    }
}