var RendererGame = new function () {
    var renderer = Renderer.get(),
        scene = SimpleScene.get()

    this.render = function () {
        renderer.clear()

        renderer.setViewport(0, 0, 100, 100);
        renderer.render(scene1, activeCamera)

        renderer.setViewport(0, 0, width, height)
        renderer.render(scene2, camera)
    }

    this.animate = function () {
        if (TWEEN.update()) {
            requestAnimationFrame(Renderer.animate)
        } else {
            setTimeout(function () {
                requestAnimationFrame(Renderer.animate)
            }, timeOut)
        }

        Renderer.render();
        // stats.update();
    }
    //
    // this.init = function (w, h, id) {
    //     $('#' + id).append(renderer.domElement)
    //     this.setSize(w, h)
    // }
}