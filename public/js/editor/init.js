$(document).ready(function () {
    mapHeight = mapWidth = 1025
//        mapHeight = mapWidth = 2049
//        mapHeight = mapWidth = 8193
    Editor.init()
    Gui.init()
})

var Aaa = function () {
    this.aaa = 'To jest a'
}

Aaa.prototype.do = function () {
    this.a = 'zaqwsx'
    console.log('I do things')
}

var Bbb = function () {
    this.bbb = 'To jest b'
    this.do = function () {
        console.log('x')
        Aaa.prototype.do.apply(this)
        console.log(this.a)
    }
    this.done = function () {
        console.log('Something is done')
    }
}

Bbb.prototype = new Aaa()
//Bbb.prototype.constructor = Bbb

bb = new Bbb()

bb.do()