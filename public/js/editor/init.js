$(document).ready(function () {
    mapHeight = mapWidth = 1025
//        mapHeight = mapWidth = 2049
//        mapHeight = mapWidth = 8193
    Editor.init()
    Websocket.init('editor')
    Gui.init()
})
