var Zoom = {
    gameWidth: 0,
    gameHeight: 0,
    lens: null,
    scale: {x: 20, y: 20},
    init: function () {
        this.gameWidth = $(window).width()
        this.gameHeight = $(window).height()

        document.body.ondragstart = function () {
            return false;
        };

        map.bind('mousedown', function (e) {
            if (e.pageX > Zoom.smallimage.pos.r || e.pageX < Zoom.smallimage.pos.l || e.pageY < Zoom.smallimage.pos.t || e.pageY > Zoom.smallimage.pos.b) {
                return false;
            }
            Zoom.lens.setposition(e)
        });

        this.smallimage = this.Smallimage()
        this.smallimage.fetchdata()
        this.lens = this.Lens()
        this.lens.setdimensions(0, 0);
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
            var bordertop = 0;
            bordertop = image.css('border-top-width');
            btop = '';
            var borderleft = 0;
            borderleft = image.css('border-left-width');
            bleft = '';
            if (bordertop) {
                for (i = 0; i < 3; i++) {
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
                for (i = 0; i < 3; i++) {
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
            $obj.node.w = (parseInt(Zoom.gameWidth / Zoom.scale.x) > Zoom.smallimage.w ) ? Zoom.smallimage.w : (parseInt(Zoom.gameWidth / Zoom.scale.x));
            $obj.node.h = (parseInt(Zoom.gameHeight / Zoom.scale.y) > Zoom.smallimage.h ) ? Zoom.smallimage.h : (parseInt(Zoom.gameHeight / Zoom.scale.y));
            $obj.node.css({
                'width': $obj.node.w,
                'height': $obj.node.h
            });
            $obj.node.top = (Zoom.smallimage.oh - $obj.node.h - 2) / 2;
            $obj.node.left = (Zoom.smallimage.ow - $obj.node.w - 2) / 2;
        };
        $obj.setcenter = function (x, y) {
            if (game.players[Turn.color].computer && !Gui.show) {
                return;
            }

            $obj.node.top = (y * 40 - Zoom.gameHeight / 2) / Zoom.scale.y
            $obj.node.left = (x * 40 - Zoom.gameWidth / 2) / Zoom.scale.x
            $obj.node.css({
                top: $obj.node.top,
                left: $obj.node.left
            });

            Three.getCamera().position.x = x * 4 - 262
            Three.getCamera().position.z = y * 4 - 262
        };
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

            Three.getCamera().position.x = ($obj.node.left * Zoom.scale.x + Zoom.gameWidth / 2) / 10 - 262
            Three.getCamera().position.z = ($obj.node.top * Zoom.scale.y + Zoom.gameHeight / 2) / 10 - 262
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
    }
};
