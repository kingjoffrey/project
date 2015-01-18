var Three = new function () {
    var scene = new THREE.Scene()

    var camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 1000)
    camera.rotation.order = 'YXZ';
    camera.rotation.y = -Math.PI / 4;
    camera.rotation.x = Math.atan(-1 / Math.sqrt(2));
    camera.position.set(0, 52, 0)
    camera.scale.addScalar(1);

    this.getCamera = function () {
        return camera
    }

    var renderer = new THREE.WebGLRenderer({antialias: true})
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.shadowMapEnabled = true
    renderer.shadowMapSoft = false

    //renderer.shadowCameraNear = 3;
    //renderer.shadowCameraFar = camera.far;
    //renderer.shadowCameraFov = 50;
    //
    //renderer.shadowMapBias = 0.0039;
    //renderer.shadowMapDarkness = 0.5;
    //renderer.shadowMapWidth = 1024;
    //renderer.shadowMapHeight = 1024;

    this.getRenderer = function () {
        return renderer
    }

    var ground = new THREE.Mesh(new THREE.PlaneBufferGeometry(436, 624), new THREE.MeshLambertMaterial({map: THREE.ImageUtils.loadTexture('/img/maps/1.png')}));
    //var ground = new THREE.Mesh(new THREE.PlaneBufferGeometry(436, 624), new THREE.MeshLambertMaterial({color: 0x00dd00}));
    ground.rotation.x = -Math.PI / 2; //-90 degrees around the x axis
    ground.receiveShadow = true;
    scene.add(ground);

    //var ambientLight = new THREE.AmbientLight(0x111111);
    //scene.add(ambientLight)

    var pointLight = new THREE.PointLight(0xddddDD);
    pointLight.position.set(-100000, 100000, 100000);
    scene.add(pointLight)

    //var light = new THREE.DirectionalLight(0xffffff, 1);
    //light.castShadow = true
    //light.shadowDarkness = 1
    //light.shadowCameraVisible = true
    //light.shadowCameraNear = 1
    //light.shadowCameraFar = 600
    //light.shadowCameraLeft = -350; // CHANGED
    //light.shadowCameraRight = 350; // CHANGED
    //light.shadowCameraTop = 300; // CHANGED
    //light.shadowCameraBottom = -300; // CHANGED
    //light.position.set(100, 100, -100); // CHANGED
    //
    //scene.add(light);
    //scene.add(new THREE.DirectionalLightHelper(light, 0.2))

    //var spotLight = new THREE.SpotLight(0xffffff);
    //spotLight.castShadow = true;
    //spotLight.shadowCameraFov = Math.PI
    //spotLight.shadowBias = 0.0001;
    //spotLight.shadowDarkness = 0.2;
    //spotLight.shadowMapWidth = 2048
    //spotLight.shadowMapHeight = 2048

    //Lighting
    var theLight = new THREE.DirectionalLight(0xffffff, 1);
    theLight.position.set(1500, 1000, 1000);
    theLight.castShadow = true;
    theLight.shadowDarkness = 0.3
    theLight.shadowMapWidth = 8192
    theLight.shadowMapHeight = 8192
    scene.add(theLight);

    var loader = new THREE.JSONLoader();

    this.loadArmy = function (id, x, y, img) {
        var mesh = new THREE.Mesh(new THREE.PlaneBufferGeometry(2.2, 2.8), new THREE.MeshBasicMaterial({map: THREE.ImageUtils.loadTexture(img)}))
        mesh.rotation.y = -Math.PI / 4;
        //var scale = 1.5
        //mesh.scale.set(scale, scale, scale);
        mesh.position.set(x * 4 - 216, 1.5, y * 4 - 311);
        mesh.name = 'army'
        mesh.identification = id
        scene.add(mesh);
        EventsControls.attach(mesh);
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

                        mesh.castShadow = true;
                        mesh.receiveShadow = true;

                        scene.add(mesh);
                    }
                }
            }
        })
        loader.load('/models/hill.json', function (geometry) {
            var scale = 0.7
            var material = new THREE.MeshLambertMaterial({color: '#00a000'})
            for (var y in game.fields) {
                for (var x in game.fields[y]) {
                    if (game.fields[y][x] == 'h') {
                        var mesh = new THREE.Mesh(geometry, material)
                        mesh.scale.set(scale, scale, scale)
                        mesh.position.set(x * 4 - 216, 0, y * 4 - 311)
                        mesh.rotation.y = Math.PI * Math.random()

                        //mesh.castShadow = true
                        mesh.receiveShadow = true

                        scene.add(mesh)
                    }
                }
            }
        })
        loader.load('/models/tree.json', function (geometry) {
            var scale = 0.4
            var material = new THREE.MeshLambertMaterial({color: '#008000'})
            for (var y in game.fields) {
                for (var x in game.fields[y]) {
                    if (game.fields[y][x] == 'f') {
                        var mesh = new THREE.Mesh(geometry, material)
                        mesh.scale.set(scale, scale, scale)
                        mesh.position.set(x * 4 - 216, 0, y * 4 - 311)
                        mesh.rotation.y = Math.PI * Math.random()

                        mesh.castShadow = true
                        mesh.receiveShadow = true

                        scene.add(mesh)
                    }
                }
            }
        })

        //var material = new THREE.MeshLambertMaterial({color: '#008000'})
        //for (var y in game.fields) {
        //    for (var x in game.fields[y]) {
        //        if (game.fields[y][x] == 'g') {
        //            var mesh = new THREE.Mesh(new THREE.PlaneBufferGeometry(4, 4), material)
        //            mesh.position.set(x * 4 - 216, 0, y * 4 - 311)
        //            mesh.rotation.x = -Math.PI / 2
        //
        //            mesh.receiveShadow = true
        //
        //            scene.add(mesh)
        //        }
        //    }
        //}
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

                    mesh.castShadow = true
                    mesh.receiveShadow = true

                    scene.add(mesh);
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

                    mesh.castShadow = true;
                    mesh.receiveShadow = true;

                    scene.add(mesh);
                }
            }
        })
    }
    this.loadRuins = function () {
        loader.load('/models/ruin.json', function (geometry) {
            var scale = 0.3
            var material = new THREE.MeshLambertMaterial({color: '#808080'})
            for (var ruinId in game.ruins) {
                var mesh = new THREE.Mesh(geometry, material);
                mesh.scale.set(scale, scale, scale);
                mesh.position.set(game.ruins[ruinId].x * 4 - 216, 0, game.ruins[ruinId].y * 4 - 311);
                //mesh.rotation.y = Math.PI * Math.random()

                mesh.castShadow = true;
                mesh.receiveShadow = true;

                scene.add(mesh);
            }
        })
    }

    this.init = function () {
        //var controls = new THREE.OrbitControls( camera, renderer.domElement )
        $('#game').append(renderer.domElement)
        Three.loadFields()
        render()
    }
    var render = function () {
        requestAnimationFrame(render);
        renderer.render(scene, camera);
    };
}

var EventsControls = new EventsControls(Three.getCamera(), Three.getRenderer().domElement);
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

