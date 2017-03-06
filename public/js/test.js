"use strict"
var GameScene = new function () {
    var scene,
        camera,
        sun,
        cameraY = 24,
        radiansX = 2 * Math.PI + Math.atan(-1 / Math.sqrt(2)),
        radiansY = 2 * Math.PI - Math.PI / 4,
        degreesX = radiansX * (180 / Math.PI),
        degreesY = radiansY * (180 / Math.PI),
        initCamera = function (w, h) {
            var viewAngle = 22,
                near = 1,
                far = 1000

            camera = new THREE.PerspectiveCamera(viewAngle, w / h, near, far)
            camera.rotation.order = 'YXZ'
            camera.rotation.y = radiansY
            camera.rotation.x = radiansX

            camera.position.y = cameraY
            camera.scale.addScalar(1)
            scene.add(camera)
            scene.add(new THREE.AmbientLight(0x777777))
        }

    this.initSun = function (size) {
        sun = new THREE.DirectionalLight(0xdfebff, 0.75)
        sun.position.set(100, 200, 150)
        scene.add(sun)
    }
    this.moveCameraLeft = function () {
        camera.position.x += -2
        camera.position.z += -2
    }
    this.moveCameraRight = function () {
        camera.position.x += 2
        camera.position.z += 2
    }
    this.moveCameraUp = function () {
        camera.position.x += 2
        camera.position.z += -2
    }
    this.moveCameraDown = function () {
        camera.position.x += -2
        camera.position.z += 2
    }
    this.moveCameraAway = function () {
        camera.position.y += 2
        camera.position.x -= 2
        camera.position.z += 2
    }
    this.moveCameraClose = function () {
        camera.position.y -= 2
        camera.position.x += 2
        camera.position.z -= 2
    }
    this.get = function () {
        return scene
    }
    this.add = function (object) {
        scene.add(object)
    }
    this.remove = function (object) {
        scene.remove(object)
    }
    this.getCamera = function () {
        return camera
    }
    this.resize = function (w, h) {
        camera.aspect = w / h
        camera.updateProjectionMatrix()
    }
    this.init = function (w, h) {
        scene = new THREE.Scene()
        initCamera(w, h)
    }
}

var Renderer = new function () {
    var renderer = new THREE.WebGLRenderer({antialias: true})
    this.get = function () {
        return renderer
    }
}

var GameRenderer = new function () {
    var renderer,
        scene,
        camera,
        render = function () {
            renderer.render(scene, camera)
        }
    this.setSize = function (w, h) {
        renderer.setSize(w, h)
    }
    this.animate = function () {
        requestAnimationFrame(GameRenderer.animate)
        render()
    }
    this.init = function (id, Scene) {
        renderer = Renderer.get()
        scene = Scene.get()
        camera = Scene.getCamera()
        $('#' + id).append(renderer.domElement)
    }
}

function isSet(val) {
    if (typeof val === 'undefined') {
        return false;
    } else {
        return true;
    }
}

var doKey = function (event) {
    var key = event.keyCode || event.charCode;
    switch (key) {
        case 37://left
            GameScene.moveCameraLeft()
            break
        case 38://up
            GameScene.moveCameraUp()
            break
        case 39://right
            GameScene.moveCameraRight()
            break
        case 40://down
            GameScene.moveCameraDown()
            break
    }
}

$(document)
    .keydown(function (event) {
        doKey(event)
    })
    .ready(function () {
        $('#bg').hide()
        $('body').css('margin', 0)

        var size = 32

        GameScene.init($(window).innerWidth(), $(window).innerHeight())
        GameScene.resize($(window).innerWidth(), $(window).innerHeight())
        GameRenderer.init('main', GameScene)
        GameRenderer.setSize($(window).innerWidth(), $(window).innerHeight())
        GameScene.initSun(size)
        GameRenderer.animate()

        GameScene.getCamera().position.x = -16
        GameScene.getCamera().position.y = 20
        GameScene.getCamera().position.z = 24

        Fields.init()
    })

var Fields = new function () {
    var fields,
        grassField

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
     * @param grass
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
    this.init = function () {

        var f = [
            ['g', 'g', 'g', 'g', 'g', 'g', 'h', 'h', 'h', 'g'],
            ['g', 'g', 'g', 'w', 'h', 'h', 'h', 'g', 'h', 'g'],
            ['g', 'w', 'w', 'w', 'w', 'g', 'g', 'h', 'h', 'h'],
            ['g', 'w', 'w', 'w', 'w', 'w', 'g', 'h', 'm', 'h'],
            ['g', 'w', 'w', 'w', 'w', 'g', 'h', 'h', 'h', 'h'],
            ['g', 'g', 'g', 'w', 'g', 'g', 'h', 'g', 'h', 'g'],
            ['g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'h', 'g']
        ]

        grassField = new Field('g')
        fields = []

        for (var y in f) {
            for (var x in f[y]) {
                this.add(x, y, f[y][x])
            }
        }

        var maxX = fields[0].length,
            maxY = fields.length

        Ground.init(maxX, maxY)
    }
}
var Field = function (type) {
    var field = {
        'type': type
    }
    this.getType = function () {
        return field.type
    }
    this.getTypeWithoutBridge = function () {
        if (field.type == 'b') {
            return 'w'
        }
        return field.type
    }
    this.getGrassOrWater = function () {
        if (field.type == 'b' || field.type == 'w') {
            return 'w'
        } else {
            return 'g'
        }
    }
    this.getHill = function () {
        if (field.type == 'h' || field.type == 'm') {
            return 1
        } else {
            return 0
        }
    }
    this.getMountain = function () {
        if (field.type == 'm') {
            return 1
        } else {
            return 0
        }
    }
}
var Ground = new function () {
    var mountainLevel = -1.95,
        hillLevel = -0.9,
        // hillLevel = 0,
        bottomLevel = 2,
        // bottomLevel = 0,
        waterLevel = 0.1,
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
        createWaterVertexPositionsUp = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1]


                if (Fields.get(x - 1, y, 1).getGrassOrWater() == 'w') {
                    if (Fields.get(x - 1, y + 1, 1).getGrassOrWater() == 'g') {
                        var x1 = x
                    } else {
                        var x1 = x - 0.3
                    }
                } else {
                    var x1 = x + 0.3
                }

                if (Fields.get(x + 1, y, 1).getGrassOrWater() == 'w') {
                    if (Fields.get(x + 1, y + 1, 1).getGrassOrWater() == 'g') {
                        var x2 = x + 1
                    } else {
                        var x2 = x + 1.3
                    }
                } else {
                    var x2 = x + 0.7
                }

                vertexPositions.push([x1, y + 0.7, bottomLevel])       //
                vertexPositions.push([x2, y + 0.7, bottomLevel])       //  FIRST TRIANGLE
                vertexPositions.push([x, y + 1, 0])                    //

                vertexPositions.push([x + 1, y + 1, 0])                //
                vertexPositions.push([x, y + 1, 0])                    //  SECOND TRIANGLE
                vertexPositions.push([x2, y + 0.7, bottomLevel])       //
            }

            return vertexPositions
        },
        createWaterVertexPositionsDown = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1]


                if (Fields.get(x - 1, y, 1).getGrassOrWater() == 'w') {
                    if (Fields.get(x - 1, y - 1, 1).getGrassOrWater() == 'g') {
                        var x1 = x
                    } else {
                        var x1 = x - 0.3
                    }
                } else {
                    var x1 = x + 0.3
                }

                if (Fields.get(x + 1, y, 1).getGrassOrWater() == 'w') {
                    if (Fields.get(x + 1, y - 1, 1).getGrassOrWater() == 'g') {
                        var x2 = x + 1
                    } else {
                        var x2 = x + 1.3
                    }
                } else {
                    var x2 = x + 0.7
                }

                vertexPositions.push([x, y, 0])                        //
                vertexPositions.push([x + 1, y, 0])                    //  FIRST TRIANGLE
                vertexPositions.push([x1, y + 0.3, bottomLevel])       //

                vertexPositions.push([x2, y + 0.3, bottomLevel])       //
                vertexPositions.push([x1, y + 0.3, bottomLevel])       //  SECOND TRIANGLE
                vertexPositions.push([x + 1, y, 0])                    //
            }

            return vertexPositions
        },
        createWaterVertexPositionsLeft = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1]


                if (Fields.get(x, y - 1, 1).getGrassOrWater() == 'w') {
                    if (Fields.get(x + 1, y - 1, 1).getGrassOrWater() == 'g') {
                        var y1 = y
                    } else {
                        var y1 = y - 0.3
                    }
                } else {
                    var y1 = y + 0.3
                }

                if (Fields.get(x, y + 1, 1).getGrassOrWater() == 'w') {
                    if (Fields.get(x + 1, y + 1, 1).getGrassOrWater() == 'g') { // w|g
                        var y2 = y + 1
                    } else {                                                    // w|w
                        var y2 = y + 1.3
                    }
                } else {                                                        // g
                    var y2 = y + 0.7
                }

                vertexPositions.push([x + 0.7, y1, bottomLevel])       //
                vertexPositions.push([x + 1, y, 0])                    //  FIRST TRIANGLE
                vertexPositions.push([x + 0.7, y2, bottomLevel])       //

                vertexPositions.push([x + 1, y + 1, 0])                //
                vertexPositions.push([x + 0.7, y2, bottomLevel])       //  SECOND TRIANGLE
                vertexPositions.push([x + 1, y, 0])                    //
            }

            return vertexPositions
        },
        createWaterVertexPositionsRight = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1]


                if (Fields.get(x, y - 1, 1).getGrassOrWater() == 'w') {
                    if (Fields.get(x - 1, y - 1, 1).getGrassOrWater() == 'g') {  // g|w
                        var y1 = y
                    } else {                                                     // w|w
                        var y1 = y - 0.3
                    }
                } else {                                                         // g
                    var y1 = y + 0.3
                }

                if (Fields.get(x, y + 1, 1).getGrassOrWater() == 'w') {
                    if (Fields.get(x - 1, y + 1, 1).getGrassOrWater() == 'g') { // g|w
                        var y2 = y + 1
                    } else {                                                    // w|w
                        var y2 = y + 1.3
                    }
                } else {                                                        // g
                    var y2 = y + 0.7
                }

                vertexPositions.push([x + 0.3, y1, bottomLevel])       //
                vertexPositions.push([x, y, 0])                        //  FIRST TRIANGLE
                vertexPositions.push([x + 0.3, y2, bottomLevel])       //

                vertexPositions.push([x, y + 1, 0])                    //
                vertexPositions.push([x + 0.3, y2, bottomLevel])       //  SECOND TRIANGLE
                vertexPositions.push([x, y, 0])                        //
            }

            return vertexPositions
        },
        createHillVertexPositionsUp = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1]


                if (Fields.get(x - 1, y, 1).getHill()) {
                    if (!Fields.get(x - 1, y + 1, 1).getHill()) {
                        var x1 = x
                    } else {
                        var x1 = x - 0.3
                    }
                } else {
                    var x1 = x + 0.3
                }

                if (Fields.get(x + 1, y, 1).getHill()) {
                    if (!Fields.get(x + 1, y + 1, 1).getHill()) {
                        var x2 = x + 1
                    } else {
                        var x2 = x + 1.3
                    }
                } else {
                    var x2 = x + 0.7
                }

                vertexPositions.push([x1, y + 0.7, hillLevel])       //
                vertexPositions.push([x2, y + 0.7, hillLevel])       //  FIRST TRIANGLE
                vertexPositions.push([x, y + 1, 0])                    //

                vertexPositions.push([x + 1, y + 1, 0])                //
                vertexPositions.push([x, y + 1, 0])                    //  SECOND TRIANGLE
                vertexPositions.push([x2, y + 0.7, hillLevel])       //
            }

            return vertexPositions
        },
        createHillVertexPositionsDown = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1]


                if (Fields.get(x - 1, y, 1).getHill()) {
                    if (!Fields.get(x - 1, y - 1, 1).getHill()) {
                        var x1 = x
                    } else {
                        var x1 = x - 0.3
                    }
                } else {
                    var x1 = x + 0.3
                }

                if (Fields.get(x + 1, y, 1).getHill()) {
                    if (!Fields.get(x + 1, y - 1, 1).getHill()) {
                        var x2 = x + 1
                    } else {
                        var x2 = x + 1.3
                    }
                } else {
                    var x2 = x + 0.7
                }

                vertexPositions.push([x, y, 0])                        //
                vertexPositions.push([x + 1, y, 0])                    //  FIRST TRIANGLE
                vertexPositions.push([x1, y + 0.3, hillLevel])       //

                vertexPositions.push([x2, y + 0.3, hillLevel])       //
                vertexPositions.push([x1, y + 0.3, hillLevel])       //  SECOND TRIANGLE
                vertexPositions.push([x + 1, y, 0])                    //
            }

            return vertexPositions
        },
        createHillVertexPositionsLeft = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1]


                if (Fields.get(x, y - 1, 1).getHill()) {
                    if (!Fields.get(x + 1, y - 1, 1).getHill()) {
                        var y1 = y
                    } else {
                        var y1 = y - 0.3
                    }
                } else {
                    var y1 = y + 0.3
                }

                if (Fields.get(x, y + 1, 1).getHill()) {
                    if (!Fields.get(x + 1, y + 1, 1).getHill()) { // w|g
                        var y2 = y + 1
                    } else {                                                    // w|w
                        var y2 = y + 1.3
                    }
                } else {                                                        // g
                    var y2 = y + 0.7
                }

                vertexPositions.push([x + 0.7, y1, hillLevel])       //
                vertexPositions.push([x + 1, y, 0])                    //  FIRST TRIANGLE
                vertexPositions.push([x + 0.7, y2, hillLevel])       //

                vertexPositions.push([x + 1, y + 1, 0])                //
                vertexPositions.push([x + 0.7, y2, hillLevel])       //  SECOND TRIANGLE
                vertexPositions.push([x + 1, y, 0])                    //
            }

            return vertexPositions
        },
        createHillVertexPositionsRight = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1]


                if (Fields.get(x, y - 1, 1).getHill()) {
                    if (!Fields.get(x - 1, y - 1, 1).getHill()) {  // g|w
                        var y1 = y
                    } else {                                                     // w|w
                        var y1 = y - 0.3
                    }
                } else {                                                         // g
                    var y1 = y + 0.3
                }

                if (Fields.get(x, y + 1, 1).getHill()) {
                    if (!Fields.get(x - 1, y + 1, 1).getHill()) { // g|w
                        var y2 = y + 1
                    } else {                                                    // w|w
                        var y2 = y + 1.3
                    }
                } else {                                                        // g
                    var y2 = y + 0.7
                }

                vertexPositions.push([x + 0.3, y1, hillLevel])       //
                vertexPositions.push([x, y, 0])                        //  FIRST TRIANGLE
                vertexPositions.push([x + 0.3, y2, hillLevel])       //

                vertexPositions.push([x, y + 1, 0])                    //
                vertexPositions.push([x + 0.3, y2, hillLevel])       //  SECOND TRIANGLE
                vertexPositions.push([x, y, 0])                        //
            }

            return vertexPositions
        },
        createHillVertexPositions = function (x, y) {
            var vertexPositions = []

            for (var yy = 0; yy < y; yy++) {
                for (var xx = 0; xx < x; xx++) {
                    if (!Fields.get(xx, yy).getHill()) {
                        continue
                    }

                    var x1 = xx,
                        x2 = xx + 1,
                        x3 = xx,
                        x4 = xx + 1,
                        y1 = yy,
                        y2 = yy,
                        y3 = yy + 1,
                        y4 = yy + 1

                    if (!Fields.get(xx, yy - 1, 1).getHill()) { // above
                        y1 = y1 + 0.3
                        y2 = y2 + 0.3
                    }
                    if (!Fields.get(xx, yy + 1, 1).getHill()) { // under
                        y3 = y3 - 0.3
                        y4 = y4 - 0.3
                    }
                    if (!Fields.get(xx - 1, yy, 1).getHill()) { // left
                        x1 = x1 + 0.3
                        x3 = x3 + 0.3
                    }
                    if (!Fields.get(xx + 1, yy, 1).getHill()) { // right
                        x2 = x2 - 0.3
                        x4 = x4 - 0.3
                    }


                    if (!Fields.get(xx, yy - 1, 1).getHill() && Fields.get(xx + 1, yy, 1).getHill() && Fields.get(xx + 1, yy - 1, 1).getHill()) { // corner 1
                        x2 = x2 + 0.3
                    }
                    if (!Fields.get(xx - 1, yy - 1, 1).getHill() && Fields.get(xx, yy - 1, 1).getHill() && Fields.get(xx - 1, yy, 1).getHill()) { // corner 2
                        x1 = x1 + 0.3
                        y1 = y1 + 0.3
                    }
                    if (!Fields.get(xx - 1, yy, 1).getHill() && Fields.get(xx, yy + 1, 1).getHill() && Fields.get(xx - 1, yy + 1, 1).getHill()) { // corner 3
                        y3 = y3 + 0.3
                    }

                    if (!Fields.get(xx - 1, yy + 1, 1).getHill() && Fields.get(xx, yy + 1, 1).getHill() && Fields.get(xx - 1, yy, 1).getHill()) { // corner 4
                        x3 = x3 + 0.3
                        y3 = y3 - 0.3
                    }
                    if (!Fields.get(xx, yy + 1, 1).getHill() && Fields.get(xx + 1, yy + 1, 1).getHill() && Fields.get(xx + 1, yy, 1).getHill()) { // corner 5
                        x4 = x4 + 0.3
                    }
                    if (!Fields.get(xx - 1, yy, 1).getHill() && Fields.get(xx, yy - 1, 1).getHill() && Fields.get(xx - 1, yy - 1, 1).getHill()) { // corner 6
                        y1 = y1 - 0.3
                    }

                    if (!Fields.get(xx + 1, yy + 1, 1).getHill() && Fields.get(xx, yy + 1, 1).getHill() && Fields.get(xx + 1, yy, 1).getHill()) { // corner 7
                        x4 = x4 - 0.3
                        y4 = y4 - 0.3
                    }
                    if (!Fields.get(xx + 1, yy, 1).getHill() && Fields.get(xx, yy - 1, 1).getHill() && Fields.get(xx + 1, yy - 1, 1).getHill()) { // corner 8
                        y2 = y2 - 0.3
                    }
                    if (!Fields.get(xx, yy + 1, 1).getHill() && Fields.get(xx - 1, yy + 1, 1).getHill() && Fields.get(xx - 1, yy, 1).getHill()) { // corner 9
                        x3 = x3 - 0.3
                    }

                    if (!Fields.get(xx + 1, yy - 1, 1).getHill() && Fields.get(xx, yy - 1, 1).getHill() && Fields.get(xx + 1, yy, 1).getHill()) { // corner 10
                        x2 = x2 - 0.3
                        y2 = y2 + 0.3
                    }
                    if (!Fields.get(xx + 1, yy, 1).getHill() && Fields.get(xx, yy + 1, 1).getHill() && Fields.get(xx + 1, yy + 1, 1).getHill()) { // corner 11
                        y4 = y4 + 0.3
                    }
                    if (!Fields.get(xx, yy - 1, 1).getHill() && Fields.get(xx - 1, yy - 1, 1).getHill() && Fields.get(xx - 1, yy, 1).getHill()) { // corner 12
                        x1 = x1 - 0.3
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

            return vertexPositions
        },
        createMountainVertexPositionsUp = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1]


                if (Fields.get(x - 1, y, 1).getMountain()) {
                    if (!Fields.get(x - 1, y + 1, 1).getMountain()) {
                        var x1 = x
                    } else {
                        var x1 = x - 0.3
                    }
                } else {
                    var x1 = x + 0.3
                }

                if (Fields.get(x + 1, y, 1).getMountain()) {
                    if (!Fields.get(x + 1, y + 1, 1).getMountain()) {
                        var x2 = x + 1
                    } else {
                        var x2 = x + 1.3
                    }
                } else {
                    var x2 = x + 0.7
                }

                vertexPositions.push([x1, y + 0.7, mountainLevel])       //
                vertexPositions.push([x2, y + 0.7, mountainLevel])       //  FIRST TRIANGLE
                vertexPositions.push([x, y + 1, hillLevel])                    //

                vertexPositions.push([x + 1, y + 1, hillLevel])                //
                vertexPositions.push([x, y + 1, hillLevel])                    //  SECOND TRIANGLE
                vertexPositions.push([x2, y + 0.7, mountainLevel])       //
            }

            return vertexPositions
        },
        createMountainVertexPositionsDown = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1]


                if (Fields.get(x - 1, y, 1).getMountain()) {
                    if (!Fields.get(x - 1, y - 1, 1).getMountain()) {
                        var x1 = x
                    } else {
                        var x1 = x - 0.3
                    }
                } else {
                    var x1 = x + 0.3
                }

                if (Fields.get(x + 1, y, 1).getMountain()) {
                    if (!Fields.get(x + 1, y - 1, 1).getMountain()) {
                        var x2 = x + 1
                    } else {
                        var x2 = x + 1.3
                    }
                } else {
                    var x2 = x + 0.7
                }

                vertexPositions.push([x, y, hillLevel])                        //
                vertexPositions.push([x + 1, y, hillLevel])                    //  FIRST TRIANGLE
                vertexPositions.push([x1, y + 0.3, mountainLevel])       //

                vertexPositions.push([x2, y + 0.3, mountainLevel])       //
                vertexPositions.push([x1, y + 0.3, mountainLevel])       //  SECOND TRIANGLE
                vertexPositions.push([x + 1, y, hillLevel])                    //
            }

            return vertexPositions
        },
        createMountainVertexPositionsLeft = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1]


                if (Fields.get(x, y - 1, 1).getMountain()) {
                    if (!Fields.get(x + 1, y - 1, 1).getMountain()) {
                        var y1 = y
                    } else {
                        var y1 = y - 0.3
                    }
                } else {
                    var y1 = y + 0.3
                }

                if (Fields.get(x, y + 1, 1).getMountain()) {
                    if (!Fields.get(x + 1, y + 1, 1).getMountain()) { // w|g
                        var y2 = y + 1
                    } else {                                                    // w|w
                        var y2 = y + 1.3
                    }
                } else {                                                        // g
                    var y2 = y + 0.7
                }

                vertexPositions.push([x + 0.7, y1, mountainLevel])       //
                vertexPositions.push([x + 1, y, hillLevel])                    //  FIRST TRIANGLE
                vertexPositions.push([x + 0.7, y2, mountainLevel])       //

                vertexPositions.push([x + 1, y + 1, hillLevel])                //
                vertexPositions.push([x + 0.7, y2, mountainLevel])       //  SECOND TRIANGLE
                vertexPositions.push([x + 1, y, hillLevel])                    //
            }

            return vertexPositions
        },
        createMountainVertexPositionsRight = function (stripes) {
            var vertexPositions = []

            for (var i in stripes) {
                var x = stripes[i][0],
                    y = stripes[i][1]


                if (Fields.get(x, y - 1, 1).getMountain()) {
                    if (!Fields.get(x - 1, y - 1, 1).getMountain()) {  // g|w
                        var y1 = y
                    } else {                                                     // w|w
                        var y1 = y - 0.3
                    }
                } else {                                                         // g
                    var y1 = y + 0.3
                }

                if (Fields.get(x, y + 1, 1).getMountain()) {
                    if (!Fields.get(x - 1, y + 1, 1).getMountain()) { // g|w
                        var y2 = y + 1
                    } else {                                                    // w|w
                        var y2 = y + 1.3
                    }
                } else {                                                        // g
                    var y2 = y + 0.7
                }

                vertexPositions.push([x + 0.3, y1, mountainLevel])       //
                vertexPositions.push([x, y, hillLevel])                        //  FIRST TRIANGLE
                vertexPositions.push([x + 0.3, y2, mountainLevel])       //

                vertexPositions.push([x, y + 1, hillLevel])                    //
                vertexPositions.push([x + 0.3, y2, mountainLevel])       //  SECOND TRIANGLE
                vertexPositions.push([x, y, hillLevel])                        //
            }

            return vertexPositions
        },
        createMountainVertexPositions = function (x, y) {
            var vertexPositions = []

            for (var yy = 0; yy < y; yy++) {
                for (var xx = 0; xx < x; xx++) {
                    if (!Fields.get(xx, yy).getMountain()) {
                        continue
                    }

                    var x1 = xx,
                        x2 = xx + 1,
                        x3 = xx,
                        x4 = xx + 1,
                        y1 = yy,
                        y2 = yy,
                        y3 = yy + 1,
                        y4 = yy + 1

                    if (!Fields.get(xx, yy - 1, 1).getMountain()) { // above
                        y1 = y1 + 0.3
                        y2 = y2 + 0.3
                    }
                    if (!Fields.get(xx, yy + 1, 1).getMountain()) { // under
                        y3 = y3 - 0.3
                        y4 = y4 - 0.3
                    }
                    if (!Fields.get(xx - 1, yy, 1).getMountain()) { // left
                        x1 = x1 + 0.3
                        x3 = x3 + 0.3
                    }
                    if (!Fields.get(xx + 1, yy, 1).getMountain()) { // right
                        x2 = x2 - 0.3
                        x4 = x4 - 0.3
                    }


                    if (!Fields.get(xx, yy - 1, 1).getMountain() && Fields.get(xx + 1, yy, 1).getMountain() && Fields.get(xx + 1, yy - 1, 1).getMountain()) { // corner 1
                        x2 = x2 + 0.3
                    }
                    if (!Fields.get(xx - 1, yy - 1, 1).getMountain() && Fields.get(xx, yy - 1, 1).getMountain() && Fields.get(xx - 1, yy, 1).getMountain()) { // corner 2
                        x1 = x1 + 0.3
                        y1 = y1 + 0.3
                    }
                    if (!Fields.get(xx - 1, yy, 1).getMountain() && Fields.get(xx, yy + 1, 1).getMountain() && Fields.get(xx - 1, yy + 1, 1).getMountain()) { // corner 3
                        y3 = y3 + 0.3
                    }

                    if (!Fields.get(xx - 1, yy + 1, 1).getMountain() && Fields.get(xx, yy + 1, 1).getMountain() && Fields.get(xx - 1, yy, 1).getMountain()) { // corner 4
                        x3 = x3 + 0.3
                        y3 = y3 - 0.3
                    }
                    if (!Fields.get(xx, yy + 1, 1).getMountain() && Fields.get(xx + 1, yy + 1, 1).getMountain() && Fields.get(xx + 1, yy, 1).getMountain()) { // corner 5
                        x4 = x4 + 0.3
                    }
                    if (!Fields.get(xx - 1, yy, 1).getMountain() && Fields.get(xx, yy - 1, 1).getMountain() && Fields.get(xx - 1, yy - 1, 1).getMountain()) { // corner 6
                        y1 = y1 - 0.3
                    }

                    if (!Fields.get(xx + 1, yy + 1, 1).getMountain() && Fields.get(xx, yy + 1, 1).getMountain() && Fields.get(xx + 1, yy, 1).getMountain()) { // corner 7
                        x4 = x4 - 0.3
                        y4 = y4 - 0.3
                    }
                    if (!Fields.get(xx + 1, yy, 1).getMountain() && Fields.get(xx, yy - 1, 1).getMountain() && Fields.get(xx + 1, yy - 1, 1).getMountain()) { // corner 8
                        y2 = y2 - 0.3
                    }
                    if (!Fields.get(xx, yy + 1, 1).getMountain() && Fields.get(xx - 1, yy + 1, 1).getMountain() && Fields.get(xx - 1, yy, 1).getMountain()) { // corner 9
                        x3 = x3 - 0.3
                    }

                    if (!Fields.get(xx + 1, yy - 1, 1).getMountain() && Fields.get(xx, yy - 1, 1).getMountain() && Fields.get(xx + 1, yy, 1).getMountain()) { // corner 10
                        x2 = x2 - 0.3
                        y2 = y2 + 0.3
                    }
                    if (!Fields.get(xx + 1, yy, 1).getMountain() && Fields.get(xx, yy + 1, 1).getMountain() && Fields.get(xx + 1, yy + 1, 1).getMountain()) { // corner 11
                        y4 = y4 + 0.3
                    }
                    if (!Fields.get(xx, yy - 1, 1).getMountain() && Fields.get(xx - 1, yy - 1, 1).getMountain() && Fields.get(xx - 1, yy, 1).getMountain()) { // corner 12
                        x1 = x1 - 0.3
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

            return vertexPositions
        },
        createGrassVertexPositions = function (stripesArray) {
            var vertexPositions = []
            for (var yyy in stripesArray) {
                var stripes = stripesArray[yyy].get()
                // console.log(stripes)

                for (var i in stripes) {
                    var field = stripes[i]


                    vertexPositions.push([field.start, yyy * 1, 0])           //
                    vertexPositions.push([field.end, yyy * 1, 0])             //  FIRST TRIANGLE
                    vertexPositions.push([field.start, yyy * 1 + 1, 0])       //

                    vertexPositions.push([field.end, yyy * 1 + 1, 0])         //
                    vertexPositions.push([field.start, yyy * 1 + 1, 0])       //  SECOND TRIANGLE
                    vertexPositions.push([field.end, yyy * 1, 0])             //
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
                        if (!end) {
                            stripes.add(startX, xx)
                            start = 0
                            end = 1
                        }
                    }
                }

                if (start && !end) {
                    stripes.add(startX, xx)
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
        createMesh = function (geometry) {
            var material = new THREE.MeshLambertMaterial({
                color: '#ffffff',
                side: THREE.DoubleSide
            })

            if (!geometry) {
                return
            }

            var mesh = new THREE.Mesh(geometry, material)
            mesh.rotation.x = Math.PI / 2
            GameScene.add(mesh)


            var geo = new THREE.WireframeGeometry(mesh.geometry),
                mat = new THREE.LineBasicMaterial({color: 0x00ff00, linewidth: 1}),
                wireframe = new THREE.LineSegments(geo, mat)
            mesh.add(wireframe)
        }
    this.init = function (x, y) {
        var stripesArray = createGrassStripes(x, y),
            vertexPositions = createGrassVertexPositions(stripesArray),
            uvs = createUVS(new Float32Array(vertexPositions.length * 2), stripesArray, x, y)
        createMesh(createGeometry(vertexPositions, uvs))

        var stripes = createWaterStripes(x, y),
            vertexPositionsUp = createWaterVertexPositionsUp(stripes.getUp()),
            vertexPositionsDown = createWaterVertexPositionsDown(stripes.getDown()),
            vertexPositionsLeft = createWaterVertexPositionsLeft(stripes.getLeft()),
            vertexPositionsRight = createWaterVertexPositionsRight(stripes.getRight())

        createMesh(createGeometry(vertexPositionsUp))
        createMesh(createGeometry(vertexPositionsDown))
        createMesh(createGeometry(vertexPositionsLeft))
        createMesh(createGeometry(vertexPositionsRight))

        var stripes = createHillStripes(x, y),
            vertexPositionsUp = createHillVertexPositionsUp(stripes.getUp()),
            vertexPositionsDown = createHillVertexPositionsDown(stripes.getDown()),
            vertexPositionsLeft = createHillVertexPositionsLeft(stripes.getLeft()),
            vertexPositionsRight = createHillVertexPositionsRight(stripes.getRight())

        createMesh(createGeometry(vertexPositionsUp))
        createMesh(createGeometry(vertexPositionsDown))
        createMesh(createGeometry(vertexPositionsLeft))
        createMesh(createGeometry(vertexPositionsRight))

        createMesh(createGeometry(createHillVertexPositions(x, y)))

        var stripes = createMountainStripes(x, y),
            vertexPositionsUp = createMountainVertexPositionsUp(stripes.getUp()),
            vertexPositionsDown = createMountainVertexPositionsDown(stripes.getDown()),
            vertexPositionsLeft = createMountainVertexPositionsLeft(stripes.getLeft()),
            vertexPositionsRight = createMountainVertexPositionsRight(stripes.getRight())

        createMesh(createGeometry(vertexPositionsUp))
        createMesh(createGeometry(vertexPositionsDown))
        createMesh(createGeometry(vertexPositionsLeft))
        createMesh(createGeometry(vertexPositionsRight))

        createMesh(createGeometry(createMountainVertexPositions(x, y)))
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