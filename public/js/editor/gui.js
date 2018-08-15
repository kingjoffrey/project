var EditorGui = new function () {
    var doKey = function (event) {
            var key = event.keyCode || event.charCode;
            switch (key) {
                case 27: //ESC
                    Message.remove()
                    break
                case 37://left
                    GameScene.moveCameraLeft()
                    break
                case 38://up
                    GameScene.moveCameraUp()
                    break
                case 39://right
                    GameScene.moveCameraRight()
                    break
                case 40://down
                    GameScene.moveCameraDown()
                    break
//            default
//                console.log(key)
            }
        }

    this.unlock = function () {
        lock = false
        $('#wait').hide()
    }
    this.lock = function () {
        lock = true
        $('#wait').show()
    }
    this.adjust = function () {
        $('#mapBox').width($('#map canvas').width())
        $('#map').height($('#map canvas').height())
        GameScene.resize($(window).innerWidth(), $(window).innerHeight())
        GameRenderer.setSize($(window).innerWidth(), $(window).innerHeight())
    }
    this.init = function () {
        $(window).resize(function () {
            EditorGui.adjust()
        })

        $('#terrain').removeClass('game').addClass('editor')

        $(document).on('keydown', function (event) {
            doKey(event)
        })

        this.adjust()

        $('#wait').hide()
    }
}