var Editor = new function () {
    var init = 0,
        brush = null

    this.getInit = function () {
        return init
    }
    this.init = function () {
        init = 1
        initButtons()
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
