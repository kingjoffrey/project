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
        console.log(r.players)
        Players.init(r.players)
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
