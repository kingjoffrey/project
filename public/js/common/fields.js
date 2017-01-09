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
        setPixel = function (ctx, x, y, color) {
            ctx.fillStyle = color;
            ctx.fillRect(x, y, 1, 1);
        }

    this.init = function (fields, mapId) {
        var tmpCanvas = document.createElement('canvas'),
            ctx = tmpCanvas.getContext('2d'),
            canvas = document.createElement('canvas'),
            context = canvas.getContext('2d'),
            maxWidth = parseInt($('#mapBox').css('width'))

        for (var y in fields) {
            for (var x in fields[y]) {
                switch (fields[y][x].type) {
                    case 'g':
                        setPixel(ctx, x, y, '#009900')
                        break
                    case 'f':
                        setPixel(ctx, x, y, '#004e00')
                        GameModels.addTree(x, y)
                        break
                    case 'w':
                        setPixel(ctx, x, y, '#0000cd')
                        break
                    case 'h':
                        setPixel(ctx, x, y, '#505200')
                        break
                    case 'm':
                        setPixel(ctx, x, y, '#262728')
                        break
                    case 'r':
                        setPixel(ctx, x, y, '#c1c1c1')
                        break
                    case 'b':
                        setPixel(ctx, x, y, '#c1c1c1')
                        break
                    case 's':
                        setPixel(ctx, x, y, '#39723E')
                        GameModels.addSwamp(x, y)
                        break
                }
                this.add(x, y, fields[y][x])
            }
        }
        maxX = x
        maxY = y
        x++
        y++
        var ratio = maxWidth / x
        width = x * ratio
        height = y * ratio

        canvas.width = width
        canvas.height = height

        context.drawImage(tmpCanvas, 0, 0, x, y, 0, 0, width, height)

        $('#map').append(canvas).css({
            width: width,
            height: height
        })

        Ground.init(maxX, maxY, '/img/maps/' + mapId + '.png')
        initRoads()
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
    this.createTexture = function () {
        var canvas = document.createElement('canvas'),
            context = canvas.getContext('2d'),
            makeField = function (x, y, color) {
                context.fillStyle = color;
                context.fillRect(x * 3, y * 3, 3, 3);
            }
        for (var y in fields) {
            for (var x in fields[y]) {
                switch (fields[y][x].type) {
                    case 'g':
                        makeField(x, y, '#009900')
                        break
                    case 'f':
                        makeField(x, y, '#004e00')
                        break
                    case 'w':
                        makeField(x, y, '#009900')
                        break
                    case 'h':
                        makeField(x, y, '#505200')
                        break
                    case 'm':
                        makeField(x, y, '#262728')
                        break
                    case 'r':
                        makeField(x, y, '#009900')
                        break
                    case 'b':
                        makeField(x, y, '#009900')
                        break
                    case 's':
                        makeField(x, y, '#39723E')
                        break
                }
            }
        }
        context.drawImage(canvas, 0, 0)
        // $('#map canvas').remove()
        $('#map').append(canvas)
        return {'canvas': canvas, 'context': context}
    }
}