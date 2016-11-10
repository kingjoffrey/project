var Gui = new function () {
    var doKey = function (event) {
            if ($(event.target).attr('id') == 'msg') {
                return;
            }
            var key = event.keyCode || event.charCode;
            switch (key) {
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
            $('#exit').click(function () {
                Gui.exit()
            });
        }


    this.exit = function () {
        window.location = '/' + lang + '/index'
    }
    this.adjust = function () {
        GameScene.resize($(window).innerWidth(), $(window).innerHeight())
    }
    this.init = function () {
        $(window).resize(function () {
           Gui.adjust()
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