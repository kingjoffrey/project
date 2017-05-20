var Ground = new function () {
    var mountainLevel = -0.9,
        // var mountainLevel = 0,
        hillLevel = -0.2,
        // hillLevel = 0,
        bottomLevel = 0.3,
        // bottomLevel = 0,
        waterLevel = 0.05,
        m = 2,
        checkMountainUp = function (x, y) {
            if (Fields.get(x, y + 1, 1).getMountain()) {
                return 0
            } else {
                return 1
            }
        },
        checkMountainDown = function (x, y) {
            if (Fields.get(x, y - 1, 1).getMountain()) {
                return 0
            } else {
                return 1
            }
        },
        checkMountainLeft = function (x, y) {
            if (Fields.get(x + 1, y, 1).getMountain()) {
                return 0
            } else {
                return 1
            }
        },
        checkMountainRight = function (x, y) {
            if (Fields.get(x - 1, y, 1).getMountain()) {
                return 0
            } else {
                return 1
            }
        },
        checkHillUp = function (x, y) {
            if (Fields.get(x, y + 1, 1).getHill()) {
                return 0
            } else {
                return 1
            }
        },
        checkHillDown = function (x, y) {
            if (Fields.get(x, y - 1, 1).getHill()) {
                return 0
            } else {
                return 1
            }
        },
        checkHillLeft = function (x, y) {
            if (Fields.get(x + 1, y, 1).getHill()) {
                return 0
            } else {
                return 1
            }
        },
        checkHillRight = function (x, y) {
            if (Fields.get(x - 1, y, 1).getHill()) {
                return 0
            } else {
                return 1
            }
        },
        checkUp = function (x, y) {
            if (Fields.get(x, y + 1, 1).getGrassOrWater() == 'g') {
                return 1
            }
        },
        checkDown = function (x, y) {
            if (Fields.get(x, y - 1, 1).getGrassOrWater() == 'g') {
                return 1
            }
        },
        checkLeft = function (x, y) {
            if (Fields.get(x + 1, y, 1).getGrassOrWater() == 'g') {
                return 1
            }
        },
        checkRight = function (x, y) {
            if (Fields.get(x - 1, y, 1).getGrassOrWater() == 'g') {
                return 1
            }
        },
        createUVS = function (uvs, stripesArray, x, y) {
            var uv = [],
                k = 0

            for (var yyy in stripesArray) {
                var stripes = stripesArray[yyy].get()

                for (var i in stripes) {
                    var field = stripes[i]

                    uv[0] = [field.start / x, yyy / y]
                    uv[1] = [field.end / x, yyy / y]
                    uv[2] = [field.start / x, (yyy * 1 + 1) / y]
                    uv[3] = [field.end / x, (yyy * 1 + 1) / y]


                    // first triangle
                    uvs[0 + k] = uv[0][0]
                    uvs[1 + k] = uv[0][1]
                    uvs[2 + k] = uv[1][0]
                    uvs[3 + k] = uv[1][1]
                    uvs[4 + k] = uv[2][0]
                    uvs[5 + k] = uv[2][1]
                    // second triangle
                    uvs[6 + k] = uv[3][0]
                    uvs[7 + k] = uv[3][1]
                    uvs[8 + k] = uv[2][0]
                    uvs[9 + k] = uv[2][1]
                    uvs[10 + k] = uv[1][0]
                    uvs[11 + k] = uv[1][1]
                    k += 12
                }
            }

            return uvs
        },
        createNewUVS = function (vertexPositions, x, y) {
            var uv = [],
                k = 0,
                uvs = new Float32Array(vertexPositions.length * 2)

            for (var i = 0; i < vertexPositions.length; i = i + 6) {
                var vertex1 = vertexPositions[i],
                    vertex2 = vertexPositions[i + 1],
                    vertex3 = vertexPositions[i + 2],
                    vertex4 = vertexPositions[i + 3]

                uv[0] = [vertex1[0] / m / x, vertex1[1] / m / y]
                uv[1] = [vertex2[0] / m / x, vertex2[1] / m / y]
                uv[2] = [vertex3[0] / m / x, vertex3[1] / m / y]
                uv[3] = [vertex4[0] / m / x, vertex4[1] / m / y]


                // first triangle
                uvs[0 + k] = uv[0][0]
                uvs[1 + k] = uv[0][1]
                uvs[2 + k] = uv[1][0]
                uvs[3 + k] = uv[1][1]
                uvs[4 + k] = uv[2][0]
                uvs[5 + k] = uv[2][1]
                // second triangle
                uvs[6 + k] = uv[3][0]
                uvs[7 + k] = uv[3][1]
                uvs[8 + k] = uv[2][0]
                uvs[9 + k] = uv[2][1]
                uvs[10 + k] = uv[1][0]
                uvs[11 + k] = uv[1][1]
                k += 12
            }

            return uvs
        },
        createWaterVertexPositionsUp = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1],
                    x1 = x * m,
                    x2 = x * m + 1 * m,
                    x3 = x * m,
                    x4 = x * m + 1 * m,
                    y1 = y * m,
                    y2 = y * m,
                    y3 = y * m + 1 * m,
                    y4 = y * m + 1 * m


                if (Fields.get(x - 1, y, 1).getGrassOrWater() == 'w') {
                    if (Fields.get(x - 1, y + 1, 1).getGrassOrWater() != 'g') {
                        var x1 = x1 - 0.3 * m
                    }
                } else {
                    var x1 = x1 + 0.3 * m
                }

                if (Fields.get(x + 1, y, 1).getGrassOrWater() == 'w') {
                    if (Fields.get(x + 1, y + 1, 1).getGrassOrWater() != 'g') {
                        var x2 = x2 + 0.3 * m
                    }
                } else {
                    var x2 = x2 - 0.3 * m
                }

                vertexPositions.push([x1, y1 + 0.7 * m, bottomLevel])       //
                vertexPositions.push([x2, y2 + 0.7 * m, bottomLevel])       //  FIRST TRIANGLE
                vertexPositions.push([x3, y3, 0])                   //

                vertexPositions.push([x4, y4, 0])           //
                vertexPositions.push([x3, y3, 0])                   //  SECOND TRIANGLE
                vertexPositions.push([x2, y2 + 0.7 * m, bottomLevel])       //
            }

            return vertexPositions
        },
        createWaterVertexPositionsDown = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1],
                    x1 = x * m,
                    x2 = x * m + 1 * m,
                    x3 = x * m,
                    x4 = x * m + 1 * m,
                    y1 = y * m,
                    y2 = y * m,
                    y3 = y * m + 1 * m,
                    y4 = y * m + 1 * m


                if (Fields.get(x - 1, y, 1).getGrassOrWater() == 'w') {
                    if (Fields.get(x - 1, y - 1, 1).getGrassOrWater() != 'g') {
                        var x3 = x3 - 0.3 * m
                    }
                } else {
                    var x3 = x3 + 0.3 * m
                }

                if (Fields.get(x + 1, y, 1).getGrassOrWater() == 'w') {
                    if (Fields.get(x + 1, y - 1, 1).getGrassOrWater() != 'g') {
                        var x4 = x4 + 0.3 * m
                    }
                } else {
                    var x4 = x4 - 0.3 * m
                }

                vertexPositions.push([x1, y1, 0])                            //
                vertexPositions.push([x2, y2, 0])                    //  FIRST TRIANGLE
                vertexPositions.push([x3, y3 - 0.7 * m, bottomLevel])        //

                vertexPositions.push([x4, y4 - 0.7 * m, bottomLevel])        //
                vertexPositions.push([x3, y3 - 0.7 * m, bottomLevel])        //  SECOND TRIANGLE
                vertexPositions.push([x2, y2, 0])                    //
            }

            return vertexPositions
        },
        createWaterVertexPositionsLeft = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1],
                    x1 = x * m,
                    x2 = x * m + 1 * m,
                    x3 = x * m,
                    x4 = x * m + 1 * m,
                    y1 = y * m,
                    y2 = y * m,
                    y3 = y * m + 1 * m,
                    y4 = y * m + 1 * m

                if (Fields.get(x, y - 1, 1).getGrassOrWater() == 'w') {
                    if (Fields.get(x + 1, y - 1, 1).getGrassOrWater() != 'g') {
                        var y1 = y1 - 0.3 * m
                    }
                } else {
                    var y1 = y1 + 0.3 * m
                }

                if (Fields.get(x, y + 1, 1).getGrassOrWater() == 'w') {
                    if (Fields.get(x + 1, y + 1, 1).getGrassOrWater() != 'g') { // w|g
                        var y3 = y3 + 0.3 * m
                    }
                } else {                                                        // g
                    var y3 = y3 - 0.3 * m
                }

                vertexPositions.push([x1 + 0.7 * m, y1, bottomLevel])       //
                vertexPositions.push([x2, y2, 0])                    //  FIRST TRIANGLE
                vertexPositions.push([x3 + 0.7 * m, y3, bottomLevel])       //

                vertexPositions.push([x4, y4, 0])                //
                vertexPositions.push([x3 + 0.7 * m, y3, bottomLevel])       //  SECOND TRIANGLE
                vertexPositions.push([x2, y2, 0])                    //
            }

            return vertexPositions
        },
        createWaterVertexPositionsRight = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1],
                    x1 = x * m,
                    x2 = x * m + 0.3 * m,
                    x3 = x * m,
                    x4 = x * m + 0.3 * m,
                    y1 = y * m,
                    y2 = y * m,
                    y3 = y * m + 1 * m,
                    y4 = y * m + 1 * m


                if (Fields.get(x, y - 1, 1).getGrassOrWater() == 'w') {
                    if (Fields.get(x - 1, y - 1, 1).getGrassOrWater() != 'g') {  // g|w
                        var y2 = y2 - 0.3 * m
                    }
                } else {                                                         // g
                    var y2 = y2 + 0.3 * m
                }

                if (Fields.get(x, y + 1, 1).getGrassOrWater() == 'w') {
                    if (Fields.get(x - 1, y + 1, 1).getGrassOrWater() != 'g') { // g|w
                        var y4 = y4 + 0.3 * m
                    }
                } else {                                                        // g
                    var y4 = y4 - 0.3 * m
                }

                vertexPositions.push([x1, y1, 0])     //
                vertexPositions.push([x2, y2, bottomLevel])                         //  FIRST TRIANGLE
                vertexPositions.push([x3, y3, 0])     //

                vertexPositions.push([x4, y4, bottomLevel])                 //
                vertexPositions.push([x3, y3, 0])     //  SECOND TRIANGLE
                vertexPositions.push([x2, y2, bottomLevel])                         //
            }

            return vertexPositions
        },
        createHillVertexPositionsUp = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1],
                    x1 = x * m,
                    x2 = x * m + 1 * m,
                    x3 = x * m,
                    x4 = x * m + 1 * m,
                    y1 = y * m,
                    y2 = y * m,
                    y3 = y * m + 1 * m,
                    y4 = y * m + 1 * m


                if (Fields.get(x - 1, y, 1).getHill()) {
                    if (Fields.get(x - 1, y + 1, 1).getHill()) {
                        var x1 = x1 - 0.3 * m
                    }
                } else {
                    var x1 = x1 + 0.3 * m
                }

                if (Fields.get(x + 1, y, 1).getHill()) {
                    if (Fields.get(x + 1, y + 1, 1).getHill()) {
                        var x2 = x2 + 0.3 * m
                    }
                } else {
                    var x2 = x2 - 0.3 * m
                }

                vertexPositions.push([x1, y1 + 0.7 * m, hillLevel])       //
                vertexPositions.push([x2, y2 + 0.7 * m, hillLevel])       //  FIRST TRIANGLE
                vertexPositions.push([x3, y3, 0])                    //

                vertexPositions.push([x4, y4, 0])                //
                vertexPositions.push([x3, y3, 0])                    //  SECOND TRIANGLE
                vertexPositions.push([x2, y2 + 0.7 * m, hillLevel])       //
            }

            return vertexPositions
        },
        createHillVertexPositionsDown = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1],
                    x1 = x * m,
                    x2 = x * m + 1 * m,
                    x3 = x * m,
                    x4 = x * m + 1 * m,
                    y1 = y * m,
                    y2 = y * m,
                    y3 = y * m + 0.3 * m,
                    y4 = y * m + 0.3 * m


                if (Fields.get(x - 1, y, 1).getHill()) {
                    if (Fields.get(x - 1, y - 1, 1).getHill()) {
                        var x3 = x3 - 0.3 * m
                    }
                } else {
                    var x3 = x3 + 0.3 * m
                }

                if (Fields.get(x + 1, y, 1).getHill()) {
                    if (Fields.get(x + 1, y - 1, 1).getHill()) {
                        var x4 = x4 + 0.3 * m
                    }
                } else {
                    var x4 = x4 - 0.3 * m
                }

                vertexPositions.push([x1, y1, 0])               //
                vertexPositions.push([x2, y2, 0])               //  FIRST TRIANGLE
                vertexPositions.push([x3, y3, hillLevel])       //

                vertexPositions.push([x4, y4, hillLevel])       //
                vertexPositions.push([x3, y3, hillLevel])       //  SECOND TRIANGLE
                vertexPositions.push([x2, y2, 0])               //
            }

            return vertexPositions
        },
        createHillVertexPositionsLeft = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1],
                    x1 = x * m,
                    x2 = x * m + 1 * m,
                    x3 = x * m,
                    x4 = x * m + 1 * m,
                    y1 = y * m,
                    y2 = y * m,
                    y3 = y * m + 1 * m,
                    y4 = y * m + 1 * m


                if (Fields.get(x, y - 1, 1).getHill()) {
                    if (Fields.get(x + 1, y - 1, 1).getHill()) {
                        var y1 = y1 - 0.3 * m
                    }
                } else {
                    var y1 = y1 + 0.3 * m
                }

                if (Fields.get(x, y + 1, 1).getHill()) {
                    if (Fields.get(x + 1, y + 1, 1).getHill()) { // w|g
                        var y3 = y3 + 0.3 * m
                    }
                } else {                                                        // g
                    var y3 = y3 - 0.3 * m
                }

                vertexPositions.push([x1 + 0.7 * m, y1, hillLevel])       //
                vertexPositions.push([x2, y2, 0])                    //  FIRST TRIANGLE
                vertexPositions.push([x3 + 0.7 * m, y3, hillLevel])       //

                vertexPositions.push([x4, y4, 0])                //
                vertexPositions.push([x3 + 0.7 * m, y3, hillLevel])       //  SECOND TRIANGLE
                vertexPositions.push([x2, y2, 0])                    //
            }

            return vertexPositions
        },
        createHillVertexPositionsRight = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1],
                    x1 = x * m,
                    x2 = x * m + 0.3 * m,
                    x3 = x * m,
                    x4 = x * m + 0.3 * m,
                    y1 = y * m,
                    y2 = y * m,
                    y3 = y * m + 1 * m,
                    y4 = y * m + 1 * m


                if (Fields.get(x, y - 1, 1).getHill()) {
                    if (Fields.get(x - 1, y - 1, 1).getHill()) {  // g|w
                        var y2 = y2 - 0.3 * m
                    }
                } else {                                                         // g
                    var y2 = y2 + 0.3 * m
                }

                if (Fields.get(x, y + 1, 1).getHill()) {
                    if (Fields.get(x - 1, y + 1, 1).getHill()) { // g|w
                        var y4 = y4 + 0.3 * m
                    }
                } else {                                                        // g
                    var y4 = y4 - 0.3 * m
                }

                vertexPositions.push([x1, y1, 0])       //
                vertexPositions.push([x2, y2, hillLevel])                        //  FIRST TRIANGLE
                vertexPositions.push([x3, y3, 0])       //

                vertexPositions.push([x4, y4, hillLevel])                    //
                vertexPositions.push([x3, y3, 0])       //  SECOND TRIANGLE
                vertexPositions.push([x2, y2, hillLevel])                        //
            }

            return vertexPositions
        },
        createHillVertexPositions = function (x, y) {
            var vertexPositions = [], stripesArray = {}

            for (var yy = 0; yy < y; yy++) {
                var stripes = new Stripes(),
                    start = 0,
                    end = 0

                for (var xx = 0; xx < x; xx++) {
                    if (!Fields.get(xx, yy).getHill()) {
                        continue
                    }

                    var hillsAround = [], k = 0

                    for (var i = xx - 1; i <= xx + 1; i++) {
                        for (var j = yy - 1; j <= yy + 1; j++) {
                            if (i == xx && j == yy) {
                                continue
                            }
                            var l = Fields.get(i, j, 1).getHill()
                            k = k + l
                            hillsAround.push(l)
                        }
                    }

                    if (k == 8) {
                        if (!start) {
                            var startX = xx

                            start = 1
                            end = 0
                        }
                    } else {
                        if (start && !end) {
                            stripes.add(startX, xx)
                            start = 0
                            end = 1
                        }

                        var x1 = xx * m,
                            x2 = xx * m + 1 * m,
                            x3 = xx * m,
                            x4 = xx * m + 1 * m,
                            y1 = yy * m,
                            y2 = yy * m,
                            y3 = yy * m + 1 * m,
                            y4 = yy * m + 1 * m

                        if (!Fields.get(xx, yy - 1, 1).getHill()) { // above
                            y1 = y1 + 0.3 * m
                            y2 = y2 + 0.3 * m
                        }
                        if (!Fields.get(xx, yy + 1, 1).getHill()) { // under
                            y3 = y3 - 0.3 * m
                            y4 = y4 - 0.3 * m
                        }
                        if (!Fields.get(xx - 1, yy, 1).getHill()) { // left
                            x1 = x1 + 0.3 * m
                            x3 = x3 + 0.3 * m
                        }
                        if (!Fields.get(xx + 1, yy, 1).getHill()) { // right
                            x2 = x2 - 0.3 * m
                            x4 = x4 - 0.3 * m
                        }


                        if (!Fields.get(xx, yy - 1, 1).getHill() && Fields.get(xx + 1, yy, 1).getHill() && Fields.get(xx + 1, yy - 1, 1).getHill()) { // corner 1
                            x2 = x2 + 0.3 * m
                        }
                        if (!Fields.get(xx - 1, yy - 1, 1).getHill() && Fields.get(xx, yy - 1, 1).getHill() && Fields.get(xx - 1, yy, 1).getHill()) { // corner 2
                            x1 = x1 + 0.3 * m
                            y1 = y1 + 0.3 * m
                        }
                        if (!Fields.get(xx - 1, yy, 1).getHill() && Fields.get(xx, yy + 1, 1).getHill() && Fields.get(xx - 1, yy + 1, 1).getHill()) { // corner 3
                            y3 = y3 + 0.3 * m
                        }

                        if (!Fields.get(xx - 1, yy + 1, 1).getHill() && Fields.get(xx, yy + 1, 1).getHill() && Fields.get(xx - 1, yy, 1).getHill()) { // corner 4
                            x3 = x3 + 0.3 * m
                            y3 = y3 - 0.3 * m
                        }
                        if (!Fields.get(xx, yy + 1, 1).getHill() && Fields.get(xx + 1, yy + 1, 1).getHill() && Fields.get(xx + 1, yy, 1).getHill()) { // corner 5
                            x4 = x4 + 0.3 * m
                        }
                        if (!Fields.get(xx - 1, yy, 1).getHill() && Fields.get(xx, yy - 1, 1).getHill() && Fields.get(xx - 1, yy - 1, 1).getHill()) { // corner 6
                            y1 = y1 - 0.3 * m
                        }

                        if (!Fields.get(xx + 1, yy + 1, 1).getHill() && Fields.get(xx, yy + 1, 1).getHill() && Fields.get(xx + 1, yy, 1).getHill()) { // corner 7
                            x4 = x4 - 0.3 * m
                            y4 = y4 - 0.3 * m
                        }
                        if (!Fields.get(xx + 1, yy, 1).getHill() && Fields.get(xx, yy - 1, 1).getHill() && Fields.get(xx + 1, yy - 1, 1).getHill()) { // corner 8
                            y2 = y2 - 0.3 * m
                        }
                        if (!Fields.get(xx, yy + 1, 1).getHill() && Fields.get(xx - 1, yy + 1, 1).getHill() && Fields.get(xx - 1, yy, 1).getHill()) { // corner 9
                            x3 = x3 - 0.3 * m
                        }

                        if (!Fields.get(xx + 1, yy - 1, 1).getHill() && Fields.get(xx, yy - 1, 1).getHill() && Fields.get(xx + 1, yy, 1).getHill()) { // corner 10
                            x2 = x2 - 0.3 * m
                            y2 = y2 + 0.3 * m
                        }
                        if (!Fields.get(xx + 1, yy, 1).getHill() && Fields.get(xx, yy + 1, 1).getHill() && Fields.get(xx + 1, yy + 1, 1).getHill()) { // corner 11
                            y4 = y4 + 0.3 * m
                        }
                        if (!Fields.get(xx, yy - 1, 1).getHill() && Fields.get(xx - 1, yy - 1, 1).getHill() && Fields.get(xx - 1, yy, 1).getHill()) { // corner 12
                            x1 = x1 - 0.3 * m
                        }

                        //  FIRST TRIANGLE
                        vertexPositions.push([x1, y1, hillLevel])         // I
                        vertexPositions.push([x2, y2, hillLevel])         // II
                        vertexPositions.push([x3, y3, hillLevel])         // III

                        // SECOND TRIANGLE
                        vertexPositions.push([x4, y4, hillLevel])         // IV
                        vertexPositions.push([x3, y3, hillLevel])         // III
                        vertexPositions.push([x2, y2, hillLevel])         // II
                    }
                }
                stripesArray[yy] = stripes
            }

            return {'vertexPositions': vertexPositions, 'stripesArray': stripesArray}
        },
        createMountainVertexPositionsUp = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1],
                    x1 = x * m,
                    x2 = x * m + 1 * m,
                    x3 = x * m,
                    x4 = x * m + 1 * m,
                    y1 = y * m + 0.7 * m,
                    y2 = y * m + 0.7 * m,
                    y3 = y * m + 1 * m,
                    y4 = y * m + 1 * m

                if (Fields.get(x - 1, y, 1).getMountain()) {
                    if (Fields.get(x - 1, y + 1, 1).getMountain()) {
                        var x1 = x1 - 0.3 * m
                    }
                } else {
                    var x1 = x1 + 0.3 * m
                }

                if (Fields.get(x + 1, y, 1).getMountain()) {
                    if (Fields.get(x + 1, y + 1, 1).getMountain()) {
                        var x2 = x2 + 0.3 * m
                    }
                } else {
                    var x2 = x2 - 0.3 * m
                }

                vertexPositions.push([x1, y1, mountainLevel])       //
                vertexPositions.push([x2, y2, mountainLevel])       //  FIRST TRIANGLE
                vertexPositions.push([x3, y3, 0])                    //

                vertexPositions.push([x4, y4, 0])                //
                vertexPositions.push([x3, y3, 0])                    //  SECOND TRIANGLE
                vertexPositions.push([x2, y2, mountainLevel])       //
            }

            return vertexPositions
        },
        createMountainVertexPositionsDown = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1],
                    x1 = x * m,
                    x2 = x * m + 1 * m,
                    x3 = x * m,
                    x4 = x * m + 1 * m,
                    y1 = y * m,
                    y2 = y * m,
                    y3 = y * m + 0.3 * m,
                    y4 = y * m + 0.3 * m


                if (Fields.get(x - 1, y, 1).getMountain()) {
                    if (Fields.get(x - 1, y - 1, 1).getMountain()) {
                        var x3 = x3 - 0.3 * m
                    }
                } else {
                    var x3 = x3 + 0.3 * m
                }

                if (Fields.get(x + 1, y, 1).getMountain()) {
                    if (Fields.get(x + 1, y - 1, 1).getMountain()) {
                        var x4 = x4 + 0.3 * m
                    }
                } else {
                    var x4 = x4 - 0.3 * m
                }

                vertexPositions.push([x1, y1, 0])                        //
                vertexPositions.push([x2, y2, 0])                    //  FIRST TRIANGLE
                vertexPositions.push([x3, y3, mountainLevel])       //

                vertexPositions.push([x4, y4, mountainLevel])       //
                vertexPositions.push([x3, y3, mountainLevel])       //  SECOND TRIANGLE
                vertexPositions.push([x2, y2, 0])                    //
            }

            return vertexPositions
        },
        createMountainVertexPositionsLeft = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1],
                    x1 = x * m + 0.7 * m,
                    x2 = x * m + 1 * m,
                    x3 = x * m + 0.7 * m,
                    x4 = x * m + 1 * m,
                    y1 = y * m,
                    y2 = y * m,
                    y3 = y * m + 1 * m,
                    y4 = y * m + 1 * m


                if (Fields.get(x, y - 1, 1).getMountain()) {
                    if (Fields.get(x + 1, y - 1, 1).getMountain()) {
                        var y1 = y1 - 0.3 * m
                    }
                } else {
                    var y1 = y1 + 0.3 * m
                }

                if (Fields.get(x, y + 1, 1).getMountain()) {
                    if (Fields.get(x + 1, y + 1, 1).getMountain()) { // w|g
                        var y3 = y3 + 0.3 * m
                    }
                } else {                                                        // g
                    var y3 = y3 - 0.3 * m
                }

                vertexPositions.push([x1, y1, mountainLevel])       //
                vertexPositions.push([x2, y2, 0])                    //  FIRST TRIANGLE
                vertexPositions.push([x3, y3, mountainLevel])       //

                vertexPositions.push([x4, y4, 0])                //
                vertexPositions.push([x3, y3, mountainLevel])       //  SECOND TRIANGLE
                vertexPositions.push([x2, y2, 0])                    //
            }

            return vertexPositions
        },
        createMountainVertexPositionsRight = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1],
                    x1 = x * m,
                    x2 = x * m + 0.3 * m,
                    x3 = x * m,
                    x4 = x * m + 0.3 * m,
                    y1 = y * m,
                    y2 = y * m,
                    y3 = y * m + 1 * m,
                    y4 = y * m + 1 * m


                if (Fields.get(x, y - 1, 1).getMountain()) {
                    if (Fields.get(x - 1, y - 1, 1).getMountain()) {  // g|w
                        var y2 = y2 - 0.3 * m
                    }
                } else {                                                         // g
                    var y2 = y2 + 0.3 * m
                }

                if (Fields.get(x, y + 1, 1).getMountain()) {
                    if (Fields.get(x - 1, y + 1, 1).getMountain()) { // g|w
                        var y4 = y4 + 0.3 * m
                    }
                } else {                                                        // g
                    var y4 = y4 - 0.3 * m
                }

                vertexPositions.push([x1, y1, 0])       //
                vertexPositions.push([x2, y2, mountainLevel])                        //  FIRST TRIANGLE
                vertexPositions.push([x3, y3, 0])       //

                vertexPositions.push([x4, y4, mountainLevel])                    //
                vertexPositions.push([x3, y3, 0])       //  SECOND TRIANGLE
                vertexPositions.push([x2, y2, mountainLevel])                        //
            }

            return vertexPositions
        },
        createMountainVertexPositions = function (x, y) {
            var vertexPositions = [], stripesArray = {}

            for (var yy = 0; yy < y; yy++) {
                var stripes = new Stripes(),
                    start = 0,
                    end = 0

                for (var xx = 0; xx < x; xx++) {
                    if (!Fields.get(xx, yy).getMountain()) {
                        continue
                    }

                    var mountainsAround = [], k = 0

                    for (var i = xx - 1; i <= xx + 1; i++) {
                        for (var j = yy - 1; j <= yy + 1; j++) {
                            if (i == xx && j == yy) {
                                continue
                            }
                            var l = Fields.get(i, j, 1).getMountain()
                            k = k + l
                            mountainsAround.push(l)
                        }
                    }

                    if (k == 8) {
                        if (!start) {
                            var startX = xx

                            start = 1
                            end = 0
                        }
                    } else {
                        if (start && !end) {
                            stripes.add(startX, xx)
                            start = 0
                            end = 1
                        }
                        var x1 = xx * m,
                            x2 = xx * m + 1 * m,
                            x3 = xx * m,
                            x4 = xx * m + 1 * m,
                            y1 = yy * m,
                            y2 = yy * m,
                            y3 = yy * m + 1 * m,
                            y4 = yy * m + 1 * m

                        if (!Fields.get(xx, yy - 1, 1).getMountain()) { // above
                            y1 = y1 + 0.3 * m
                            y2 = y2 + 0.3 * m
                        }
                        if (!Fields.get(xx, yy + 1, 1).getMountain()) { // under
                            y3 = y3 - 0.3 * m
                            y4 = y4 - 0.3 * m
                        }
                        if (!Fields.get(xx - 1, yy, 1).getMountain()) { // left
                            x1 = x1 + 0.3 * m
                            x3 = x3 + 0.3 * m
                        }
                        if (!Fields.get(xx + 1, yy, 1).getMountain()) { // right
                            x2 = x2 - 0.3 * m
                            x4 = x4 - 0.3 * m
                        }


                        if (!Fields.get(xx, yy - 1, 1).getMountain() && Fields.get(xx + 1, yy, 1).getMountain() && Fields.get(xx + 1, yy - 1, 1).getMountain()) { // corner 1
                            x2 = x2 + 0.3 * m
                        }
                        if (!Fields.get(xx - 1, yy - 1, 1).getMountain() && Fields.get(xx, yy - 1, 1).getMountain() && Fields.get(xx - 1, yy, 1).getMountain()) { // corner 2
                            x1 = x1 + 0.3 * m
                            y1 = y1 + 0.3 * m
                        }
                        if (!Fields.get(xx - 1, yy, 1).getMountain() && Fields.get(xx, yy + 1, 1).getMountain() && Fields.get(xx - 1, yy + 1, 1).getMountain()) { // corner 3
                            y3 = y3 + 0.3 * m
                        }

                        if (!Fields.get(xx - 1, yy + 1, 1).getMountain() && Fields.get(xx, yy + 1, 1).getMountain() && Fields.get(xx - 1, yy, 1).getMountain()) { // corner 4
                            x3 = x3 + 0.3 * m
                            y3 = y3 - 0.3 * m
                        }
                        if (!Fields.get(xx, yy + 1, 1).getMountain() && Fields.get(xx + 1, yy + 1, 1).getMountain() && Fields.get(xx + 1, yy, 1).getMountain()) { // corner 5
                            x4 = x4 + 0.3 * m
                        }
                        if (!Fields.get(xx - 1, yy, 1).getMountain() && Fields.get(xx, yy - 1, 1).getMountain() && Fields.get(xx - 1, yy - 1, 1).getMountain()) { // corner 6
                            y1 = y1 - 0.3 * m
                        }

                        if (!Fields.get(xx + 1, yy + 1, 1).getMountain() && Fields.get(xx, yy + 1, 1).getMountain() && Fields.get(xx + 1, yy, 1).getMountain()) { // corner 7
                            x4 = x4 - 0.3 * m
                            y4 = y4 - 0.3 * m
                        }
                        if (!Fields.get(xx + 1, yy, 1).getMountain() && Fields.get(xx, yy - 1, 1).getMountain() && Fields.get(xx + 1, yy - 1, 1).getMountain()) { // corner 8
                            y2 = y2 - 0.3 * m
                        }
                        if (!Fields.get(xx, yy + 1, 1).getMountain() && Fields.get(xx - 1, yy + 1, 1).getMountain() && Fields.get(xx - 1, yy, 1).getMountain()) { // corner 9
                            x3 = x3 - 0.3 * m
                        }

                        if (!Fields.get(xx + 1, yy - 1, 1).getMountain() && Fields.get(xx, yy - 1, 1).getMountain() && Fields.get(xx + 1, yy, 1).getMountain()) { // corner 10
                            x2 = x2 - 0.3 * m
                            y2 = y2 + 0.3 * m
                        }
                        if (!Fields.get(xx + 1, yy, 1).getMountain() && Fields.get(xx, yy + 1, 1).getMountain() && Fields.get(xx + 1, yy + 1, 1).getMountain()) { // corner 11
                            y4 = y4 + 0.3 * m
                        }
                        if (!Fields.get(xx, yy - 1, 1).getMountain() && Fields.get(xx - 1, yy - 1, 1).getMountain() && Fields.get(xx - 1, yy, 1).getMountain()) { // corner 12
                            x1 = x1 - 0.3 * m
                        }

                        //  FIRST TRIANGLE
                        vertexPositions.push([x1, y1, mountainLevel])         // I
                        vertexPositions.push([x2, y2, mountainLevel])         // II
                        vertexPositions.push([x3, y3, mountainLevel])         // III

                        // SECOND TRIANGLE
                        vertexPositions.push([x4, y4, mountainLevel])         // IV
                        vertexPositions.push([x3, y3, mountainLevel])         // III
                        vertexPositions.push([x2, y2, mountainLevel])         // II
                    }
                }
                stripesArray[yy] = stripes
            }

            return {'vertexPositions': vertexPositions, 'stripesArray': stripesArray}
        },
        createVertexPositionsFromStripesArray = function (stripesArray, z) {
            var vertexPositions = []
            for (var yyy in stripesArray) {
                var stripes = stripesArray[yyy].get()
                // console.log(stripes)

                for (var i in stripes) {
                    var field = stripes[i]


                    vertexPositions.push([field.start * m, yyy * m, z])           //
                    vertexPositions.push([field.end * m, yyy * m, z])             //  FIRST TRIANGLE
                    vertexPositions.push([field.start * m, yyy * m + 1 * m, z])       //

                    vertexPositions.push([field.end * m, yyy * m + 1 * m, z])         //
                    vertexPositions.push([field.start * m, yyy * m + 1 * m, z])       //  SECOND TRIANGLE
                    vertexPositions.push([field.end * m, yyy * m, z])             //
                }
            }

            return vertexPositions
        },
        createWaterStripes = function (x, y) {
            var stripes = new WaterStripes()

            for (var yy = 0; yy < y; yy++) {
                for (var xx = 0; xx < x; xx++) {
                    if (Fields.get(xx, yy).getGrassOrWater() == 'g') {
                        continue
                    }

                    if (checkDown(xx, yy)) {
                        stripes.addDown(xx, yy)
                    }
                    if (checkUp(xx, yy)) {
                        stripes.addUp(xx, yy)
                    }
                    if (checkLeft(xx, yy)) {
                        stripes.addLeft(xx, yy)
                    }
                    if (checkRight(xx, yy)) {
                        stripes.addRight(xx, yy)
                    }
                }
            }

            return stripes
        },
        createHillStripes = function (x, y) {
            var stripes = new HillStripes()

            for (var yy = 0; yy < y; yy++) {
                for (var xx = 0; xx < x; xx++) {
                    if (!Fields.get(xx, yy).getHill()) {
                        continue
                    }

                    if (checkHillDown(xx, yy)) {
                        stripes.addDown(xx, yy)
                    }
                    if (checkHillUp(xx, yy)) {
                        stripes.addUp(xx, yy)
                    }
                    if (checkHillLeft(xx, yy)) {
                        stripes.addLeft(xx, yy)
                    }
                    if (checkHillRight(xx, yy)) {
                        stripes.addRight(xx, yy)
                    }
                }
            }

            return stripes
        },
        createMountainStripes = function (x, y) {
            var stripes = new MountainStripes()

            for (var yy = 0; yy < y; yy++) {
                for (var xx = 0; xx < x; xx++) {
                    if (!Fields.get(xx, yy).getMountain()) {
                        continue
                    }

                    if (checkMountainDown(xx, yy)) {
                        stripes.addDown(xx, yy)
                    }
                    if (checkMountainUp(xx, yy)) {
                        stripes.addUp(xx, yy)
                    }
                    if (checkMountainLeft(xx, yy)) {
                        stripes.addLeft(xx, yy)
                    }
                    if (checkMountainRight(xx, yy)) {
                        stripes.addRight(xx, yy)
                    }
                }
            }

            return stripes
        },
        createGrassStripes = function (x, y) {
            var stripesArray = {}

            for (var yy = 0; yy < y; yy++) {

                var stripes = new Stripes(),
                    start = 0,
                    end = 0

                for (var xx = 0; xx < x; xx++) {

                    if (Fields.get(xx, yy).getGrassOrWater() == 'g') {
                        if (!start) {
                            var startX = xx

                            start = 1
                            end = 0
                        }
                    } else {
                        if (start && !end) {
                            stripes.add(startX, xx)
                            start = 0
                            end = 1
                        }
                    }
                }

                if (start && !end) {
                    stripes.add(startX, xx)
                    start = 0
                    end = 1
                }

                stripesArray[yy] = stripes
            }

            return stripesArray
        },
        createGeometry = function (vertexPositions, uvs) {
            var geometry = new THREE.BufferGeometry()


            var vertices = new Float32Array(vertexPositions.length * 3),
                normals = new Float32Array(vertexPositions.length * 3)

            for (var i = 0; i < vertexPositions.length; i++) {
                var index = 3 * i
                vertices[index + 0] = vertexPositions[i][0]
                vertices[index + 1] = vertexPositions[i][1]
                vertices[index + 2] = vertexPositions[i][2]
            }

            geometry.addAttribute('position', new THREE.BufferAttribute(vertices, 3))
            geometry.addAttribute('normal', new THREE.BufferAttribute(normals, 3))
            if (isSet(uvs)) {
                geometry.addAttribute('uv', new THREE.BufferAttribute(uvs, 2))
            }

            geometry.computeVertexNormals()

            return geometry
        },
        createMesh = function (geometry, canvas) {
            if (!geometry) {
                return
            }

            if (isSet(canvas)) {
                var texture = new THREE.Texture(canvas)
                texture.needsUpdate = true

                var mesh = new THREE.Mesh(geometry, new THREE.MeshLambertMaterial({
                    map: texture,
                    side: THREE.DoubleSide
                }))
            } else {
                var mesh = new THREE.Mesh(geometry, new THREE.MeshLambertMaterial({
                    color: '#ffffff',
                    side: THREE.DoubleSide
                }))
            }


            // mesh.rotation.x = Math.PI / 2

            if (Page.getShadows()) {
                // mesh.castShadow = true
                mesh.receiveShadow = true
            }


            var geo = new THREE.WireframeGeometry(mesh.geometry),
                mat = new THREE.LineBasicMaterial({color: 0x00ff00, linewidth: 1}),
                wireframe = new THREE.LineSegments(geo, mat)
            mesh.add(wireframe)

            return mesh
        },
        createWater = function (x, y, canvas) {
            var maxX = x * 2,
                maxY = y * 2

            var texture = new THREE.Texture(canvas)
            texture.needsUpdate = true

            var mesh = new THREE.Mesh(new THREE.PlaneBufferGeometry(maxX, maxY), new THREE.MeshLambertMaterial({
                map: texture,
                side: THREE.DoubleSide
            }))
            mesh.rotation.x = Math.PI / 2
            mesh.position.set(maxX / 2, -waterLevel, maxY / 2)

            if (Page.getShadows()) {
                mesh.receiveShadow = true
            }

            return mesh
        },
        createBorder1 = function (x, y) {
            var maxX = x * 2,
                maxY = y * 2

            var mesh = new THREE.Mesh(new THREE.PlaneBufferGeometry(maxX, 1), new THREE.MeshLambertMaterial({
                color: '#34220c',
                side: THREE.DoubleSide
            }))

            mesh.position.y = y
            mesh.position.z = 0.5 - waterLevel

            mesh.rotation.x = Math.PI / 2

            return mesh
        },
        createBorder2 = function (x, y) {
            var maxX = x * 2,
                maxY = y * 2

            var mesh = new THREE.Mesh(new THREE.PlaneBufferGeometry(maxY, 1), new THREE.MeshLambertMaterial({
                color: '#34220c',
                side: THREE.DoubleSide
            }))
            mesh.rotation.y = Math.PI / 2
            mesh.rotation.z = Math.PI / 2

            mesh.position.x = -x
            mesh.position.z = 0.5 - waterLevel

            return mesh
        }

    this.getMountainLevel = function () {
        return mountainLevel
    }
    this.getHillLevel = function () {
        return hillLevel
    }
    this.getWaterLevel = function () {
        return waterLevel
    }
    this.init = function (x, y, textureCanvas, waterTextureCanvas) {
        var waterMesh = createWater(x, y, waterTextureCanvas)

        var stripesArray = createGrassStripes(x, y),
            vertexPositions = createVertexPositionsFromStripesArray(stripesArray, 0),
            uvs = createUVS(new Float32Array(vertexPositions.length * 2), stripesArray, x, y)

        var mesh = createMesh(createGeometry(vertexPositions, uvs), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel

        waterMesh.add(mesh)

        var stripes = createWaterStripes(x, y),
            vertexPositionsUp = createWaterVertexPositionsUp(stripes.getUp()),
            vertexPositionsDown = createWaterVertexPositionsDown(stripes.getDown()),
            vertexPositionsLeft = createWaterVertexPositionsLeft(stripes.getLeft()),
            vertexPositionsRight = createWaterVertexPositionsRight(stripes.getRight())

        mesh = createMesh(createGeometry(vertexPositionsUp, createNewUVS(vertexPositionsUp, x, y)), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel

        waterMesh.add(mesh)

        mesh = createMesh(createGeometry(vertexPositionsDown, createNewUVS(vertexPositionsDown, x, y)), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel

        waterMesh.add(mesh)

        mesh = createMesh(createGeometry(vertexPositionsLeft, createNewUVS(vertexPositionsLeft, x, y)), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel

        waterMesh.add(mesh)

        mesh = createMesh(createGeometry(vertexPositionsRight, createNewUVS(vertexPositionsRight, x, y)), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel

        waterMesh.add(mesh)

        var stripes = createHillStripes(x, y),
            vertexPositionsUp = createHillVertexPositionsUp(stripes.getUp()),
            vertexPositionsDown = createHillVertexPositionsDown(stripes.getDown()),
            vertexPositionsLeft = createHillVertexPositionsLeft(stripes.getLeft()),
            vertexPositionsRight = createHillVertexPositionsRight(stripes.getRight()),
            hillsTopArray = createHillVertexPositions(x, y)

        mesh = createMesh(createGeometry(vertexPositionsUp, createNewUVS(vertexPositionsUp, x, y)), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel
        waterMesh.add(mesh)
        mesh = createMesh(createGeometry(vertexPositionsDown, createNewUVS(vertexPositionsDown, x, y)), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel
        waterMesh.add(mesh)
        mesh = createMesh(createGeometry(vertexPositionsLeft, createNewUVS(vertexPositionsLeft, x, y)), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel
        waterMesh.add(mesh)
        mesh = createMesh(createGeometry(vertexPositionsRight, createNewUVS(vertexPositionsRight, x, y)), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel
        waterMesh.add(mesh)
        mesh = createMesh(createGeometry(hillsTopArray.vertexPositions, createNewUVS(hillsTopArray.vertexPositions, x, y)), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel
        waterMesh.add(mesh)

        var vertexPositionsTopCenter = createVertexPositionsFromStripesArray(hillsTopArray.stripesArray, hillLevel),
            uvs = createUVS(new Float32Array(vertexPositionsTopCenter.length * 2), hillsTopArray.stripesArray, x, y)

        var mesh = createMesh(createGeometry(vertexPositionsTopCenter, uvs), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel
        waterMesh.add(mesh)

        var stripes = createMountainStripes(x, y),
            vertexPositionsUp = createMountainVertexPositionsUp(stripes.getUp()),
            vertexPositionsDown = createMountainVertexPositionsDown(stripes.getDown()),
            vertexPositionsLeft = createMountainVertexPositionsLeft(stripes.getLeft()),
            vertexPositionsRight = createMountainVertexPositionsRight(stripes.getRight()),
            mountainsTopArray = createMountainVertexPositions(x, y)

        mesh = createMesh(createGeometry(vertexPositionsUp, createNewUVS(vertexPositionsUp, x, y)), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel
        waterMesh.add(mesh)
        mesh = createMesh(createGeometry(vertexPositionsDown, createNewUVS(vertexPositionsDown, x, y)), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel
        waterMesh.add(mesh)
        mesh = createMesh(createGeometry(vertexPositionsLeft, createNewUVS(vertexPositionsLeft, x, y)), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel
        waterMesh.add(mesh)
        mesh = createMesh(createGeometry(vertexPositionsRight, createNewUVS(vertexPositionsRight, x, y)), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel
        waterMesh.add(mesh)
        mesh = createMesh(createGeometry(mountainsTopArray.vertexPositions, createNewUVS(mountainsTopArray.vertexPositions, x, y)), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel
        waterMesh.add(mesh)

        var vertexPositionsTopCenter = createVertexPositionsFromStripesArray(mountainsTopArray.stripesArray, mountainLevel),
            uvs = createUVS(new Float32Array(vertexPositionsTopCenter.length * 2), mountainsTopArray.stripesArray, x, y)

        var mesh = createMesh(createGeometry(vertexPositionsTopCenter, uvs), textureCanvas)
        mesh.position.x = -x
        mesh.position.y = -y
        mesh.position.z = -waterLevel
        waterMesh.add(mesh)

        mesh = createBorder1(x, y)
        waterMesh.add(mesh)
        mesh = createBorder2(x, y)
        waterMesh.add(mesh)

        return waterMesh
    }
}

var WaterStripes = function () {
    var leftStripes = [],
        rightStripes = [],
        upStripes = [],
        downStripes = []

    this.addLeft = function (x, y) {
        leftStripes.push([x, y])
    }
    this.addRight = function (x, y) {
        rightStripes.push([x, y])
    }
    this.addUp = function (x, y) {
        upStripes.push([x, y])
    }
    this.addDown = function (x, y) {
        downStripes.push([x, y])
    }

    this.getUp = function () {
        return upStripes
    }
    this.getDown = function () {
        return downStripes
    }
    this.getLeft = function () {
        return leftStripes
    }
    this.getRight = function () {
        return rightStripes
    }
}

var HillStripes = function () {
    var leftStripes = [],
        rightStripes = [],
        upStripes = [],
        downStripes = []

    this.addLeft = function (x, y) {
        leftStripes.push([x, y])
    }
    this.addRight = function (x, y) {
        rightStripes.push([x, y])
    }
    this.addUp = function (x, y) {
        upStripes.push([x, y])
    }
    this.addDown = function (x, y) {
        downStripes.push([x, y])
    }

    this.getUp = function () {
        return upStripes
    }
    this.getDown = function () {
        return downStripes
    }
    this.getLeft = function () {
        return leftStripes
    }
    this.getRight = function () {
        return rightStripes
    }
}

var MountainStripes = function () {
    var leftStripes = [],
        rightStripes = [],
        upStripes = [],
        downStripes = []

    this.addLeft = function (x, y) {
        leftStripes.push([x, y])
    }
    this.addRight = function (x, y) {
        rightStripes.push([x, y])
    }
    this.addUp = function (x, y) {
        upStripes.push([x, y])
    }
    this.addDown = function (x, y) {
        downStripes.push([x, y])
    }

    this.getUp = function () {
        return upStripes
    }
    this.getDown = function () {
        return downStripes
    }
    this.getLeft = function () {
        return leftStripes
    }
    this.getRight = function () {
        return rightStripes
    }
}

var Stripes = function () {
    var stripes = []

    this.add = function (startX, endX) {
        stripes.push({'start': startX, 'end': endX})
    }

    this.get = function () {
        return stripes
    }
}
