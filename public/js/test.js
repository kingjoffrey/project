    var Three = new function () {
        var scene = new THREE.Scene()

        var camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 1000)
        camera.position.set(-380, 252, 420);
        camera.rotation.order = 'YXZ';
        camera.rotation.y = -Math.PI / 4;
        camera.rotation.x = Math.atan(-1 / Math.sqrt(2));

        var renderer = new THREE.WebGLRenderer()
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.shadowMapEnabled = true

        var light = new THREE.DirectionalLight(0xffffff, 1)
        light.position.set(150, 100, 100)
        light.castShadow = true
        light.shadowDarkness = 0.3
        light.shadowCameraVisible = true
        light.shadowCameraRight = 50;
        light.shadowCameraLeft = -50;
        light.shadowCameraTop = 50;
        light.shadowCameraBottom = -50;
        scene.add(light);

        var ground = new THREE.Mesh(
            new THREE.PlaneBufferGeometry(436, 624),
            new THREE.MeshLambertMaterial({color: '#808080'})
        );
        ground.rotation.x = -Math.PI / 2;
        ground.receiveShadow = true

        scene.add(ground);

        this.init = function () {
            $('body').append(renderer.domElement);
            Three.render();
        }
        this.render = function () {
            requestAnimationFrame(Three.render);
            renderer.render(scene, camera);
        };
    }

    $(document).ready(function () {
        Three.init();
    });
