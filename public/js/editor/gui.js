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
        },
        activateButtons = function () {
            $('#castle').click(function () {
                EditorModels.createMesh('castle')
            })
            $('#ruin').click(function () {
                EditorModels.createMesh('ruin')
            })
            $('#tower').click(function () {
                EditorModels.createMesh('tower')
            })
            $('#road').click(function () {
                EditorModels.createMesh('road')
            })
            $('#forest').click(function () {
                EditorModels.createMesh('forest')
            })
            $('#swamp').click(function () {
                EditorModels.createMesh('swamp')
            })
            $('#eraser').click(function () {
                EditorModels.createMesh('eraser')
            })
            $('#up').click(function () {
                EditorModels.createMesh('up')
            })
            $('#down').click(function () {
                EditorModels.createMesh('down')
            })
            $('#rightMenu #exit').click(function () {
                EditorController.index()
            })
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

        activateButtons()
        this.adjust()

        $('#wait').hide()
    }
}