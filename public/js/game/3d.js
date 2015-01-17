var Three = new function () {
    this.scene = new THREE.Scene()

    this.camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 1000)
    this.camera.position.set(20, 52, 20);
    this.camera.rotation.order = 'YXZ';
    this.camera.rotation.y = -Math.PI / 4;
    this.camera.rotation.x = Math.atan(-1 / Math.sqrt(2));
    this.camera.scale.addScalar(1);

    this.renderer = new THREE.WebGLRenderer()
    this.renderer.setSize(window.innerWidth, window.innerHeight);

    var ground = new THREE.Mesh(new THREE.PlaneBufferGeometry(436, 624), new THREE.MeshBasicMaterial({map: THREE.ImageUtils.loadTexture('/img/maps/1.png')}));
    ground.rotation.x = -Math.PI / 2; //-90 degrees around the x axis

    this.scene.add(ground);

    var light = new THREE.PointLight(0xFFFFDD);
    light.position.set(-1000, 1000, 1000);
    this.scene.add(light);

    var loader = new THREE.JSONLoader();
    this.loadCastle = function (color, castle) {
        loader.load('/models/castle.json', Three.addCastle(color, castle.id, castle.x, castle.y))
    }
    this.loadTower = function (color, tower) {
        loader.load('/models/tower.json', Three.getGeomHandler(color, tower.x * 4 - 216, tower.y * 4 - 311, 0.7))
    }
    this.loadRuin = function (ruin) {
        loader.load('/models/ruin.json', Three.getGeomHandler('neutral', ruin.x * 4 - 216, ruin.y * 4 - 311, 0.3))
    }
    this.loadArmy = function (id, x, y, img) {
        var mesh = new THREE.Mesh(new THREE.PlaneBufferGeometry(2.2, 2.8), new THREE.MeshBasicMaterial({map: THREE.ImageUtils.loadTexture(img)}))
        mesh.rotation.y = -Math.PI / 4;
        //var scale = 1.5
        //mesh.scale.set(scale, scale, scale);
        mesh.position.set(x * 4 - 216, 1.5, y * 4 - 311);
        mesh.name = 'army'
        mesh.identification = id
        Three.scene.add(mesh);
        EventsControls.attach(mesh);
    }
    this.loadMountain = function (x, y) {
        loader.load('/models/mountain.json', Three.getGeomHandler('#808080', x * 4 - 216, y * 4 - 311, 1))
    }
    this.loadHill = function (x, y) {
        loader.load('/models/hill.json', Three.getGeomHandler('#008000', x * 4 - 216, y * 4 - 311, 0.6))
    }
    this.loadForest = function (x, y) {
        loader.load('/models/tree.json', Three.getGeomHandler('#008000', x * 4 - 216, y * 4 - 311, 0.3))
    }
    this.loadFields = function () {
        loader.load('/models/mountain.json', function (geometry) {
            var scale = 1
            var material = new THREE.MeshLambertMaterial({color: '#808080'})
            for (var y in game.fields) {
                for (var x in game.fields[y]) {
                    if (game.fields[y][x] == 'm') {
                        var mesh = new THREE.Mesh(geometry, material);
                        mesh.scale.set(scale, scale, scale);
                        mesh.position.set(x * 4 - 216, 0, y * 4 - 311);
                        mesh.rotation.y = Math.PI * Math.random()
                        Three.scene.add(mesh);
                    }
                }
            }
        })
        loader.load('/models/hill.json', function (geometry) {
            var scale = 0.7
            var material = new THREE.MeshLambertMaterial({color: '#00d000'})
            for (var y in game.fields) {
                for (var x in game.fields[y]) {
                    if (game.fields[y][x] == 'h') {
                        var mesh = new THREE.Mesh(geometry, material)
                        mesh.scale.set(scale, scale, scale)
                        mesh.position.set(x * 4 - 216, 0, y * 4 - 311)
                        mesh.rotation.y = Math.PI * Math.random()
                        Three.scene.add(mesh)
                    }
                }
            }
        })
        loader.load('/models/tree.json', function (geometry) {
            var scale = 0.4
            var material = new THREE.MeshLambertMaterial({color: '#008000'})
            var i = 0
            for (var y in game.fields) {
                for (var x in game.fields[y]) {
                    if (game.fields[y][x] == 'f') {
                        var mesh = new THREE.Mesh(geometry, material)
                        mesh.scale.set(scale, scale, scale)
                        mesh.position.set(x * 4 - 216, 0, y * 4 - 311)
                        mesh.rotation.y = Math.PI * Math.random()
                        Three.scene.add(mesh)
                    }
                }
            }
        })
    }
    this.addCastle = function (color, castleId, x, y) {
        return function (geometry) {
            var scale = 0.5
            var mesh = new THREE.Mesh(geometry, new THREE.MeshLambertMaterial({color: game.players[color].backgroundColor}));
            mesh.scale.set(scale, scale, scale);
            mesh.position.set(x * 4 - 214, 0, y * 4 - 309);

            mesh.name = 'castle'
            mesh.identification = castleId

            Three.scene.add(mesh);
            EventsControls.attach(mesh);
        };
    }
    this.loadCastles = function () {
        loader.load('/models/castle.json', function (geometry) {
            var scale = 0.5
            for (var color in game.players) {
                var material = new THREE.MeshLambertMaterial({color: game.players[color].backgroundColor})
                for (var castleId in game.players[color].castles) {
                    var mesh = new THREE.Mesh(geometry, material);
                    mesh.scale.set(scale, scale, scale);
                    mesh.position.set(game.players[color].castles[castleId].x * 4 - 214, 0, game.players[color].castles[castleId].y * 4 - 309);

                    mesh.name = 'castle'
                    mesh.identification = castleId

                    Three.scene.add(mesh);
                    EventsControls.attach(mesh);
                }
            }
        })
    }
    this.loadTowers = function () {
        loader.load('/models/tower.json', function (geometry) {
            var scale = 1
            for (var color in game.players) {
                var material = new THREE.MeshLambertMaterial({color: game.players[color].backgroundColor})
                for (var towerId in game.players[color].towers) {
                    var mesh = new THREE.Mesh(geometry, material);
                    mesh.scale.set(scale, scale, scale);
                    mesh.position.set(game.players[color].towers[towerId].x * 4 - 216, 0, game.players[color].towers[towerId].y * 4 - 311);
                    mesh.rotation.y = Math.PI / 2
                    Three.scene.add(mesh);
                }
            }
        })
    }

    this.getGeomHandler = function (color, x, y, scale) {
        return function (geometry) {
            var mesh = new THREE.Mesh(geometry, new THREE.MeshLambertMaterial({color: game.players[color].backgroundColor}));
            mesh.scale.set(scale, scale, scale);
            mesh.position.set(x, 0, y);
            Three.scene.add(mesh);
        };
    }

    this.init = function () {
        $('#game').append(Three.renderer.domElement);
        Three.loadFields()
        Three.render();
    }
    this.render = function () {
        requestAnimationFrame(Three.render);
        Three.renderer.render(Three.scene, Three.camera);
    };
}

var EventsControls = new EventsControls(Three.camera, Three.renderer.domElement);
EventsControls.displacing = false;
EventsControls.onclick = function () {
    switch (this.focused.name) {
        case 'castle':
            Message.castle(this.focused.identification)
            break
        case 'army':
            Army.select(game.players[game.me.color].armies[this.focused.identification])
            break
    }
}
