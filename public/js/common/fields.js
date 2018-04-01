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
                        var h = 0,
                            color

                        if (x % 2) {
                            if (y % 2) {
                                color = 0x3c963c
                            } else {
                                color = 0x3fa342
                            }
                        } else {
                            if (y % 2) {
                                color = 0x3fa342
                            } else {
                                color = 0x3c963c
                            }
                        }

                        for (var jj = y * 1 - 1; jj <= y * 1 + 1; jj++) {
                            for (var ii = x * 1 - 1; ii <= x * 1 + 1; ii++) {
                                var f = this.get(ii, jj, 1)
                                if (f.getType() == 'm' || (f.getType() == 'h' && f.getLevel() == 9)) {
                                    h++
                                }
                            }
                        }
                        // console.log('h=' + h)
                        var level = this.get(x, y).getLevel()
                        if (h == 9) {
                            level++
                            this.get(x, y).setLevel(level)
                        }

                        switch (level) {
                            case 1:
                                var radius = 0.6,
                                    height = 0.3,
                                    segments = 5
                                break;
                            case 2:
                                var radius = 5.5,
                                    height = 0.35,
                                    segments = 5
                                break;
                            case 3:
                                var radius = 0.6,
                                    height = 0.4,
                                    segments = 5
                                break;
                            case 4:
                                var radius = 0.65,
                                    height = 0.45,
                                    segments = 5
                                break;
                            case 5:
                                var radius = 0.7,
                                    height = 0.5,
                                    segments = 5
                                break;
                            case 6:
                                var radius = 0.8,
                                    height = 0.55,
                                    segments = 5
                                break;
                            case 7:
                                var radius = 0.9,
                                    height = 0.6,
                                    segments = 5
                                break;
                            case 8:
                                var radius = 1,
                                    height = 0.65,
                                    segments = 5
                                break;
                            case 9:
                                var radius = 2,
                                    height = 0.7,
                                    segments = 5
                                break;
                            case 10:
                                var radius = 3,
                                    height = 0.75,
                                    segments = 4
                                break;
                            default:
                                console.log('to many h')
                        }
                        var geometry = new THREE.ConeGeometry(radius, height, segments);
                        var material = new THREE.MeshLambertMaterial({
                            color: color,
                            side: THREE.DoubleSide
                        });
                        var cone = new THREE.Mesh(geometry, material);

                        cone.position.set(x * 2 + 1, height / 2, y * 2 + 1)
                        // cone.rotation.y = Math.PI / 4

                        GameScene.add(cone)

                        paintTextureField(tmpTextureContext, x, y, grassColor1, grassColor2)
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
    // this.paint = function (x, y, context) {
    //     switch (this.get(x, y).getType()) {
    //         case 'g':
    //             paintTextureField(context, x, y, grassColor1, grassColor2, 5)
    //             break
    //         case 'f':
    //             paintTextureField(context, x, y, grassColor1, grassColor2)
    //             addDots(context, x, y, 0, forestColor1, 5)
    //             break
    //         case 'w':
    //             paintTextureField(context, x, y, waterColor1, waterColor2, 0)
    //             break
    //         case 'b':
    //             paintTextureField(context, x, y, waterColor1, waterColor2, 0)
    //             paintRoad(context, x, y)
    //             break
    //         case 'h':
    //             paintTextureField(context, x, y, grassColor1, grassColor2, 5)
    //             // paintHill(context, x, y)
    //             break
    //         case 'm':
    //             paintTextureField(context, x, y, mountainColor1, mountainColor2)
    //             // paintMountain(context, x, y)
    //             break
    //         case 'r':
    //             paintTextureField(context, x, y, grassColor1, grassColor1)
    //             paintRoad(context, x, y)
    //             break
    //         case 's':
    //             paintTextureField(context, x, y, grassColor1, grassColor2)
    //             addDots(context, x, y, 0, waterColor1, 30)
    //             break
    //     }
    // }
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

        for (var y in f) {
            for (var x in f[y]) {
                var level = 0
                for (var jj = y * 1 - 1; jj <= y * 1 + 1; jj++) {
                    for (var ii = x * 1 - 1; ii <= x * 1 + 1; ii++) {
                        switch (this.get(x, y, 1).getType()) {
                            case 'h':
                                if (this.get(ii, jj, 1).getHill()) {
                                    level++
                                }
                        }
                    }
                }
                this.get(x, y).setLevel(level)
            }
        }
    }
}
