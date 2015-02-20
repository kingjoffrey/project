// *** A* ***

var AStar = new function () {
    var x = 0,
        y = 0,
        open = {},
        close = {},
        field,
        army,
        soldierSplitKey,
        heroSplitKey,
        myCastleId = {}

    this.getX = function () {
        return x
    }
    this.getY = function () {
        return y
    }
    this.cursorPosition = function (point) {
        var X = parseInt((point.x + 218) / 4),
            Y = parseInt((point.z + 312) / 4)

        if (x == X && y == Y) {
            return
        }
        x = X
        y = Y
        field = Fields.get(x, y)
        if (field.getCastleId()) {
            coord.html(Players.get(field.getCastleColor()).getCastles().get(field.getCastleId()).getName())
        } else {
            coord.html(Terrain.getName(field.getType()))
        }
    }
    this.showPath = function (a) {
        army = a
        if (getG(field.getType()) > 6) {
            return
        }
return
        //$('.path').remove();
        var startX = army.getX(),
            startY = army.getY(),
            key = x + '_' + y

        open[startX + '_' + startY] = new node(startX, startY, x, y, 0);

        aStar(x, y, 1)

        if (notSet(close[key])) {
            return
        }

        soldierSplitKey = army.getSoldierSplitKey()
        heroSplitKey = army.getHeroSplitKey()

        var path = getPath(close, key);
        var className = 'path1';
        var moves = 0;

        if (soldierSplitKey) {
            moves = army.getSoldiers()[soldierSplitKey].movesLeft
        } else if (heroSplitKey) {
            moves = army.getHeroes()[heroSplitKey].movesLeft
        } else {
            moves = army.getMoves()
        }

        for (var i in path) {
            var pathX = path[i].x * 40;
            var pathY = path[i].y * 40;

            if (moves < path[i].G) {
                if (className == 'path1') {
                    className = 'path2';
                }
//                if (notSet(set)) {
//                    var set = {'x': pathX, 'y': pathY};
//                }
            }

            //board.append(
            //    $('<div>')
            //        .addClass('path ' + className)
            //        .css({
            //            left: pathX + 'px',
            //            top: pathY + 'px'
            //        })
            //        .html(path[i].G)
            //);
        }

        return path;
//        var set = this.walking(path);
//
//        if (notSet(set)) {
//            return;
//        } else {
//            AStar.x = set.x;
//            AStar.y = set.y;
//        }
    }

    var getPath = function (close, key) {
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
                this.myCastleId[castleId] = true;
            }
        }

        for (var k in path) {
            if (path[k].tt == 'c') {
                var castleId = Castle.getMy(path[k].x, path[k].y);
                if (this.myCastleId[castleId]) {
                    i++;
                } else {
                    this.myCastleId[castleId] = true;
                }
            }
            path[k].F -= i;
            path[k].G -= i;
        }

        return path;
    }
    var addOpen = function (x, y, destX, destY) {
        var startX = x - 1,
            startY = y - 1,
            endX = x + 1,
            endY = y + 1,
            terrainType,
            g

        for (var i = startX; i <= endX; i++) {
            for (var j = startY; j <= endY; j++) {

                if (x == i && y == j) {
                    continue;
                }

                var key = i + '_' + j;

                //if (isSet(close[key]) && close[key].x == i && close[key].y == j) {
                if (isSet(close[key])) {
                    continue;
                }

                terrainType = Fields.get(i, j).getType()

                if (terrainType == 'e') {
                    continue;
                }

                g = getG(terrainType);

                if (g > 6) {
                    continue;
                }

                if (isSet(open[key])) {
                    calculatePath(x + '_' + y, g, key);
                } else {
                    var parent = {
                        'x': x,
                        'y': y
                    };
                    g += close[x + '_' + y].G;
                    open[key] = new node(i, j, destX, destY, g, parent, terrainType);
                }
            }
        }
    }

    var getG = function (terrainType) {
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
    }

    var isNotEmpty = function () {
        for (var key in open) {
            if (open.hasOwnProperty(key)) {
                return true;
            }
        }
        return false;
    }

    var findSmallestF = function () {
        var f

        for (var i in open) {
            if (notSet(open[f])) {
                f = i;
            }
            if (open[i].F < open[f].F) {
                f = i;
            }
        }
        return f;
    }

    var calculatePath = function (kA, g, key) {
        if (open[key].G > (g + close[kA].G)) {
            open[key].parent = {
                'x': close[kA].x,
                'y': close[kA].y
            };
            open[key].G = g + close[kA].G;
            open[key].F = open[key].G + open[key].H;
        }
    }

    var aStar = function (destX, destY, nr) {
        nr++
        if (nr > 7000) {
            nr--;
//            console.log('>' + nr);
            return;
        }
        var f = findSmallestF(),
            x = open[f].x,
            y = open[f].y

        close[f] = open[f];
        if (x == destX && y == destY) {
            return
        }
        delete open[f];
        addOpen(x, y, destX, destY);
        if (!isNotEmpty(open)) {
            return;
        }
        aStar(destX, destY, nr);
    }
}

function node(x, y, destX, destY, g, parent, tt) {
    var calculateH = function (destX, destY) {
        return Math.sqrt(Math.pow(destX - this.x, 2) + Math.pow(this.y - destY, 2))
    }
    this.x = x;
    this.y = y;
    this.G = g;
    this.H = calculateH(destX, destY);
    this.F = this.H + this.G;
    this.parent = parent;
    this.tt = tt;
}
