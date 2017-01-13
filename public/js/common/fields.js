"use strict"
var Fields = new function () {
    var fields = [],
        grassField,
        maxX,
        maxY,
        mapMultiplier = 7,
        textureMultiplier = 16,
        mapCanvas = document.createElement('canvas'),
        mapContext = mapCanvas.getContext('2d'),
        tmpTextureCanvas = document.createElement('canvas'),
        tmpTextureContext = tmpTextureCanvas.getContext('2d'),
        textureCanvas = document.createElement('canvas'),
        textureContext = textureCanvas.getContext('2d'),
        initRoads = function () {
            for (var y = 0; y < maxY; y++) {
                for (var x = 0; x < maxX; x++) {
                    var type = fields[y][x].getType()
                    if (type == 'r' || type == 'b') {
                        GameModels.addRoad(x, y)
                    }
                }
            }
        },
        paintMapField = function (x, y, color) {
            mapContext.fillStyle = color;
            mapContext.fillRect(x * mapMultiplier, y * mapMultiplier, mapMultiplier, mapMultiplier);
        },
        paintTextureField = function (x, y, color) {
            tmpTextureContext.fillStyle = color;
            tmpTextureContext.fillRect(x * textureMultiplier, y * textureMultiplier, textureMultiplier, textureMultiplier);
        }

    this.initCastle = function (x, y, castleId, color) {
        for (var i = y; i <= y + 1; i++) {
            for (var j = x; j <= x + 1; j++) {
                fields[i][j].setCastle(castleId, color);
            }
        }
    }
    this.getMaxX = function () {
        return maxX
    }
    this.getMaxY = function () {
        return maxY
    }
    this.getCanvas = function () {
        return textureCanvas
    }
    this.add = function (x, y, type) {
        if (typeof fields[y] == 'undefined') {
            fields[y] = []
        }
        fields[y][x] = new Field(type)
    }
    /**
     *
     * @param x
     * @param y
     * @returns {Field}
     */
    this.get = function (x, y, grass) {
        if (isSet(fields[y]) && isSet(fields[y][x])) {
            return fields[y][x]
        } else {
            if (isSet(grass)) {
                return grassField
            } else {
                console.log('no field at x=' + x + ' y=' + y)
            }
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
    this.createTextures = function () {
        for (var y in fields) {
            for (var x in fields[y]) {
                switch (this.get(x, y).getType()) {
                    case 'g':
                        paintMapField(x, y, '#009900')
                        paintTextureField(x, y, '#009900')
                        break
                    case 'f':
                        paintMapField(x, y, '#004e00')
                        paintTextureField(x, y, '#004e00')
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
                        break
                }
            }
        }

        textureContext.drawImage(tmpTextureCanvas, 0, 0, maxX * textureMultiplier, maxY * textureMultiplier, 0, 0, maxX * textureMultiplier, maxY * textureMultiplier)
    }
    this.init = function (f) {
        grassField = new Field('g')

        for (var y in f) {
            // if (y > 31) {
            //     continue
            // }
            for (var x in f[y]) {
                // if (x > 31) {
                //     continue
                // }
                var type = f[y][x]
                switch (type) {
                    case 'f':
                        GameModels.addTree(x, y)
                        break
                    case 's':
                        GameModels.addSwamp(x, y)
                        break
                }
                this.add(x, y, type)
            }
        }

        // var height = y * 1 - 1
        // var r = f.reverse()
        // for (var y in r) {
        //     if (y < 1) {
        //         continue
        //     }
        //     for (var x in r[y]) {
        //         if (x > 31) {
        //             continue
        //         }
        //         var type = r[y][x],
        //             yy = y * 1 + height
        //         switch (type) {
        //             case 'f':
        //                 GameModels.addTree(x, yy)
        //                 break
        //             case 's':
        //                 GameModels.addSwamp(x, yy)
        //                 break
        //         }
        //         this.add(x, yy, type)
        //     }
        // }

        maxX = fields[0].length
        maxY = fields.length

        mapCanvas.width = maxX * mapMultiplier
        mapCanvas.height = maxY * mapMultiplier
        tmpTextureCanvas.width = maxX * textureMultiplier
        tmpTextureCanvas.height = maxY * textureMultiplier
        textureCanvas.width = maxX * textureMultiplier
        textureCanvas.height = maxY * textureMultiplier

        textureContext.translate(0, maxY * textureMultiplier)
        textureContext.scale(1, -1)

        this.createTextures()
        $('#map').append(mapCanvas)
        Ground.init(maxX, maxY, textureCanvas)
        initRoads()
    }
    // this.getFields = function () {
    //     var xxx = []
    //     for (var y in fields) {
    //         xxx[y] = []
    //         for (var x in fields[y]) {
    //             xxx[y][x] = this.get(x, y).getType()
    //         }
    //     }
    //     return xxx
    // }
}