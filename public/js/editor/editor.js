var Editor = new function () {
    var init = 0,
        brush = null

    this.getInit = function () {
        return init
    }
    this.init = function (fields) {
        init = 1
        initButtons()
        Scene.init()
        Fields.init(fields, mapId)
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
