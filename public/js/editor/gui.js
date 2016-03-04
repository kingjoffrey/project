var Gui = new function () {
    var doKey = function (event) {
            if ($(event.target).attr('id') == 'msg') {
                return;
            }
            var key = event.keyCode || event.charCode;
            switch (key) {
                case 37://left
                    Scene.moveCameraLeft()
                    break;
                case 38://up
                    Scene.moveCameraUp()
                    break;
                case 39://right
                    Scene.moveCameraRight()
                    break;
                case 40://down
                    Scene.moveCameraDown()
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
    this.init = function () {
        //$(window).resize(function () {
        //    Gui.adjust()
        //})
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
                if (Scene.getCameraY() < 230) {
                    Scene.moveCameraAway()
                }
            } else {
                if (Scene.getCameraY() > 22) {
                    Scene.moveCameraClose()
                }
            }
        })
        activateButtons()
    }
}