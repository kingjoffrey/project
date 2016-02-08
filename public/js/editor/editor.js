var Editor = new function () {
    var init = 0,
        brush = null

    this.getInit = function () {
        return init
    }
    this.init = function (r) {
        init = 1
        initButtons()
        Scene.init()
        Fields.init(r.fields, mapId)
        Ruins.init(r.ruins)
        Players.init(r.players)
        Gui.init()
        Scene.setCameraPosition(0, Fields.getMaxY())
        //console.log(r)
    }
    var initButtons = function () {
        $('#castle').click(function (e) {
            brush = 'castle'
        })
        $('#eraser').click(function () {
            brush = 'eraser'
        })
    }
}
