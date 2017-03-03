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

        GameScene.getCamera().position.x = -50
        GameScene.getCamera().position.y = 50
        GameScene.getCamera().position.z = 50

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
            ['g', 'g', 'g', 'g', 'g', 'g', 'g'],
            ['g', 'g', 'g', 'w', 'g', 'g', 'g'],
            ['g', 'g', 'w', 'w', 'w', 'g', 'g'],
            ['g', 'w', 'w', 'w', 'w', 'w', 'g'],
            ['g', 'g', 'w', 'w', 'w', 'g', 'g'],
            ['g', 'g', 'g', 'w', 'g', 'g', 'g'],
            ['g', 'g', 'g', 'g', 'g', 'g', 'g']
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
}
var Ground = new function () {
    var mountainLevel = 1.95,
        hillLevel = 0.9,
        bottomLevel = 2,
        waterLevel = 0.1,
        checkInnerTL = function (x, y) {
            if (Fields.get(x, y + 1, 1).getGrassOrWater() == 'g' && Fields.get(x - 1, y, 1).getGrassOrWater() == 'g') {
                return 1
            }
        },
        checkInnerTR = function (x, y) {
            if (Fields.get(x, y + 1, 1).getGrassOrWater() == 'g' && Fields.get(x + 1, y, 1).getGrassOrWater() == 'g') {
                return 1
            }
        },
        checkInnerBR = function (x, y) {
            if (Fields.get(x, y - 1, 1).getGrassOrWater() == 'g' && Fields.get(x + 1, y, 1).getGrassOrWater() == 'g') {
                return 1
            }
        },
        checkInnerBL = function (x, y) {
            if (Fields.get(x, y - 1, 1).getGrassOrWater() == 'g' && Fields.get(x - 1, y, 1).getGrassOrWater() == 'g') {
                return 1
            }
        },
        checkOuterTL = function (x, y) {
            if (Fields.get(x, y + 1, 1).getGrassOrWater() == 'w' && Fields.get(x + 1, y, 1).getGrassOrWater() == 'w' && Fields.get(x + 1, y + 1, 1).getGrassOrWater() == 'g') {
                return 1
            }
        },
        checkOuterTR = function (x, y) {
            if (Fields.get(x, y + 1, 1).getGrassOrWater() == 'w' && Fields.get(x - 1, y, 1).getGrassOrWater() == 'w' && Fields.get(x - 1, y + 1, 1).getGrassOrWater() == 'g') {
                return 1
            }
        },
        checkOuterBR = function (x, y) {
            if (Fields.get(x, y - 1, 1).getGrassOrWater() == 'w' && Fields.get(x - 1, y, 1).getGrassOrWater() == 'w' && Fields.get(x - 1, y - 1, 1).getGrassOrWater() == 'g') {
                return 1
            }
        },
        checkOuterBL = function (x, y) {
            if (Fields.get(x, y - 1, 1).getGrassOrWater() == 'w' && Fields.get(x + 1, y, 1).getGrassOrWater() == 'w' && Fields.get(x + 1, y - 1, 1).getGrassOrWater() == 'g') {
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
        createGrassVertexPositions = function (stripesArray) {
            var vertexPositions = []

            for (var yyy in stripesArray) {
                var stripes = stripesArray[yyy].get()

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
        createWaterVertexPositions = function (stripesArray) {
            var vertexPositions = []

            for (var yyy in stripesArray) {
                var stripes = stripesArray[yyy].get()

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
            var stripesArray = {}

            for (var yy = 0; yy < y; yy++) {

                var stripes = new Stripes()

                for (var xx = 0; xx < x; xx++) {
                    if (Fields.get(xx, yy).getGrassOrWater() == 'g') {
                        continue
                    }

                    if (checkInnerTR(xx, yy)) {
                        stripes.add(startX, xx)
                    } else if (checkInnerBR(xx, yy)) {
                        stripes.add(startX, xx)
                    } else if (checkOuterBR(xx, yy)) {
                        stripes.add(startX, xx)
                    } else if (checkOuterTR(xx, yy)) {
                        stripes.add(startX, xx)
                    }

                    if (checkInnerTL(xx, yy)) {
                        var startX = xx
                    } else if (checkInnerBL(xx, yy)) {
                        var startX = xx
                    } else if (checkOuterBL(xx, yy)) {
                        var startX = xx
                    } else if (checkOuterTL(xx, yy)) {
                        var startX = xx
                    }
                }

                stripesArray[yy] = stripes
            }


            return stripesArray
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
            geometry.addAttribute('uv', new THREE.BufferAttribute(uvs, 2))

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

        var stripesArray = createWaterStripes(x, y),
            vertexPositions = createWaterVertexPositions(stripesArray),
            uvs = createUVS(new Float32Array(vertexPositions.length * 2), stripesArray, x, y)

        createMesh(createGeometry(vertexPositions, uvs))
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