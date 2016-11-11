var Renderers = new function () {
    var renderers = {},
        i = 0
    this.add = function (r) {
        renderers[i] = r
        i++
    }
    this.clear = function () {
        for (var id in renderers) {
            renderers[id] = ''
        }
        renderers = {}
    }
}