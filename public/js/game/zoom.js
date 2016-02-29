"use strict"
var Zoom = {
    lens: null,
    scale: {x: 20, y: 20},
    init: function () {
        map.bind('mousedown', function (e) {
            if (e.pageX > Zoom.smallimage.pos.r || e.pageX < Zoom.smallimage.pos.l || e.pageY < Zoom.smallimage.pos.t || e.pageY > Zoom.smallimage.pos.b) {
                return false;
            }
            Zoom.lens.setposition(e)
        })

        this.smallimage = this.Smallimage()
        this.smallimage.fetchdata()
        this.lens = this.Lens()
        this.lens.setdimensions();
        this.lens.show()
    },
    /*========================================================,
     |   Smallimage
     |---------------------------------------------------------:
     |   Base image into the anchor element
     `========================================================*/

    Smallimage: function () {
        var $obj = {};
        var image = $('#mapImage');
        $obj.node = image[0];

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
    /*========================================================,
     |   Lens
     |---------------------------------------------------------:
     |   Lens over the image
     `========================================================*/

    Lens: function () {
        var $obj = {};
        $obj.node = $('.zoomPup');
        $obj.mousepos = {}
        $obj.setdimensions = function () {
            $obj.node.w = (parseInt(Scene.getWidth() / Zoom.scale.x) > Zoom.smallimage.w ) ? Zoom.smallimage.w : (parseInt(Scene.getWidth() / Zoom.scale.x));
            $obj.node.h = (parseInt(Scene.getHeight() / Zoom.scale.y) > Zoom.smallimage.h ) ? Zoom.smallimage.h : (parseInt(Scene.getHeight() / Zoom.scale.y));
            $obj.node.css({
                'width': $obj.node.w,
                'height': $obj.node.h
            });
            $obj.node.top = (Zoom.smallimage.oh - $obj.node.h - 2) / 2;
            $obj.node.left = (Zoom.smallimage.ow - $obj.node.w - 2) / 2;
        };
        $obj.setcenter = function (x, y, func) {
            $obj.node.top = y * 2 - Scene.getHeight() / 2
            $obj.node.left = x * 2 - Scene.getWidth() / 2
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
                    .to(endPosition, Zoom.getH(startPosition, endPosition))
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
            $obj.mousepos.x = e.pageX;
            $obj.mousepos.y = e.pageY;
            var lensleft = 0;
            var lenstop = 0;

            function overleft(lens) {
                return $obj.mousepos.x < Zoom.smallimage.pos.l;
            }

            function overright(lens) {
                return $obj.mousepos.x > Zoom.smallimage.pos.r;
            }

            function overtop(lens) {
                return $obj.mousepos.y < Zoom.smallimage.pos.t;
            }

            function overbottom(lens) {
                return $obj.mousepos.y > Zoom.smallimage.pos.b;
            }

            lensleft = $obj.mousepos.x + Zoom.smallimage.bleft - Zoom.smallimage.pos.l - ($obj.node.w + 2) / 2;
            lenstop = $obj.mousepos.y + Zoom.smallimage.btop - Zoom.smallimage.pos.t - ($obj.node.h + 2) / 2;
            if (overleft($obj.node)) {
                lensleft = Zoom.smallimage.bleft - $obj.node.w / 2;
            } else if (overright($obj.node)) {
                lensleft = Zoom.smallimage.w + Zoom.smallimage.bleft - $obj.node.w / 2;
            }
            if (overtop($obj.node)) {
                lenstop = Zoom.smallimage.btop - $obj.node.h / 2;
            } else if (overbottom($obj.node)) {
                lenstop = Zoom.smallimage.h + Zoom.smallimage.btop - $obj.node.h / 2;
            }

            $obj.node.left = lensleft;
            $obj.node.top = lenstop;
            $obj.node.css({
                'left': lensleft + 'px',
                'top': lenstop + 'px'
            });

            var yOffset = Scene.getCamera().position.y - Scene.getCameraY()
            console.log(yOffset)
            //Scene.getCamera().position.x = ($obj.node.left * Zoom.scale.x + Scene.getWidth() / 2) / 10 - Scene.getCameraY() - yOffset
            //Scene.getCamera().position.z = ($obj.node.top * Zoom.scale.y + Scene.getHeight() / 2) / 10 - Scene.getCameraY() + yOffset
            Scene.getCamera().position.x = ($obj.node.left * Zoom.scale.x + Scene.getWidth() / 2) - Scene.getCameraY() - yOffset
            Scene.getCamera().position.z = ($obj.node.top * Zoom.scale.y + Scene.getHeight() / 2) - Scene.getCameraY() + yOffset
        };
        $obj.show = function () {
            $obj.node.show();
        };
        $obj.getoffset = function () {
            var o = {};
            o.left = $obj.node.left;
            o.top = $obj.node.top;
            return o;
        };
        return $obj;
    },
    getH: function (startPosition, endPosition) {
        return Math.sqrt(Math.pow(endPosition.x - startPosition.x, 2) + Math.pow(startPosition.z - endPosition.z, 2)) * 5
    }
}
