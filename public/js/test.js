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
        createUVS = function (uvs, maxX, maxY) {
            var uv = []
            for (var u = 0; u < maxX; u++) {
                uv[u] = []
                for (var v = 0; v < maxY; v++) {
                    uv[u][v] = []
                    uv[u][v][0] = [u / maxX, v / maxY]
                    uv[u][v][1] = [(u + 1) / maxX, v / maxY]
                    uv[u][v][2] = [u / maxX, (v + 1) / maxY]
                    uv[u][v][3] = [(u + 1) / maxX, (v + 1) / maxY]
                }
            }

            var k = 0
            for (var u = 0; u < maxX; u++) {
                for (var v = 0; v < maxY; v++) {
                    // first triangle
                    uvs[0 + k] = uv[u][v][0][0]
                    uvs[1 + k] = uv[u][v][0][1]
                    uvs[2 + k] = uv[u][v][1][0]
                    uvs[3 + k] = uv[u][v][1][1]
                    uvs[4 + k] = uv[u][v][2][0]
                    uvs[5 + k] = uv[u][v][2][1]
                    // second triangle
                    uvs[6 + k] = uv[u][v][3][0]
                    uvs[7 + k] = uv[u][v][3][1]
                    uvs[8 + k] = uv[u][v][2][0]
                    uvs[9 + k] = uv[u][v][2][1]
                    uvs[10 + k] = uv[u][v][1][0]
                    uvs[11 + k] = uv[u][v][1][1]
                    k += 12
                }
            }

            return uvs
        },
        field = function (x, y, z) {
            return [
                {'x': x, 'y': y, 'z': z},
                {'x': x + 1, 'y': y, 'z': z},
                {'x': x + 2, 'y': y, 'z': z},
                {'x': x, 'y': y + 1, 'z': z},
                {'x': x + 1, 'y': y + 1, 'z': z},
                {'x': x + 2, 'y': y + 1, 'z': z},
                {'x': x, 'y': y + 2, 'z': z},
                {'x': x + 1, 'y': y + 2, 'z': z},
                {'x': x + 2, 'y': y + 2, 'z': z}
            ]

        },


        createGrass = function (x, y) {
            var fields = {}

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
                            stripes.add(startX, xx - 1)
                            start = 0
                            end = 1
                        }
                    }
                }

                if (start && !end) {
                    stripes.add(startX, xx - 1)
                }

                fields[yy] = stripes
            }

            return fields
        },
        createVertexPositions = function (fields) {
            var vertexPositions = []

            for (var yyy in fields) {
                var field = fields[yyy].get()
                vertexPositions.push([field.start, yyy, 0])           //
                vertexPositions.push([field.end, yyy, 0])             //  FIRST TRIANGLE
                vertexPositions.push([field.start, yyy + 1, 0])       //

                vertexPositions.push([field.end, yyy + 1, 0])         //
                vertexPositions.push([field.start, yyy + 1, 0])       //  SECOND TRIANGLE
                vertexPositions.push([field.end, yyy, 0])             //
            }

            return vertexPositions
        },


        createGeometry = function (x, y) {
            var maxX = x * 2,
                maxY = y * 2,
                geometry = new THREE.BufferGeometry()

            var vertexPositions = createVertexPositions(x, y)

            return

            var vertices = new Float32Array(vertexPositions.length * 3),
                normals = new Float32Array(vertexPositions.length * 3),
                uvs = new Float32Array(vertexPositions.length * 2)

            for (var i = 0; i < vertexPositions.length; i++) {
                var index = 3 * i
                vertices[index + 0] = vertexPositions[i][0]
                vertices[index + 1] = vertexPositions[i][1]
                vertices[index + 2] = vertexPositions[i][2]
            }

            geometry.addAttribute('position', new THREE.BufferAttribute(vertices, 3))
            geometry.addAttribute('normal', new THREE.BufferAttribute(normals, 3))
            geometry.addAttribute('uv', createUVS(new THREE.BufferAttribute(uvs, 2), maxX, maxY))

            geometry.computeVertexNormals()

            return geometry
        },
        createMesh = function (x, y) {
            var material = new THREE.MeshLambertMaterial({
                color: '#ffffff',
                side: THREE.DoubleSide
            })
            var geometry = createGeometry(x, y)

            if (!geometry) {
                return
            }

            var mesh = new THREE.Mesh(geometry, material)
            mesh.rotation.x = Math.PI / 2
            GameScene.add(mesh)


            var geo = new THREE.WireframeGeometry(mesh.geometry)
            var mat = new THREE.LineBasicMaterial({color: 0x000000, linewidth: 1})
            var wireframe = new THREE.LineSegments(geo, mat)

            mesh.add(wireframe)
        }
    this.init = function (x, y) {
        createMesh(x, y)
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