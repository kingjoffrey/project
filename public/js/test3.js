var Test = new function () {
    var mapCanvas = document.createElement('canvas'),
        mapContext = mapCanvas.getContext('2d'),
        textureCanvas = document.createElement('canvas'),
        textureContext = textureCanvas.getContext('2d'),
        setPixel = function (x, y, color) {
            mapContext.fillStyle = color;
            mapContext.fillRect(x * 3, y * 3, 3, 3);
        }
    this.init = function () {
        mapCanvas.width = 327
        mapCanvas.height = 468
        for (var y in fields) {
            for (var x in fields[y]) {
                switch (fields[y][x].type) {
                    case 'g':
                        setPixel(x, y, '#009900')
                        break
                    case 'f':
                        setPixel(x, y, '#004e00')
                        break
                    case 'w':
                        setPixel(x, y, '#009900')
                        break
                    case 'h':
                        setPixel(x, y, '#505200')
                        break
                    case 'm':
                        setPixel(x, y, '#262728')
                        break
                    case 'r':
                        setPixel(x, y, '#009900')
                        break
                    case 'b':
                        setPixel(x, y, '#009900')
                        break
                    case 's':
                        setPixel(x, y, '#39723E')
                        break
                }
            }
        }
        var width = x * 3 + 3,
            height = y * 3 + 3

        textureCanvas.width = width
        textureCanvas.height = height

        textureContext.translate(0, height)
        textureContext.scale(1, -1)

        textureContext.drawImage(mapCanvas, 0, 0, width, height, 0, 0, width, height)

        // $('#2d').append(textureCanvas)
        return textureCanvas
    }
}
$(document).ready(function () {
    // Test.init()
    // Models.init()
    GameScene.init($(window).innerWidth(), $(window).innerHeight())
    Fields.init(fields, 1)
    GameRenderer.init('game', GameScene)
    GameScene.initSun(Fields.getMaxY())
    GameRenderer.animate()
    GameScene.getCamera().position.x = -150
    GameScene.getCamera().position.y = 265
    GameScene.getCamera().position.z = 450
})
