"use strict"
var Fields = new function () {
    var fields = [],
        grassField,
        maxX,
        maxY,
        textureMultiplier = 16,
        tmpWaterTextureCanvas = document.createElement('canvas'),
        tmpWaterTextureContext = tmpWaterTextureCanvas.getContext('2d'),
        waterTextureCanvas = document.createElement('canvas'),
        waterTextureContext = waterTextureCanvas.getContext('2d'),
        tmpTextureCanvas = document.createElement('canvas'),
        tmpTextureContext = tmpTextureCanvas.getContext('2d'),
        textureCanvas = document.createElement('canvas'),
        textureContext = textureCanvas.getContext('2d'),
        initRoads = function () {
            for (var y = 0; y < maxY; y++) {
                for (var x = 0; x < maxX; x++) {
                    var type = Fields.get(x, y, 1).getType()
                    if (type == 'r' || type == 'b') {
                        GameModels.addRoad(x, y)
                    }
                }
            }
        },
        paintTextureField = function (x, y, color1, color2, percent) {
            var howManyTimes = textureMultiplier * textureMultiplier * (percent / 100)

            tmpTextureContext.fillStyle = color1
            tmpTextureContext.fillRect(x * textureMultiplier, y * textureMultiplier, textureMultiplier, textureMultiplier)
            tmpTextureContext.fillStyle = color2

            for (var i = 0; i < howManyTimes; i++) {
                var valueX = Math.floor((Math.random() * textureMultiplier)),
                    valueY = Math.floor((Math.random() * textureMultiplier))

                tmpTextureContext.fillRect(x * textureMultiplier + valueX, y * textureMultiplier + valueY, 1, 1)
            }
        },
        paintWaterTextureField = function (x, y, color1, color2, percent) {
            var howManyTimes = textureMultiplier * textureMultiplier * (percent / 100)

            tmpWaterTextureContext.fillStyle = color1
            tmpWaterTextureContext.fillRect(x * textureMultiplier, y * textureMultiplier, textureMultiplier, textureMultiplier)
            tmpWaterTextureContext.fillStyle = color2

            for (var i = 0; i < howManyTimes; i++) {
                var valueX = Math.floor((Math.random() * textureMultiplier)),
                    valueY = Math.floor((Math.random() * textureMultiplier))

                tmpWaterTextureContext.fillRect(x * textureMultiplier + valueX, y * textureMultiplier + valueY, 1, 1)
            }
        }

    this.initCastle = function (x, y, castleId, color) {
        for (var i = y; i <= y + 1; i++) {
            for (var j = x; j <= x + 1; j++) {
                fields[i][j].setCastle(castleId, color)
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
                        paintTextureField(x, y, '#3c963c', '#3fa342', 5)
                        paintWaterTextureField(x, y, '#365294', '#526daa', 0)
                        break
                    case 'f':
                        paintTextureField(x, y, '#3c963c', '#0b7e22', 5)
                        paintWaterTextureField(x, y, '#365294', '#526daa', 0)
                        GameModels.addTree(x, y)
                        break
                    case 'w':
                        paintTextureField(x, y, '#3c963c', '#ffff7f', 10)
                        paintWaterTextureField(x, y, '#365294', '#526daa', 1)
                        break
                    case 'h':
                        paintTextureField(x, y, '#0b7e22', '#3c963c', 1)
                        paintWaterTextureField(x, y, '#365294', '#526daa', 0)
                        break
                    case 'm':
                        paintTextureField(x, y, '#ededed', '#131313', 1)
                        paintWaterTextureField(x, y, '#365294', '#526daa', 0)
                        break
                    case 'r':
                        paintTextureField(x, y, '#3c963c', '#c4c720', 0)
                        paintWaterTextureField(x, y, '#365294', '#526daa', 0)
                        break
                    case 'b':
                        paintTextureField(x, y, '#3c963c', '#ffff7f', 10)
                        paintWaterTextureField(x, y, '#365294', '#526daa', 1)
                        break
                    case 's':
                        paintTextureField(x, y, '#3c963c', '#828396', 60)
                        paintWaterTextureField(x, y, '#365294', '#526daa', 0)
                        break
                }
            }
        }

        textureContext.drawImage(tmpTextureCanvas, 0, 0, maxX * textureMultiplier, maxY * textureMultiplier, 0, 0, maxX * textureMultiplier, maxY * textureMultiplier)
        waterTextureContext.drawImage(tmpWaterTextureCanvas, 0, 0, maxX * textureMultiplier, maxY * textureMultiplier, 0, 0, maxX * textureMultiplier, maxY * textureMultiplier)
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
//                 var type = f[y][x]
//                 switch (type) {
//                     case 'f':
//                         GameModels.addTree(x, y)
//                         break
//                     case 's':
//                         GameModels.addSwamp(x, y)
//                         break
//                 }
                this.add(x, y, f[y][x])
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

        tmpWaterTextureCanvas.width = maxX * textureMultiplier
        tmpWaterTextureCanvas.height = maxY * textureMultiplier
        waterTextureCanvas.width = maxX * textureMultiplier
        waterTextureCanvas.height = maxY * textureMultiplier

        tmpTextureCanvas.width = maxX * textureMultiplier
        tmpTextureCanvas.height = maxY * textureMultiplier
        textureCanvas.width = maxX * textureMultiplier
        textureCanvas.height = maxY * textureMultiplier

        waterTextureContext.translate(0, maxY * textureMultiplier)
        waterTextureContext.scale(1, -1)

        textureContext.translate(0, maxY * textureMultiplier)
        textureContext.scale(1, -1)

        this.createTextures()
        Ground.init(maxX, maxY, textureCanvas, waterTextureCanvas)
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
