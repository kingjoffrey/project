"use strict"
var Fields = new function () {
    var fields,
        grassField,
        maxX,
        maxY,
        textureMultiplier = 16,
        textureCanvas,
        waterTextureCanvas,
        // hillColor1 = '#0c7521',
        // hillColor1 = '#096b1e',
        // hillColor2 = '#0a6f20',
        hillColor1 = '#484a4d',
        hillColor2 = '#3b3c3f',
        roadColor1 = '#aaaa00',
        mountainColor1 = '#adb1b8',
        mountainColor2 = '#c0c5ce',
        // mountainColor1 = '#dbd9ee',
        // mountainColor2 = '#d1d5f3',
        // mountainColor2 = '#555555',
        // mountainColor3 = '#ffffff',
        grassColor1 = '#3c963c',
        grassColor2 = '#3fa342',
        forestColor1 = '#0b7e22',
        waterColor1 = '#00557f',
        waterColor2 = '#526daa',
        paintMountain = function (tmpTextureContext, x, y) {
            var x = x * 1,
                y = y * 1,
                newX = x * textureMultiplier,
                newY = y * textureMultiplier,
                half = textureMultiplier / 2,
                halfPlus = textureMultiplier + half,
                m1, m2, m3, m4

            // m1 = notType(Fields.get(x, y - 1, 1).getType(), 'm')
            // m2 = notType(Fields.get(x + 1, y, 1).getType(), 'm')
            m3 = notType(Fields.get(x, y + 1, 1).getType(), 'm')
            m4 = notType(Fields.get(x - 1, y, 1).getType(), 'm')

            // if (m1) {
            //     tmpTextureContext.fillStyle = mountainColor2
            //     tmpTextureContext.fillRect(newX, newY, textureMultiplier, 2)
            // }
            // if (m3) {
            // tmpTextureContext.fillStyle = mountainColor2
            // tmpTextureContext.fillRect(newX + 1, newY + textureMultiplier - 2, textureMultiplier - 2, 1)
            // tmpTextureContext.fillRect(newX + 2, newY + textureMultiplier - 3, textureMultiplier - 4, 1)
            // tmpTextureContext.fillRect(newX + 3, newY + textureMultiplier - 4, textureMultiplier - 6, 1)
            // tmpTextureContext.fillRect(newX + 4, newY + textureMultiplier - 5, textureMultiplier - 8, 1)
            // tmpTextureContext.fillStyle = '#000000'
            // tmpTextureContext.fillRect(newX, newY + textureMultiplier - 1, textureMultiplier, 1)
            // }
            // if (m4) {
            // tmpTextureContext.fillStyle = mountainColor2
            // tmpTextureContext.fillRect(newX + 1, newY + 1, 1, textureMultiplier - 2)
            // tmpTextureContext.fillRect(newX + 2, newY + 2, 1, textureMultiplier - 4)
            // tmpTextureContext.fillRect(newX + 3, newY + 3, 1, textureMultiplier - 6)
            // tmpTextureContext.fillRect(newX + 4, newY + 4, 1, textureMultiplier - 8)
            // tmpTextureContext.fillStyle = '#000000'
            // tmpTextureContext.fillRect(newX, newY, 1, textureMultiplier)
            // }

            // if (notType(Fields.get(x, y + 1, 1).getType(), 'm')) {
            //     tmpTextureContext.fillStyle = '#000000'
            // if (Math.random() > 0.5) {
            //     if (Math.random() > 0.5) {
            //         var dot = 2
            //     } else {
            //         var dot = 1
            //     }
            //     tmpTextureContext.fillRect(newX, newY + textureMultiplier - 1, 1, dot)
            // }
            // if (Math.random() > 0.5) {
            //     if (Math.random() > 0.5) {
            //         var dot = 2
            //     } else {
            //         var dot = 1
            //     }
            //     tmpTextureContext.fillRect(newX + 1, newY + textureMultiplier - 2, 1, dot)
            // }
            // if (Math.random() > 0.5) {
            //     if (Math.random() > 0.5) {
            //         var dot = 2
            //     } else {
            //         var dot = 1
            //     }
            //     tmpTextureContext.fillRect(newX + 2, newY + textureMultiplier - 1, 1, dot)
            // }
            // if (Math.random() > 0.5) {
            //     if (Math.random() > 0.5) {
            //         var dot = 2
            //     } else {
            //         var dot = 1
            //     }
            //     tmpTextureContext.fillRect(newX + 3, newY + textureMultiplier - 2, 1, dot)
            // }
            // if (Math.random() > 0.5) {
            //     if (Math.random() > 0.5) {
            //         var dot = 2
            //     } else {
            //         var dot = 1
            //     }
            //     tmpTextureContext.fillRect(newX + 4, newY + textureMultiplier - 1, 1, dot)
            // }
            // if (Math.random() > 0.5) {
            //     if (Math.random() > 0.5) {
            //         var dot = 2
            //     } else {
            //         var dot = 1
            //     }
            //     tmpTextureContext.fillRect(newX + 5, newY + textureMultiplier - 2, 1, dot)
            // }
            // if (Math.random() > 0.5) {
            //     if (Math.random() > 0.5) {
            //         var dot = 2
            //     } else {
            //         var dot = 1
            //     }
            //     tmpTextureContext.fillRect(newX + 6, newY + textureMultiplier - 1, 1, dot)
            // }
            // if (Math.random() > 0.5) {
            //     if (Math.random() > 0.5) {
            //         var dot = 2
            //     } else {
            //         var dot = 1
            //     }
            //     tmpTextureContext.fillRect(newX + 7, newY + textureMultiplier - 2, 1, dot)
            // }
            // if (Math.random() > 0.5) {
            //     if (Math.random() > 0.5) {
            //         var dot = 2
            //     } else {
            //         var dot = 1
            //     }
            //     tmpTextureContext.fillRect(newX + 8, newY + textureMultiplier - 1, 1, dot)
            // }
            // if (Math.random() > 0.5) {
            //     if (Math.random() > 0.5) {
            //         var dot = 2
            //     } else {
            //         var dot = 1
            //     }
            //     tmpTextureContext.fillRect(newX + 9, newY + textureMultiplier - 2, 1, dot)
            // }
            // if (Math.random() > 0.5) {
            //     if (Math.random() > 0.5) {
            //         var dot = 2
            //     } else {
            //         var dot = 1
            //     }
            //     tmpTextureContext.fillRect(newX + 10, newY + textureMultiplier - 1, 1, dot)
            // }
            // if (Math.random() > 0.5) {
            //     if (Math.random() > 0.5) {
            //         var dot = 2
            //     } else {
            //         var dot = 1
            //     }
            //     tmpTextureContext.fillRect(newX + 11, newY + textureMultiplier - 2, 1, dot)
            // }
            // if (Math.random() > 0.5) {
            //     if (Math.random() > 0.5) {
            //         var dot = 2
            //     } else {
            //         var dot = 1
            //     }
            //     tmpTextureContext.fillRect(newX + 12, newY + textureMultiplier - 1, 1, dot)
            // }
            // if (Math.random() > 0.5) {
            //     if (Math.random() > 0.5) {
            //         var dot = 2
            //     } else {
            //         var dot = 1
            //     }
            //     tmpTextureContext.fillRect(newX + 13, newY + textureMultiplier - 2, 1, dot)
            // }
            // if (Math.random() > 0.5) {
            //     if (Math.random() > 0.5) {
            //         var dot = 2
            //     } else {
            //         var dot = 1
            //     }
            //     tmpTextureContext.fillRect(newX + 14, newY + textureMultiplier - 1, 1, dot)
            // }
            // if (Math.random() > 0.5) {
            //     if (Math.random() > 0.5) {
            //         var dot = 2
            //     } else {
            //         var dot = 1
            //     }
            //     tmpTextureContext.fillRect(newX + 15, newY + textureMultiplier - 2, 1, 2)
            // }
            // }

            // if (notType(Fields.get(x - 1, y, 1).getType(), 'm')) {
            //     tmpTextureContext.fillStyle = hillColor2
            //     tmpTextureContext.fillRect(newX, newY, 1, 1)
            //     tmpTextureContext.fillRect(newX, newY + 1, 2, 1)
            //     tmpTextureContext.fillRect(newX, newY + 2, 1, 1)
            //     tmpTextureContext.fillRect(newX, newY + 3, 2, 1)
            //     tmpTextureContext.fillRect(newX, newY + 4, 1, 1)
            //     tmpTextureContext.fillRect(newX, newY + 5, 2, 1)
            //     tmpTextureContext.fillRect(newX, newY + 6, 1, 1)
            //     tmpTextureContext.fillRect(newX, newY + 7, 2, 1)
            //     tmpTextureContext.fillRect(newX, newY + 8, 1, 1)
            //     tmpTextureContext.fillRect(newX, newY + 9, 2, 1)
            //     tmpTextureContext.fillRect(newX, newY + 10, 1, 1)
            //     tmpTextureContext.fillRect(newX, newY + 11, 2, 1)
            //     tmpTextureContext.fillRect(newX, newY + 12, 1, 1)
            //     tmpTextureContext.fillRect(newX, newY + 13, 2, 1)
            //     tmpTextureContext.fillRect(newX, newY + 14, 1, 1)
            //     tmpTextureContext.fillRect(newX, newY + 15, 2, 1)
            // }
        },
        paintHill = function (tmpTextureContext, x, y) {
            var x = x * 1,
                y = y * 1,
                newX = x * textureMultiplier,
                newY = y * textureMultiplier,
                half = textureMultiplier / 2,
                halfPlus = textureMultiplier + half

            // paintTextureField(tmpTextureContext, x, y, hillColor1, grassColor1, 1)

            // if (isType(Fields.get(x, y + 1, 1).getType(), 'h')
            //     && isType(Fields.get(x, y - 1, 1).getType(), 'h')
            //     && isType(Fields.get(x + 1, y, 1).getType(), 'h')
            //     && isType(Fields.get(x - 1, y, 1).getType(), 'h')
            //     && isType(Fields.get(x - 1, y - 1, 1).getType(), 'h')
            //     && isType(Fields.get(x - 1, y + 1, 1).getType(), 'h')
            //     && isType(Fields.get(x + 1, y + 1, 1).getType(), 'h')
            //     && isType(Fields.get(x + 1, y - 1, 1).getType(), 'h')
            // ) {
            tmpTextureContext.fillStyle = hillColor2
            tmpTextureContext.fillRect(newX + 5, newY + 8, 6, 1)
            // }

            if (notType(Fields.get(x, y + 1, 1).getType(), 'h') && notType(Fields.get(x, y + 1, 1).getType(), 'm')) {
                tmpTextureContext.fillStyle = hillColor2
                tmpTextureContext.fillRect(newX, newY + textureMultiplier - 1, 1, 2)
                tmpTextureContext.fillRect(newX + 1, newY + textureMultiplier - 2, 1, 2)
                tmpTextureContext.fillRect(newX + 2, newY + textureMultiplier - 1, 1, 2)
                tmpTextureContext.fillRect(newX + 3, newY + textureMultiplier - 2, 1, 2)
                tmpTextureContext.fillRect(newX + 4, newY + textureMultiplier - 1, 1, 2)
                tmpTextureContext.fillRect(newX + 5, newY + textureMultiplier - 2, 1, 2)
                tmpTextureContext.fillRect(newX + 6, newY + textureMultiplier - 1, 1, 2)
                tmpTextureContext.fillRect(newX + 7, newY + textureMultiplier - 2, 1, 2)
                tmpTextureContext.fillRect(newX + 8, newY + textureMultiplier - 1, 1, 2)
                tmpTextureContext.fillRect(newX + 9, newY + textureMultiplier - 2, 1, 2)
                tmpTextureContext.fillRect(newX + 10, newY + textureMultiplier - 1, 1, 2)
                tmpTextureContext.fillRect(newX + 11, newY + textureMultiplier - 2, 1, 2)
                tmpTextureContext.fillRect(newX + 12, newY + textureMultiplier - 1, 1, 2)
                tmpTextureContext.fillRect(newX + 13, newY + textureMultiplier - 2, 1, 2)
                tmpTextureContext.fillRect(newX + 14, newY + textureMultiplier - 1, 1, 2)
                tmpTextureContext.fillRect(newX + 15, newY + textureMultiplier - 2, 1, 2)
            }

            if (notType(Fields.get(x - 1, y, 1).getType(), 'h') && notType(Fields.get(x - 1, y, 1).getType(), 'm')) {
                tmpTextureContext.fillStyle = hillColor2
                tmpTextureContext.fillRect(newX, newY, 1, 1)
                tmpTextureContext.fillRect(newX, newY + 1, 2, 1)
                tmpTextureContext.fillRect(newX, newY + 2, 1, 1)
                tmpTextureContext.fillRect(newX, newY + 3, 2, 1)
                tmpTextureContext.fillRect(newX, newY + 4, 1, 1)
                tmpTextureContext.fillRect(newX, newY + 5, 2, 1)
                tmpTextureContext.fillRect(newX, newY + 6, 1, 1)
                tmpTextureContext.fillRect(newX, newY + 7, 2, 1)
                tmpTextureContext.fillRect(newX, newY + 8, 1, 1)
                tmpTextureContext.fillRect(newX, newY + 9, 2, 1)
                tmpTextureContext.fillRect(newX, newY + 10, 1, 1)
                tmpTextureContext.fillRect(newX, newY + 11, 2, 1)
                tmpTextureContext.fillRect(newX, newY + 12, 1, 1)
                tmpTextureContext.fillRect(newX, newY + 13, 2, 1)
                tmpTextureContext.fillRect(newX, newY + 14, 1, 1)
                tmpTextureContext.fillRect(newX, newY + 15, 2, 1)
            }

        },
        isType = function (type1, type2) {
            if (type1 == type2) {
                return 1
            } else {
                return 0
            }
        },
        notType = function (type1, type2) {
            if (type1 != type2) {
                return 1
            } else {
                return 0
            }
        },
        paintBridge = function (tmpTextureContext, tmpWaterTextureContext, x, y, noModels) {
            var x = x * 1,
                y = y * 1

            paintTextureField(tmpTextureContext, x, y, roadColor1, grassColor1, 0)
            paintWaterTextureField(tmpWaterTextureContext, x, y, waterColor1, waterColor2, 1)

            if (notSet(noModels)) {
                if (Fields.get(x, y - 1, 1).getGrassOrWater() == 'g' || Fields.get(x, y + 1, 1).getGrassOrWater() == 'g') {
                    var rotate = 1
                } else {
                    var rotate = 0
                }
                GameModels.addBridge(x, y, rotate)
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

            tmpTextureContext.fillStyle = roadColor1

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
        addDots = function (tmpTextureContext, x, y, odd, color, percent, size) {
            var howManyTimes = textureMultiplier * textureMultiplier * (percent / 100)

            tmpTextureContext.fillStyle = color

            if (isSet(size)) {
                var s = size
            } else {
                var s = 1
            }

            for (var i = 0; i < howManyTimes; i++) {
                var valueX = Math.floor((Math.random() * textureMultiplier)),
                    valueY = Math.floor((Math.random() * textureMultiplier))

                tmpTextureContext.fillRect(x * textureMultiplier + valueX, y * textureMultiplier + valueY, s, s)
            }
        },
        paintTextureField = function (tmpTextureContext, x, y, color1, color2) {
            var color

            if (x % 2) {
                if (y % 2) {
                    color = color1
                } else {
                    color = color2
                }
            } else {
                if (y % 2) {
                    color = color2
                } else {
                    color = color1
                }
            }

            tmpTextureContext.fillStyle = color
            tmpTextureContext.fillRect(x * textureMultiplier, y * textureMultiplier, textureMultiplier, textureMultiplier)

            if (color == color1) {
                return 1
            } else {
                return 0
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
    this.getTextureCanvas = function () {
        return textureCanvas
    }
    this.getWaterTextureCanvas = function () {
        return waterTextureCanvas
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
    this.createTextures = function (noModels) {
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
                        paintTextureField(tmpTextureContext, x, y, grassColor1, grassColor2, 5)
                        break
                    case 'f':
                        var odd = paintTextureField(tmpTextureContext, x, y, grassColor1, grassColor2)
                        addDots(tmpTextureContext, x, y, odd, forestColor1, 5)
                        if (notSet(noModels)) {
                            GameModels.addTree(x, y)
                        }
                        break
                    case 'w':
                        paintTextureField(tmpTextureContext, x, y, grassColor1, grassColor2)
                        paintWaterTextureField(tmpWaterTextureContext, x, y, '#365294', waterColor2, 1)
                        break
                    case 'b':
                        paintBridge(tmpTextureContext, tmpWaterTextureContext, x, y, noModels)
                        break
                    case 'h':
                        paintTextureField(tmpTextureContext, x, y, hillColor1, hillColor2)
                        // paintHill(tmpTextureContext, x, y, 1)
                        break
                    case 'm':
                        paintTextureField(tmpTextureContext, x, y, mountainColor1, mountainColor2)
                        // paintMountain(tmpTextureContext, x, y)
                        break
                    case 'r':
                        paintTextureField(tmpTextureContext, x, y, grassColor1, grassColor2)
                        paintRoad(tmpTextureContext, x, y)
                        break
                    case 's':
                        var odd = paintTextureField(tmpTextureContext, x, y, grassColor1, grassColor2, 30)
                        addDots(tmpTextureContext, x, y, odd, waterColor1, 30)
                        break
                }
            }
        }

        textureContext.drawImage(tmpTextureCanvas, 0, 0, maxX * textureMultiplier, maxY * textureMultiplier, 0, 0, maxX * textureMultiplier, maxY * textureMultiplier)
        waterTextureContext.drawImage(tmpWaterTextureCanvas, 0, 0, maxX * textureMultiplier, maxY * textureMultiplier, 0, 0, maxX * textureMultiplier, maxY * textureMultiplier)
    }
    this.createEditorTextures = function () {
        textureCanvas = document.createElement('canvas')

        var tmpTextureCanvas = document.createElement('canvas'),
            tmpTextureContext = tmpTextureCanvas.getContext('2d'),
            textureContext = textureCanvas.getContext('2d')

        tmpTextureCanvas.width = maxX * textureMultiplier
        tmpTextureCanvas.height = maxY * textureMultiplier
        textureCanvas.width = maxX * textureMultiplier
        textureCanvas.height = maxY * textureMultiplier

        textureContext.translate(0, maxY * textureMultiplier)
        textureContext.scale(1, -1)

        for (var y in fields) {
            for (var x in fields[y]) {
                this.paint(x, y, tmpTextureContext)
            }
        }

        textureContext.drawImage(tmpTextureCanvas, 0, 0, maxX * textureMultiplier, maxY * textureMultiplier, 0, 0, maxX * textureMultiplier, maxY * textureMultiplier)
    }
    this.paint = function (x, y, context) {
        switch (this.get(x, y).getType()) {
            case 'g':
                paintTextureField(context, x, y, grassColor1, grassColor2, 5)
                break
            case 'f':
                paintTextureField(context, x, y, grassColor1, grassColor2)
                addDots(context, x, y, 0, forestColor1, 5)
                break
            case 'w':
                paintTextureField(context, x, y, waterColor1, waterColor2, 0)
                break
            case 'b':
                paintTextureField(context, x, y, waterColor1, waterColor2, 0)
                paintRoad(context, x, y)
                break
            case 'h':
                paintTextureField(context, x, y, hillColor1, hillColor2)
                // paintHill(context, x, y)
                break
            case 'm':
                paintTextureField(context, x, y, mountainColor1, mountainColor2)
                // paintMountain(context, x, y)
                break
            case 'r':
                paintTextureField(context, x, y, grassColor1, grassColor1)
                paintRoad(context, x, y)
                break
            case 's':
                paintTextureField(context, x, y, grassColor1, grassColor2)
                addDots(context, x, y, 0, waterColor1, 30)
                break
        }
    }
    this.getGrassColor = function () {
        return grassColor1;
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
    }
}
