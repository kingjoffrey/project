var PickerCommon = new function () {
    var rayCaster,
        objects,
        intersects = [],
        camera,
        container,
        mouse = new THREE.Vector2(),
        x, y

    this.init = function (picker) {
        rayCaster = new THREE.Raycaster()
        objects = []
        camera = GameScene.getCamera()
        container = GameRenderer.getDomElement()

        $('canvas').off()

        $('canvas')
            .mousewheel(function (event) {
                if (event.deltaY > 0) {
                    GameScene.moveCameraClose()
                } else {
                    GameScene.moveCameraAway()
                }
            })
            .on('mousedown', picker.onContainerMouseDown)
            .on('mousemove', picker.onContainerMouseMove)
            .on('mouseup', picker.onContainerMouseUp)
            .on('mouseout', picker.onContainerMouseOut)
            .on('touchstart', picker.onContainerTouchStart)
            .on('touchmove', picker.onContainerTouchMove)
            .on('touchend', picker.onContainerTouchEnd)
            .on('touchcancel', picker.onContainerTouchEnd)
            .on('contextmenu', function () {
                return false
            })
            .on('dragstart', function () {
                return false
            })
    }
    this.getObject = function () {
        return intersects[0].object
    }
    this.intersect = function (event) {
        x = event.offsetX == undefined ? event.layerX : event.offsetX
        y = event.offsetY == undefined ? event.layerY : event.offsetY

        mouse.x = ( x / container.width ) * 2 - 1
        mouse.y = -( y / container.height ) * 2 + 1

        rayCaster.setFromCamera(mouse, camera)

        intersects = rayCaster.intersectObjects(objects)
    }
    this.convertX = function () {
        return Math.floor(parseInt(intersects[0].point.x) / 2)
    }
    this.convertZ = function () {
        return Math.floor(parseInt(intersects[0].point.z) / 2)
    }
    this.attach = function (object) {
        if (object instanceof THREE.Mesh) {
            objects.push(object);
        }
    }
    this.detach = function (object) {
        objects.splice(objects.indexOf(object), 1);
    }
    this.detachAll = function () {
        objects = []
    }
    /**
     *
     * @returns {Field}
     */
    this.getField = function () {
        return Fields.get(PickerCommon.convertX(), PickerCommon.convertZ())
    }
    this.intersects = function () {
        return intersects.length > 0
    }
    this.cursor = function (type) {
        switch (type) {
            case 'attack':
                $('body #main #game canvas').css('cursor', 'url(/img/game/cursors/attack.png) 11 11, auto')
                break
            case 'enter':
                $('body #main #game canvas').css('cursor', 'url(/img/game/cursors/enter.png) 11 11, auto')
                break
            case 'fly':
                $('body #main #game canvas').css('cursor', 'url(/img/game/cursors/fly.png) 11 11, auto')
                break
            case 'grab':
                $('body #main #game canvas').css('cursor', 'url(/img/game/cursors/grab.png) 11 11, auto')
                break
            case 'impenetrable':
                $('body #main #game canvas').css('cursor', 'url(/img/game/cursors/impenetrable.png) 11 11, auto')
                break
            case 'join':
                $('body #main #game canvas').css('cursor', 'url(/img/game/cursors/join.png) 11 11, auto')
                break
            case 'move':
                $('body #main #game canvas').css('cursor', 'url(/img/game/cursors/move.png) 11 11, auto')
                break
            case 'open':
                $('body #main #game canvas').css('cursor', 'url(/img/game/cursors/open.png) 11 11, auto')
                break
            case 'select':
                $('body #main #game canvas').css('cursor', 'url(/img/game/cursors/select.png) 11 11, auto')
                break
            case 'split':
                $('body #main #game canvas').css('cursor', 'url(/img/game/cursors/split.png) 11 11, auto')
                break
            case 'swim':
                $('body #main #game canvas').css('cursor', 'url(/img/game/cursors/swim.png) 11 11, auto')
                break
            case 'wait':
                $('body #main #game canvas').css('cursor', 'url(/img/game/cursors/wait.png) 11 11, auto')
                break
            case 'walk':
                $('body #main #game canvas').css('cursor', 'url(/img/game/cursors/walk.png) 11 11, auto')
                break
            default:
                $('body #main #game canvas').css('cursor', 'default')
        }
    }
    /**
     *
     * @returns {{x: Number, y: Number}}
     */
    this.getPoint = function () {
        return {'x': x, 'y': y}
    }
    this.checkOffset = function (point1, point2) {
        if (point1.x >= point2.x - 1 && point1.x <= point2.x + 1) {
            if (point1.y >= point2.y - 1 && point1.y <= point2.y + 1) {
                return 1
            }
        }
        return 0
    }
}
