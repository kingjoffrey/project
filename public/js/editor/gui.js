var EditorGui = new function () {
    var doKey = function (event) {
            var key = event.keyCode || event.charCode;
            if ($('#game').length == 0) {
                return
            }
            switch (key) {
                case 27: //ESC
                    Message.remove()
                    break;
                case 37://left
                    GameScene.moveCameraLeft()
                    break;
                case 38://up
                    GameScene.moveCameraUp()
                    break;
                case 39://right
                    GameScene.moveCameraRight()
                    break;
                case 40://down
                    GameScene.moveCameraDown()
                    break;
//            default:
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
                IndexController.index()
            })
            $('#publish').click(function () {
                WebSocketSendEditor.publish()
                IndexController.index()
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
        $('body')
            .keydown(function (event) {
                doKey(event)
            })
            .on('contextmenu', function () {
                return false
            })
            .on('dragstart', function () {
                return false
            })
        $('#game canvas').mousewheel(function (event) {
            if (event.deltaY > 0) {
                if (GameScene.getCameraY() < 230) {
                    GameScene.moveCameraAway()
                }
            } else {
                if (GameScene.getCameraY() > 22) {
                    GameScene.moveCameraClose()
                }
            }
        })

        activateButtons()
        this.adjust()
    }
}