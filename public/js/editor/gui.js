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
                Models.create('castle')
            })
            $('#ruin').click(function () {
                Models.create('ruin')
            })
            $('#tower').click(function () {
                Models.create('tower')
            })
            $('#road').click(function () {
                Models.create('road')
            })
            $('#bridge').click(function () {
                Models.create('bridge')
            })
            $('#forest').click(function () {
                Models.create('forest')
            })
            $('#swamp').click(function () {
                Models.create('swamp')
            })
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