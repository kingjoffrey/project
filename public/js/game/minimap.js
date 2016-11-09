"use strict"
var MiniMap = new function () {
    var lens,
        miniMap,
        miniMap = function () {
            var image = $('#map canvas'),
                node = image[0],
                $obj = {}

            $obj.findborder = function () {
                var bordertop = 0,
                    btop = ''
                bordertop = image.css('border-top-width');
                var borderleft = 0,
                    bleft = '';
                borderleft = image.css('border-left-width');
                if (bordertop) {
                    for (var i = 0; i < 3; i++) {
                        var x = [];
                        x = bordertop.substr(i, 1);
                        if (isNaN(x) == false) {
                            btop = btop + '' + bordertop.substr(i, 1);
                        } else {
                            break;
                        }
                    }
                }
                if (borderleft) {
                    for (var i = 0; i < 3; i++) {
                        if (!isNaN(borderleft.substr(i, 1))) {
                            bleft = bleft + borderleft.substr(i, 1)
                        } else {
                            break;
                        }
                    }
                }
                $obj.btop = (btop.length > 0) ? eval(btop) : 0;
                $obj.bleft = (bleft.length > 0) ? eval(bleft) : 0;
            };
            $obj.fetchdata = function () {
                $obj.findborder();
                $obj.w = image.width();
                $obj.h = image.height();
                $obj.ow = image.outerWidth();
                $obj.oh = image.outerHeight();
                $obj.pos = image.offset();
                $obj.pos.l = image.offset().left + $obj.bleft;
                $obj.pos.t = image.offset().top + $obj.btop;
                $obj.pos.r = $obj.w + $obj.pos.l;
                $obj.pos.b = $obj.h + $obj.pos.t;
                $obj.rightlimit = image.offset().left + $obj.ow;
                $obj.bottomlimit = image.offset().top + $obj.oh;
            };
            $obj.fetchdata()
            return $obj;
        },
        lens = function () {
            var $obj = {}
            $obj.node = $('#lens')
            $obj.setdimensions = function () {
                var visibleMapWidth = GameScene.getWidth() / 85,
                    visibleMapHeight = GameScene.getHeight() / 85

                $obj.node.w = miniMap.w * visibleMapWidth / Fields.getMaxX()
                $obj.node.h = miniMap.h * visibleMapHeight / Fields.getMaxY()
                $obj.node.css({
                    'width': $obj.node.w,
                    'height': $obj.node.h
                });
            };
            $obj.centerOn = function (x, y, func) {
                $obj.node.top = miniMap.h * y / Fields.getMaxY() - $obj.node.h / 2
                $obj.node.left = miniMap.w * x / Fields.getMaxX() - $obj.node.w / 2
                $obj.node.css({
                    top: $obj.node.top,
                    left: $obj.node.left
                });

                var yOffset = GameScene.getCamera().position.y - GameScene.getCameraY(),
                    startPosition = {
                        x: GameScene.getCamera().position.x,
                        z: GameScene.getCamera().position.z
                    },
                    endPosition = {
                        x: x * 2 - GameScene.getCameraY() - yOffset,
                        z: y * 2 + GameScene.getCameraY() + yOffset
                    },
                    tween = new TWEEN.Tween(startPosition)
                        .to(endPosition, Math.sqrt(Math.pow(endPosition.x - startPosition.x, 2) + Math.pow(startPosition.z - endPosition.z, 2)) * 5)
                        .onUpdate(function () {
                            GameScene.setCameraPosition(endPosition.x, endPosition.z)
                        })
                        .start()

                if (isSet(func)) {
                    tween.onComplete(function () {
                        func()
                    })
                }
            }
            $obj.centerOnCameraPosition = function () {
                var yOffset = GameScene.getCamera().position.y - GameScene.getCameraY(),
                    x = (GameScene.getCamera().position.x + GameScene.getCameraY() + yOffset) / 2 / Fields.getMaxX() * miniMap.w - lens.node.w / 2,
                    y = (GameScene.getCamera().position.z - GameScene.getCameraY() - yOffset) / 2 / Fields.getMaxY() * miniMap.h - lens.node.h / 2

                $obj.node.top = y
                $obj.node.left = x
                $obj.node.css({
                    top: $obj.node.top,
                    left: $obj.node.left
                })
            }
            $obj.setposition = function (e) {
                var mouseX = e.pageX,
                    mouseY = e.pageY,
                    lensleft = 0,
                    lenstop = 0

                function overleft(lens) {
                    return mouseX < miniMap.pos.l;
                }

                function overright(lens) {
                    return mouseX > miniMap.pos.r;
                }

                function overtop(lens) {
                    return mouseY < miniMap.pos.t;
                }

                function overbottom(lens) {
                    return mouseY > miniMap.pos.b;
                }

                lensleft = mouseX + miniMap.bleft - miniMap.pos.l - ($obj.node.w + 2) / 2;
                lenstop = mouseY + miniMap.btop - miniMap.pos.t - ($obj.node.h + 2) / 2;
                if (overleft($obj.node)) {
                    lensleft = miniMap.bleft - $obj.node.w / 2;
                } else if (overright($obj.node)) {
                    lensleft = miniMap.w + miniMap.bleft - $obj.node.w / 2;
                }
                if (overtop($obj.node)) {
                    lenstop = miniMap.btop - $obj.node.h / 2;
                } else if (overbottom($obj.node)) {
                    lenstop = miniMap.h + miniMap.btop - $obj.node.h / 2;
                }

                $obj.node.left = lensleft;
                $obj.node.top = lenstop;
                $obj.node.css({
                    'left': lensleft + 'px',
                    'top': lenstop + 'px'
                });

                var yOffset = GameScene.getCamera().position.y - GameScene.getCameraY(),
                    centerX = $obj.node.left + $obj.node.w / 2,
                    centerY = $obj.node.top + $obj.node.h / 2

                Scene.setCameraPosition(
                    (Fields.getMaxX() * centerX / miniMap.w) * 2 - GameScene.getCameraY() - yOffset,
                    (Fields.getMaxY() * centerY / miniMap.h) * 2 + GameScene.getCameraY() + yOffset
                )
            }
            $obj.show = function () {
                $obj.node.show();
            };

            $obj.setdimensions()
            return $obj;
        }

    this.calculateX = function (x) {
        return parseInt(miniMap.w * x / Fields.getMaxX())
    }
    this.calculateY = function (y) {
        return parseInt(miniMap.h * y / Fields.getMaxY())
    }
    this.centerOn = function (x, y, func) {
        lens.centerOn(x, y, func)
    }
    this.centerOnCameraPosition = function () {
        lens.centerOnCameraPosition()
    }
    this.adjust = function () {
        lens.setdimensions()
    }
    this.init = function () {
        Game.getMapElement().bind('mousedown', function (e) {
            if (e.pageX > miniMap.pos.r || e.pageX < miniMap.pos.l || e.pageY < miniMap.pos.t || e.pageY > miniMap.pos.b) {
                return false;
            }
            lens.setposition(e)
        })

        miniMap = miniMap()
        lens = lens()
        lens.show()
    }
}
