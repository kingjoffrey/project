var Renderer = new function () {
    var renderer = new THREE.WebGLRenderer({antialias: true}),
        timeOut = 100,
        scene,
        camera

    this.setFPS = function (fps) {
        timeOut = parseInt(1000 / fps)
    }
    this.setScene = function (s) {
        scene = s
    }
    this.setCamera = function (c) {
        camera = c
    }
    this.turnOnShadows = function () {
        renderer.shadowMap.enabled = true
        renderer.shadowMapSoft = false
    }
    this.getRenderer = function () {
        return renderer
    }
    this.setSize = function (w, h) {
        renderer.setSize(w, h)
    }

    this.renderSimple = function () {
        renderer.render(scene, camera)
        setTimeout(function () {
            requestAnimationFrame(Renderer.renderSimple)
        }, timeOut)
    }

    this.render = function () {
        if (TWEEN.update()) {
            requestAnimationFrame(Renderer.render)
            renderer.render(scene, camera)
        } else {
            renderer.render(scene, camera)
            setTimeout(function () {
                requestAnimationFrame(Renderer.render)
            }, timeOut)
        }
    }

    this.animate = function () {

        requestAnimationFrame(Renderer.animate);

        Renderer.render();
        // stats.update();

    }

    this.r_render = function () {

        var r = Date.now() * 0.0005;

        mesh.position.x = 700 * Math.cos(r);
        mesh.position.z = 700 * Math.sin(r);
        mesh.position.y = 700 * Math.sin(r);

        if (activeCamera === cameraPerspective) {

            cameraPerspective.fov = 35 + 30 * Math.sin(0.5 * r);
            cameraPerspective.far = mesh.position.length();
            cameraPerspective.updateProjectionMatrix();

            cameraPerspectiveHelper.update();
            cameraPerspectiveHelper.visible = true;

            cameraOrthoHelper.visible = false;

        } else {

            cameraOrtho.far = mesh.position.length();
            cameraOrtho.updateProjectionMatrix();

            cameraOrthoHelper.update();
            cameraOrthoHelper.visible = true;

            cameraPerspectiveHelper.visible = false;

        }

        cameraRig.lookAt(mesh.position);

        renderer.clear();

        activeHelper.visible = false;

        renderer.setViewport(0, 0, 100, 100);
        renderer.render(scene1, activeCamera);

        activeHelper.visible = true;

        renderer.setViewport(SCREEN_WIDTH / 2, 0, SCREEN_WIDTH / 2, SCREEN_HEIGHT);
        renderer.render(scene2, camera);


//        renderer.clear();
//        renderer.render(scene, camera);
//        renderer.clearDepth();
//        renderer.render(scene2, camera);

    }

    this.init = function (w, h, id) {
        $('#' + id).append(renderer.domElement)
        this.setSize(w, h)
    }
}