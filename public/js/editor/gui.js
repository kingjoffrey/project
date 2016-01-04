var Gui = {
    init: function () {
        $('#generate').click(function () {
            Editor.generate()
        })
        $('#save').click(function () {
            WebSocketEditor.save()
        })
        $('#castle').click(function (e) {
            Editor.brush = 'castle'
        })
        $('#eraser').click(function () {
            Editor.brush = 'eraser'
        })
    },
    showGrid: function (size) {
        var max = size / 40

        var rect = new Kinetic.Rect({
            x: 0,
            y: 0,
            width: 38,
            height: 38,
            stroke: 'black',
            strokeWidth: 1
        })

        Editor.group.add(rect)
    }
}
