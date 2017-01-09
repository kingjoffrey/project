var Test = new function () {
    var canvas = document.createElement('canvas'),
        ctx = canvas.getContext('2d'),
        setPixel = function (x, y, color) {
            ctx.fillStyle = color;
            ctx.fillRect(x * 3, y * 3, 3, 3);
        }
    this.init = function () {
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

        ctx.drawImage(canvas, 0, 0)

        $('div').append(canvas)
    }
}
$(document).ready(function () {
    // Test.init()
    Models.init()
    GameScene.init($(window).innerWidth(), $(window).innerHeight())
    Fields.init(fields, 7)
    GameRenderer.init('game', GameScene)
    GameScene.initSun(Fields.getMaxY())
    GameRenderer.animate()
})
