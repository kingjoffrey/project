var AStar = new function () {
    var destX,
        destY,
        open,
        close,
        nr,
        field,
        army,
        myCastleId = {},
        movementType,
        element,
        showCoordinates = 0,
        coordinates = ''

    this.init = function () {
        element = $('#terrain')
    }
    this.getX = function () {
        return destX
    }
    this.getY = function () {
        return destY
    }
    this.cursorPosition = function (x, y) {
        if (destX == x && destY == y) {
            return
        }
        destX = x
        destY = y
        field = Fields.get(destX, destY)
        
        if (showCoordinates) {            
            coordinates = ' ' + destX + 'x' + destY
        }
        
        if (field.getCastleId()) {
            element.html(Players.get(field.getCastleColor()).getCastles().get(field.getCastleId()).getName() + coordinates)
        } else if (field.getTowerId()) {
            element.html(translations.tower + coordinates)
        } else if (field.getRuinId()) {
            element.html(translations.ruin + coordinates)
        } else if (field.hasArmies()) {
            element.html(translations.army + coordinates)
        } else {
            element.html(Terrain.getName(field.getType()) + coordinates)
        }
        return 1
    }
    this.showPath = function () {
        army = CommonMe.getSelectedArmy()
        movementType = army.getMovementType()
        if (getG(field.getType()) > 6 && !field.hasArmies() && !field.getCastleId()) {
            return
        }
        open = {}
        close = {}
        nr = 0
        GameModels.clearPathCircles()
        var startX = army.getX(),
            startY = army.getY(),
            key = destX + '_' + destY

        open[startX + '_' + startY] = new node(startX, startY, destX, destY, 0)
        aStar()

        if (notSet(close[key])) {
            return
        }

        army.resetPathMoves()

        var path = getPath(key),
            movesEnd = false

        for (var i in path) {
            if (army.pathStep(path[i].tt, movementType)) {
                movesEnd = true
            }

            if (movesEnd) {
                GameModels.addPathCircle(path[i].x, path[i].y, 'white', path[i].tt)
            } else {
                GameModels.addPathCircle(path[i].x, path[i].y, 'green', path[i].tt)
            }
        }
    }

    var getPath = function (key) {
            var path = [],
                i = 0

            while (isSet(close[key].parent)) {
                path[path.length] = close[key];
                key = close[key].parent.x + '_' + close[key].parent.y;
            }
            path = path.reverse();

            if (isSet(path[0])) {
                if (path[0].tt == 'c') {
                    var castleId = Fields.get(path[0].x, path[0].y).getCastleId()
                    if (CommonMe.colorEquals(Fields.get(path[0].x, path[0].y).getCastleColor())) {
                        myCastleId[castleId] = true
                    }
                }
            }

            for (var k in path) {
                if (path[k].tt == 'c') {
                    var castleId = Fields.get(path[0].x, path[0].y).getCastleId()
                    if (CommonMe.colorEquals(Fields.get(path[0].x, path[0].y).getCastleColor())) {
                        if (isSet(myCastleId[castleId])) {
                            i++
                        } else {
                            myCastleId[castleId] = true
                        }
                    }
                }
                path[k].F -= i;
                path[k].G -= i;
            }

            return path;
        },
        addOpen = function (currX, currY) {
            var startX = currX - 1,
                startY = currY - 1,
                endX = currX + 1,
                endY = currY + 1,
                terrainType,
                g

            for (var i = startX; i <= endX; i++) {
                for (var j = startY; j <= endY; j++) {
                    if (currX == i && currY == j) {
                        continue
                    }
                    var key = i + '_' + j;
                    if (isSet(close[key])) {
                        continue
                    }

                    terrainType = Fields.getAStarType(i, j, destX, destY)
                    if (!terrainType) {
                        continue
                    }
                    if (terrainType == 'e') {
                        continue
                    }

                    g = getG(terrainType)
                    if (g > 6) {
                        continue
                    }

                    if (isSet(open[key])) {
                        calculatePath(currX + '_' + currY, g, key);
                    } else {
                        g += close[currX + '_' + currY].G;
                        open[key] = new node(i, j, destX, destY, g, {x: currX, y: currY}, terrainType)
                    }
                }
            }
        },
        getG = function (terrainType) {
            return Terrain.get(terrainType)[movementType]
        },
        openIsEmpty = function () {
            for (var key in open) {
                if (open.hasOwnProperty(key)) {
                    return false
                }
            }
            return true
        },
        findSmallestF = function () {
            var f

            for (var i in open) {
                if (notSet(open[f])) {
                    f = i
                }
                if (open[i].F < open[f].F) {
                    f = i
                }
            }
            return f
        },
        calculatePath = function (kA, g, key) {
            if (open[key].G > (g + close[kA].G)) {
                open[key].parent = {
                    x: close[kA].x,
                    y: close[kA].y
                };
                open[key].G = g + close[kA].G;
                open[key].F = open[key].G + open[key].H;
            }
        },
        aStar = function () {
            nr++
            if (nr > 7000) {
                console.log('>' + nr);
                return
            }
            var f = findSmallestF(),
                currX = open[f].x,
                currY = open[f].y

            close[f] = open[f]
            if (currX == destX && currY == destY) {
                return
            }
            delete open[f]
            addOpen(currX, currY)
            if (openIsEmpty()) {
                return;
            }
            aStar()
        }
}

function node(x, y, destX, destY, g, parent, tt) {
    this.x = x
    this.y = y
    this.G = g
    this.H = Math.sqrt(Math.pow(destX - x, 2) + Math.pow(y - destY, 2))
    this.F = this.H + this.G
    this.parent = parent
    this.tt = tt
}
