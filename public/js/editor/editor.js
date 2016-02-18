var Editor = new function () {
    var init = 0

    this.init = function (r) {
        if (Models.getLoading() < 17) {
            setTimeout(function () {
                console.log(Models.getLoading())
                Editor.init(r)
            }, 500)
            return
        }
        if (!init) {
            init = 1
            Units.init(r.units)
            Fields.init(r.fields, mapId)
            Ruins.init(r.ruins)
            Players.init(r.players)
            Gui.init()
            Scene.setCameraPosition(0, Fields.getMaxY())
        }
        //console.log(r)
    }
}
