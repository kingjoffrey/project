// *** A* ***

var AStar = new function () {
    var destX,
        destY,
        open,
        close,
        nr,
        field,
        army,
        soldierSplitKey,
        heroSplitKey,
        myCastleId = {}

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
        if (field.getCastleId()) {
            coord.html(Players.get(field.getCastleColor()).getCastles().get(field.getCastleId()).getName() + ' ' + destX + 'x' + destY)
        } else if (field.getTowerId()) {
            coord.html(translations.tower + ' ' + destX + 'x' + destY)
        } else if (field.getRuinId()) {
            coord.html(translations.ruin + ' ' + destX + 'x' + destY)
        } else if (field.hasArmies()) {
            coord.html(translations.army + ' ' + destX + 'x' + destY)
        } else {
            coord.html(Terrain.getName(field.getType()) + ' ' + destX + 'x' + destY)
        }
        return 1
    }
    this.showPath = function () {
        army = Me.getSelectedArmy()
        if (getG(field.getType()) > 6 && field.getType() != 'e') {
            return
        }
        open = {}
        close = {}
        nr = 0
        Three.clearPathCircles()
        var startX = army.getX(),
            startY = army.getY(),
            key = destX + '_' + destY

        open[startX + '_' + startY] = new node(startX, startY, destX, destY, 0)
        aStar()

        if (notSet(close[key])) {
            return
        }

        soldierSplitKey = army.getSoldierSplitKey()
        heroSplitKey = army.getHeroSplitKey()

        var path = getPath(key)
        //className = 'path1',
        //moves = 0

        //if (soldierSplitKey) {
        //    moves = army.getSoldiers()[soldierSplitKey].movesLeft
        //} else if (heroSplitKey) {
        //    moves = army.getHeroes()[heroSplitKey].movesLeft
        //} else {
        //    moves = army.getMoves()
        //}

        for (var i in path) {
            Three.addPathCircle(path[i].x, path[i].y)
            //var pathX = path[i].x;
            //var pathY = path[i].y;

            //if (moves < path[i].G) {
            //    if (className == 'path1') {
            //        className = 'path2';
            //    }
            //}
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
                var castleId = Castle.getMy(path[0].x, path[0].y)
                myCastleId[castleId] = true;
            }
        }

        for (var k in path) {
            if (path[k].tt == 'c') {
                var castleId = Castle.getMy(path[k].x, path[k].y);
                if (myCastleId[castleId]) {
                    i++;
                } else {
                    myCastleId[castleId] = true;
                }
            }
            path[k].F -= i;
            path[k].G -= i;
        }

        return path;
    }
    var addOpen = function (currX, currY) {
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

                    terrainType = Fields.getAStarType(i, j)
                    if (!terrainType) {
                        continue
                    }
                    if (terrainType == 'e') {
                        if (i == destX && j == destY) {
                            terrainType = 'g'
                        } else {
                            continue
                        }
                    }

                    g = getG(terrainType)
                    if (g > 6) {
                        continue
                    }

                    if (isSet(open[key])) {
                        calculatePath(currX + '_' + currY, g, key);
                    } else {
                        var parent = {
                            x: currX,
                            y: currY
                        };
                        g += close[currX + '_' + currY].G;
                        open[key] = new node(i, j, destX, destY, g, parent, terrainType)
                    }
                }
            }
        },
        getG = function (terrainType) {
            var soldierSplitKey = army.getSoldierSplitKey()
            if (soldierSplitKey) {
                if (Units.get(army.getSoldiers[soldierSplitKey].unitId).canFly) {
                    return Terrain.get(terrainType).flying
                } else if (Units.get(army.getSoldiers[soldierSplitKey].unitId).canSwim) {
                    return Terrain.get(terrainType).swimming
                } else {
                    if (terrainType == 'f' || terrainType == 's' || terrainType == 'm') {
                        return Units.get(army.getSoldiers[soldierSplitKey].unitId)[terrainType]
                    } else {
                        return Terrain.get(terrainType).walking
                    }
                }
            } else {
                return Terrain.get(terrainType).walking
            }
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
