"use strict"
var Zoom = new function () {
    var lens,
        smallImage,
        scale,
        smallImage = function () {
            var image = $('#mapImage'),
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
            var $obj = {};
            $obj.node = $('.zoomPup')
            $obj.setdimensions = function () {
                var visibleMapWidth = Scene.getWidth() / 85,
                    visibleMapHeight = Scene.getHeight() / 85

                $obj.node.w = (smallImage.w * visibleMapWidth) / Fields.getMaxX()
                $obj.node.h = (smallImage.h * visibleMapHeight) / Fields.getMaxY()
                $obj.node.css({
                    'width': $obj.node.w,
                    'height': $obj.node.h
                });
                $obj.node.top = (smallImage.oh - $obj.node.h - 2) / 2;
                $obj.node.left = (smallImage.ow - $obj.node.w - 2) / 2;
            };
            $obj.setcenter = function (x, y, func) {
                $obj.node.top = y * scale - $obj.node.h / 2
                $obj.node.left = x * scale - $obj.node.w / 2
                $obj.node.css({
                    top: $obj.node.top,
                    left: $obj.node.left
                });

                var yOffset = Scene.getCamera().position.y - Scene.getCameraY(),
                    startPosition = {
                        x: Scene.getCamera().position.x,
                        z: Scene.getCamera().position.z
                    },
                    endPosition = {
                        x: x * 2 - Scene.getCameraY() - yOffset,
                        z: y * 2 + Scene.getCameraY() + yOffset
                    },
                    tween = new TWEEN.Tween(startPosition)
                        .to(endPosition, Math.sqrt(Math.pow(endPosition.x - startPosition.x, 2) + Math.pow(startPosition.z - endPosition.z, 2)) * 5)
                        .onUpdate(function () {
                            Scene.setCameraPosition(endPosition.x, endPosition.z)
                        })
                        .start()

                if (isSet(func)) {
                    tween.onComplete(function () {
                        func()
                    })
                }
            }
            $obj.setposition = function (e) {
                var mouseX = e.pageX,
                    mouseY = e.pageY,
                    lensleft = 0,
                    lenstop = 0

                function overleft(lens) {
                    return mouseX < smallImage.pos.l;
                }

                function overright(lens) {
                    return mouseX > smallImage.pos.r;
                }

                function overtop(lens) {
                    return mouseY < smallImage.pos.t;
                }

                function overbottom(lens) {
                    return mouseY > smallImage.pos.b;
                }

                lensleft = mouseX + smallImage.bleft - smallImage.pos.l - ($obj.node.w + 2) / 2;
                lenstop = mouseY + smallImage.btop - smallImage.pos.t - ($obj.node.h + 2) / 2;
                if (overleft($obj.node)) {
                    lensleft = smallImage.bleft - $obj.node.w / 2;
                } else if (overright($obj.node)) {
                    lensleft = smallImage.w + smallImage.bleft - $obj.node.w / 2;
                }
                if (overtop($obj.node)) {
                    lenstop = smallImage.btop - $obj.node.h / 2;
                } else if (overbottom($obj.node)) {
                    lenstop = smallImage.h + smallImage.btop - $obj.node.h / 2;
                }

                $obj.node.left = lensleft;
                $obj.node.top = lenstop;
                $obj.node.css({
                    'left': lensleft + 'px',
                    'top': lenstop + 'px'
                });

                var yOffset = Scene.getCamera().position.y - Scene.getCameraY(),
                    centerX = $obj.node.left + $obj.node.w / 2,
                    centerY = $obj.node.top + $obj.node.h / 2

                Scene.getCamera().position.x = (Fields.getMaxX() * centerX / smallImage.w) * 2 - Scene.getCameraY() - yOffset
                Scene.getCamera().position.z = (Fields.getMaxY() * centerY / smallImage.h) * 2 + Scene.getCameraY() + yOffset
            };
            $obj.show = function () {
                $obj.node.show();
            };

            $obj.setdimensions()
            return $obj;
        }

    this.getLens = function () {
        return lens
    }
    this.getScale = function () {
        return scale
    }
    this.calculateMiniMapX = function (x) {
        return parseInt(smallImage.w * x / Fields.getMaxX())
    }
    this.calculateMiniMapY = function (y) {
        return parseInt(smallImage.h * y / Fields.getMaxY())
    }
    this.init = function (s) {
        scale = s
        Game.getMapElement().bind('mousedown', function (e) {
            if (e.pageX > smallImage.pos.r || e.pageX < smallImage.pos.l || e.pageY < smallImage.pos.t || e.pageY > smallImage.pos.b) {
                return false;
            }
            lens.setposition(e)
        })

        smallImage = smallImage()
        lens = lens()
        lens.show()
    }
}
