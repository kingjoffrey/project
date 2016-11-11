var Renderers = new function () {
    var renderers = {}

    this.add = function (id, unitScene) {
        renderers[id] = new UnitRenderer()
        renderers[id].init(id, unitScene)

        requestAnimationFrame(function animate() {
            if (isSet(renderers[id])) {
                renderers[id].render()
                requestAnimationFrame(animate)
            }
        })
    }
    this.get = function (id) {
        return renderers[id]
    }
    this.clear = function () {
        for (var id in renderers) {
            renderers[id].clear()
            renderers[id] = null
        }
        renderers = {}
    }
}