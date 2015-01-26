var Three = new function () {
    var ruinModel,
        towerModel,
        flagModel,
        castleModel,
        armyModel,
        mountainModel,
        hillModel,
        treeModel
    var scene = new THREE.Scene()

    this.getScene = function () {
        return scene
    }

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

    var pointLight = new THREE.PointLight(0xddddDD);
    pointLight.position.set(-100000, 100000, 100000);
    scene.add(pointLight)

    //Lighting
    var theLight = new THREE.DirectionalLight(0xffffff, 1)
    theLight.position.set(1500, 1000, 1000)
    theLight.castShadow = true
    theLight.shadowDarkness = 0.3
    theLight.shadowMapWidth = 8192
    theLight.shadowMapHeight = 8192
    scene.add(theLight);

    var loader = new THREE.JSONLoader()

    var initRuin = function () {
        ruinModel = loader.parse(ruin)
        ruinModel.material = new THREE.MeshPhongMaterial({color: '#8080a0'})
        ruinModel.material.side = THREE.DoubleSide
    }
    this.addRuin = function (x, y) {
        var mesh = new THREE.Mesh(ruinModel.geometry, ruinModel.material)
        var scale = 0.3

        mesh.scale.set(scale, scale, scale)
        mesh.position.set(x * 4 - 216, 0, y * 4 - 311)

        mesh.castShadow = true
        mesh.receiveShadow = true

        scene.add(mesh)

        return mesh.id
    }

    var initTower = function () {
        towerModel = loader.parse(tower)
        towerModel.material = new THREE.MeshLambertMaterial({color: 0xa0a0a0})
        towerModel.material.side = THREE.DoubleSide
        flagModel = loader.parse(flag)
    }
    this.addTower = function (x, y, color) {
        var material = new THREE.MeshLambertMaterial({color: color})
        material.side = THREE.DoubleSide

        var mesh = new THREE.Mesh(towerModel.geometry, towerModel.material)
        mesh.position.set(x * 4 - 216, 0, y * 4 - 311)

        mesh.castShadow = true
        mesh.receiveShadow = true

        scene.add(mesh)

        var flagMesh = new THREE.Mesh(flagModel.geometry, material)
        flagMesh.position.set(x * 4 - 216, 3.5, y * 4 - 311)
        scene.add(flagMesh)

        return mesh.id
    }

    var initCastle = function () {
        castleModel = loader.parse(castle)
        castleModel.material = new THREE.MeshLambertMaterial({color: 0xa0a0a0})
        castleModel.material.side = THREE.DoubleSide
        flagModel = loader.parse(flag)
    }
    this.addCastle = function (x, y, color, castleId) {
        var material = new THREE.MeshLambertMaterial({color: color})
        material.side = THREE.DoubleSide

        var scale = 0.5
        var mesh = new THREE.Mesh(castleModel.geometry, castleModel.material)
        mesh.scale.set(scale, scale, scale);
        mesh.position.set(x * 4 - 214, 0, y * 4 - 309)

        mesh.name = 'castle'
        mesh.identification = castleId

        mesh.castShadow = true
        mesh.receiveShadow = true

        scene.add(mesh)
        EventsControls.attach(mesh);

        var flagMesh = new THREE.Mesh(flagModel.geometry, material)
        flagMesh.position.set(x * 4 - 210.8, 1.7, y * 4 - 312.4)
        scene.add(flagMesh)

        return mesh.id
    }

    var initArmy = function () {
        hero.scale = 4
        armyModel = loader.parse(hero)
        armyModel.material = new THREE.MeshLambertMaterial({color: 0xa0a0a0})
        armyModel.material.side = THREE.DoubleSide
        flag_1.scale = 5
        flag_1Model = loader.parse(flag_1)
    }
    this.addArmy = function (x, y, color, armyId) {
        var material = new THREE.MeshLambertMaterial({color: color})
        material.side = THREE.DoubleSide

        var mesh = new THREE.Mesh(armyModel.geometry, armyModel.material)
        mesh.position.set(x * 4 - 216, 0, y * 4 - 311)
        mesh.rotation.y = Math.PI / 2 + Math.PI / 4

        mesh.name = 'army'
        mesh.identification = armyId

        mesh.castShadow = true
        mesh.receiveShadow = true

        var flagMesh = new THREE.Mesh(flag_1Model.geometry, material)
        flagMesh.position.set(-2, 0, 0)
        mesh.add(flagMesh)

        scene.add(mesh)
        EventsControls.attach(mesh);

        return mesh.id
    }

    var loadGround = function () {
        var ground = new THREE.Mesh(new THREE.PlaneBufferGeometry(436, 624), new THREE.MeshLambertMaterial({map: THREE.ImageUtils.loadTexture('/img/maps/1.png')}));
        //var ground = new THREE.Mesh(new THREE.PlaneBufferGeometry(436, 624), new THREE.MeshLambertMaterial({color: 0x00dd00}));
        ground.rotation.x = -Math.PI / 2
        ground.receiveShadow = true
        scene.add(ground)
        EventsControls.attach(ground)
    }

    var initFields = function () {
        tree.scale = 3
        mountainModel = loader.parse(mountain)
        hillModel = loader.parse(hill)
        treeModel = loader.parse(tree)

        mountainModel.material = new THREE.MeshLambertMaterial({color: '#808080'})
        mountainModel.material.side = THREE.DoubleSide

        hillModel.material = new THREE.MeshLambertMaterial({color: '#00a000'})
        hillModel.material.side = THREE.DoubleSide
        //hillModel.scale = 0.7

        treeModel.material = new THREE.MeshLambertMaterial({color: '#008000'})
        treeModel.material.side = THREE.DoubleSide
        //treeModel.scale = 0.4
    }

    this.getMountainModel = function () {
        return mountainModel
    }
    this.getHillModel = function () {
        return hillModel
    }
    this.getTreeModel = function () {
        return treeModel
    }

    this.init = function (fields) {
        initRuin()
        initTower()
        initCastle()
        initArmy()
        initFields()
        $('#game').append(renderer.domElement)
        loadGround()
        render()
    }
    var render = function () {
        requestAnimationFrame(render)
        renderer.render(scene, camera)
    }
}

var EventsControls = new EventsControls(Three.getCamera(), Three.getRenderer().domElement);
EventsControls.displacing = false
EventsControls.onclick = function () {
    console.log(this)
    switch (this.focused.name) {
        case 'castle':
            var castle = Me.getCastle(this.focused.identification)
            if (isSet(castle)) {
                Message.castle(castle)
            }
            break
        case 'army':
            Me.setSelectedArmyId(this.focused.identification)
            break
        default:
            if (Me.getSelectedArmyId()) {
                Websocket.move(parseInt((this.intersects[0].point.x + 218) / 4), parseInt((this.intersects[0].point.z + 312) / 4))
            }
    }
}