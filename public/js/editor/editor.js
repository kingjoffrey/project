var Editor = new function () {
    var init = 0

    this.init = function (r) {
        if (!init) {
            init = 1
            Units.init(r.units)
            Scene.init()
            Fields.init(r.fields, mapId)
            Ruins.init(r.ruins)
            Players.init(r.players)
            Gui.init()
            Scene.setCameraPosition(0, Fields.getMaxY())
        }
        //console.log(r)
    }
}
