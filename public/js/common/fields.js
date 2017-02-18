"use strict"
var Fields = new function () {
    var fields,
        grassField,
        maxX,
        maxY,
        textureMultiplier = 16,
        textureCanvas,
        waterTextureCanvas,
        paintMountain = function (tmpTextureContext, x, y) {
            var x = x * 1,
                y = y * 1,
                color1 = '#ededed',
                color2 = '#131313',
                color3 = '#0b7e22',
                f0, f1, f2, f3, f4, f5, f6, f7,
                newX = x * textureMultiplier,
                newY = y * textureMultiplier,
                half = textureMultiplier / 2,
                halfPlus = textureMultiplier + half

            paintTextureField(tmpTextureContext, x, y, color1, color2, 1)

            // f2 = isMountain(Fields.get(x + 1, y - 1, 1).getType())
            // f6 = isMountain(Fields.get(x - 1, y + 1, 1).getType())

            if (isTopLeft(x, y, 'm')) {
                tmpTextureContext.fillStyle = color3

                for (var i = 0; i < textureMultiplier; i++) {
                    for (var j = 0; j < textureMultiplier; j++) {
                        if (i + j < half) {
                            tmpTextureContext.fillRect(newX + i, newY + j, 1, 1)
                        }
                    }
                }
            }

            if (ifBottomRight(x, y, 'm')) {
                tmpTextureContext.fillStyle = color3

                for (var i = 0; i < textureMultiplier; i++) {
                    for (var j = 0; j < textureMultiplier; j++) {
                        if (i + j > halfPlus) {
                            tmpTextureContext.fillRect(newX + i, newY + j, 1, 1)
                        }
                    }
                }
            }
        },
        paintHill = function (tmpTextureContext, x, y) {
            var x = x * 1,
                y = y * 1,
                color1 = '#0b7e22',
                color2 = '#3c963c',
                color3 = '#3c963c',
                f0, f1, f2, f3, f4, f5, f6, f7,
                newX = x * textureMultiplier,
                newY = y * textureMultiplier,
                half = textureMultiplier / 2,
                halfPlus = textureMultiplier + half

            paintTextureField(tmpTextureContext, x, y, color1, color2, 1)

            // f2 = isMountain(Fields.get(x + 1, y - 1, 1).getType())
            // f6 = isMountain(Fields.get(x - 1, y + 1, 1).getType())

            if (isTopLeft(x, y, 'h')) {
                tmpTextureContext.fillStyle = color3

                for (var i = 0; i < textureMultiplier; i++) {
                    for (var j = 0; j < textureMultiplier; j++) {
                        if (i + j < half) {
                            tmpTextureContext.fillRect(newX + i, newY + j, 1, 1)
                        }
                    }
                }
            }

            if (ifBottomRight(x, y, 'h')) {
                tmpTextureContext.fillStyle = color3

                for (var i = 0; i < textureMultiplier; i++) {
                    for (var j = 0; j < textureMultiplier; j++) {
                        if (i + j > halfPlus) {
                            tmpTextureContext.fillRect(newX + i, newY + j, 1, 1)
                        }
                    }
                }
            }
        },
        isTopLeft = function (x, y, type) {
            var f0 = isType(Fields.get(x - 1, y - 1, 1).getType(), type),
                f1 = isType(Fields.get(x, y - 1, 1).getType(), type),
                f7 = isType(Fields.get(x - 1, y, 1).getType(), type)

            if (!f7 && !f0 && !f1) {
                return 1
            } else {
                return 0
            }
        },
        ifBottomRight = function (x, y, type) {
            var f3 = isType(Fields.get(x + 1, y, 1).getType(), type),
                f4 = isType(Fields.get(x + 1, y + 1, 1).getType(), type),
                f5 = isType(Fields.get(x, y + 1, 1).getType(), type)

            if (!f3 && !f4 && !f5) {
                return 1
            } else {
                return 0
            }
        },
        isType = function (type1, type2) {
            if (type1 == type2) {
                return 1
            } else {
                return 0
            }
        },
        paintRoad = function (tmpTextureContext, x, y) {
            var x = x * 1,
                y = y * 1,
                f1, f2, f3, f4,
                marginSmall = textureMultiplier / 8,
                marginBig = textureMultiplier / 2 + 1,
                width = textureMultiplier - (marginSmall + marginBig)

            f1 = isRoad(Fields.get(x, y - 1, 1).getType())
            f2 = isRoad(Fields.get(x + 1, y, 1).getType())
            f3 = isRoad(Fields.get(x, y + 1, 1).getType())
            f4 = isRoad(Fields.get(x - 1, y, 1).getType())

            tmpTextureContext.fillStyle = '#000000'

            // center square
            tmpTextureContext.fillRect(x * textureMultiplier + marginSmall, y * textureMultiplier + marginBig, width, width)

            // top rect BIG
            if (f1) {
                tmpTextureContext.fillRect(x * textureMultiplier + marginSmall, y * textureMultiplier, width, marginBig)
            }

            // right rect BIG
            if (f2) {
                tmpTextureContext.fillRect(x * textureMultiplier + marginSmall + width, y * textureMultiplier + marginBig, marginBig, width)
            }

            // bottom rect SMALL
            if (f3) {
                tmpTextureContext.fillRect(x * textureMultiplier + marginSmall, y * textureMultiplier + marginBig + width, width, marginSmall)
            }

            // left rect SMALL
            if (f4) {
                tmpTextureContext.fillRect(x * textureMultiplier, y * textureMultiplier + marginBig, marginSmall, width)
            }
        },
        isRoad = function (type) {
            if (type == 'r' || type == 'b') {
                return 1
            } else {
                return 0
            }
        },
        paintTextureField = function (tmpTextureContext, x, y, color1, color2, percent) {
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
        paintWaterTextureField = function (tmpWaterTextureContext, x, y, color1, color2, percent) {
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
                if (Me.sameTeam(field.getCastleColor())) {
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
                    if (Me.sameTeam(armies[armyId])) {
                        if (Me.colorEquals(armies[armyId]) && field.getType() == 'w' && Me.getArmy(armyId).canSwim()) {
                            return 'S'
                        } else {
                            return field.getType()
                        }
                    } else {
                        if (destX == x && destY == y) {
                            if (field.getType() == 'w') {
                                if (Me.getSelectedArmy().canSwim() || Me.getSelectedArmy().canFly()) {
                                    return 'E'
                                } else {
                                    return 'e'
                                }
                            } else if (field.getType() == 'm') {
                                if (Me.getSelectedArmy().canFly()) {
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
        textureCanvas = document.createElement('canvas')
        waterTextureCanvas = document.createElement('canvas')

        var tmpTextureCanvas = document.createElement('canvas'),
            tmpTextureContext = tmpTextureCanvas.getContext('2d'),
            tmpWaterTextureCanvas = document.createElement('canvas'),
            tmpWaterTextureContext = tmpWaterTextureCanvas.getContext('2d'),
            waterTextureContext = waterTextureCanvas.getContext('2d'),
            textureContext = textureCanvas.getContext('2d')

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

        for (var y in fields) {
            for (var x in fields[y]) {
                switch (this.get(x, y).getType()) {
                    case 'g':
                        paintTextureField(tmpTextureContext, x, y, '#3c963c', '#3fa342', 5)
                        paintWaterTextureField(tmpWaterTextureContext, x, y, '#ffff7f', '#3c963c', 15)
                        break
                    case 'f':
                        paintTextureField(tmpTextureContext, x, y, '#3c963c', '#0b7e22', 5)
                        paintWaterTextureField(tmpWaterTextureContext, x, y, '#ffff7f', '#3c963c', 15)
                        GameModels.addTree(x, y)
                        break
                    case 'w':
                        // paintTextureField(tmpTextureContext, x, y, '#3c963c', '#ffff7f', 10)
                        paintTextureField(tmpTextureContext, x, y, '#ffff7f', '#3c963c', 15)
                        paintWaterTextureField(tmpWaterTextureContext, x, y, '#365294', '#526daa', 1)
                        break
                    case 'h':
                        paintHill(tmpTextureContext, x, y)
                        paintWaterTextureField(tmpWaterTextureContext, x, y, '#ffff7f', '#3c963c', 15)
                        break
                    case 'm':
                        paintMountain(tmpTextureContext, x, y)
                        paintWaterTextureField(tmpWaterTextureContext, x, y, '#ffff7f', '#3c963c', 15)
                        break
                    case 'r':
                        paintTextureField(tmpTextureContext, x, y, '#3c963c', '#3c963c', 0)
                        paintWaterTextureField(tmpWaterTextureContext, x, y, '#ffff7f', '#3c963c', 15)
                        paintRoad(tmpTextureContext, x, y)
                        break
                    case 'b':
                        paintTextureField(tmpTextureContext, x, y, '#ffff7f', '#3c963c', 15)
                        paintWaterTextureField(tmpWaterTextureContext, x, y, '#365294', '#526daa', 1)
                        paintRoad(tmpTextureContext, x, y)
                        break
                    case 's':
                        paintTextureField(tmpTextureContext, x, y, '#3c963c', '#828396', 60)
                        paintWaterTextureField(tmpWaterTextureContext, x, y, '#ffff7f', '#3c963c', 15)
                        break
                }
            }
        }

        textureContext.drawImage(tmpTextureCanvas, 0, 0, maxX * textureMultiplier, maxY * textureMultiplier, 0, 0, maxX * textureMultiplier, maxY * textureMultiplier)
        waterTextureContext.drawImage(tmpWaterTextureCanvas, 0, 0, maxX * textureMultiplier, maxY * textureMultiplier, 0, 0, maxX * textureMultiplier, maxY * textureMultiplier)
    }
    this.init = function (f) {
        grassField = new Field('g')
        fields = []

        for (var y in f) {
            for (var x in f[y]) {
                this.add(x, y, f[y][x])
            }
        }

        maxX = fields[0].length
        maxY = fields.length

        this.createTextures()
        Ground.init(maxX, maxY, textureCanvas, waterTextureCanvas)
    }
}
