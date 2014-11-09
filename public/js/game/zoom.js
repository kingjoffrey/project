var zoom = {
    mouseDown: false,
    gameWidth: gameWidth,
    gameHeight: gameHeight,
    smallimage: null,
    largeimage: null,
    lens: null,
    scale: {},
    loaded: false,
    init: function (gameWidth, gameHeight) {
        this.gameWidth = gameWidth
        this.gameHeight = gameHeight
        //drag option
        $("#map").mousedown(function () {
            zoom.mouseDown = true;
        });
        $("#map").mouseup(function () {
            zoom.mouseDown = false;
        });
        document.body.ondragstart = function () {
            return false;
        };
        $("#map").bind('mouseenter mouseover', function (event) {
            zoom.smallimage.fetchdata();
        });
        $("#map").bind('mousedown', function (e) {
            if (e.pageX > zoom.smallimage.pos.r || e.pageX < zoom.smallimage.pos.l || e.pageY < zoom.smallimage.pos.t || e.pageY > zoom.smallimage.pos.b) {
                return false;
            }
            if (zoom.loaded && zoom.mouseDown) {
                zoom.lens.setposition(e);
            }
        });
        $("#map").bind('mousemove', function (e) {

            //prevent fast mouse mevements not to fire the mouseout event
            if (e.pageX > zoom.smallimage.pos.r || e.pageX < zoom.smallimage.pos.l || e.pageY < zoom.smallimage.pos.t || e.pageY > zoom.smallimage.pos.b) {
                return false;
            }

            if (!$('#game').is(':visible')) {
                obj.activate(e);
            }
            if (zoom.mouseDown) {
                zoom.lens.setposition(e);
            }
        });

        this.smallimage = this.Smallimage()
        this.lens = this.Lens()
        this.largeimage = this.Largeimage();
        this.largeimage.loadimage();
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
            $obj.node.w = (parseInt((zoom.gameWidth) / zoom.scale.x) > zoom.smallimage.w ) ? zoom.smallimage.w : (parseInt(zoom.gameWidth / zoom.scale.x));
            $obj.node.h = (parseInt((zoom.gameHeight) / zoom.scale.y) > zoom.smallimage.h ) ? zoom.smallimage.h : (parseInt(zoom.gameHeight / zoom.scale.y));
            $obj.node.css({
                'width': $obj.node.w,
                'height': $obj.node.h
            });
            $obj.node.top = (zoom.smallimage.oh - $obj.node.h - 2) / 2;
            $obj.node.left = (zoom.smallimage.ow - $obj.node.w - 2) / 2;
        };
        $obj.setcenter = function (x, y) {
            if (game.players[Turn.color].computer && !Gui.show) {
                return;
            }
            $obj.node.top = parseInt((parseInt(y) - zoom.gameHeight / 2) / zoom.scale.y);
            $obj.node.left = parseInt((parseInt(x) - zoom.gameWidth / 2) / zoom.scale.x);
            $obj.node.css({
                top: $obj.node.top,
                left: $obj.node.left
            });
            zoom.largeimage.setposition();
        };
        $obj.setposition = function (e) {
            $obj.mousepos.x = e.pageX;
            $obj.mousepos.y = e.pageY;
            var lensleft = 0;
            var lenstop = 0;

            function overleft(lens) {
                return $obj.mousepos.x < zoom.smallimage.pos.l;
            }

            function overright(lens) {
                return $obj.mousepos.x > zoom.smallimage.pos.r;

            }

            function overtop(lens) {
                return $obj.mousepos.y < zoom.smallimage.pos.t;
            }

            function overbottom(lens) {
                return $obj.mousepos.y > zoom.smallimage.pos.b;
            }

            lensleft = $obj.mousepos.x + zoom.smallimage.bleft - zoom.smallimage.pos.l - ($obj.node.w + 2) / 2;
            lenstop = $obj.mousepos.y + zoom.smallimage.btop - zoom.smallimage.pos.t - ($obj.node.h + 2) / 2;
            if (overleft($obj.node)) {
                lensleft = zoom.smallimage.bleft - $obj.node.w / 2;
            } else if (overright($obj.node)) {
                lensleft = zoom.smallimage.w + zoom.smallimage.bleft - $obj.node.w / 2;
            }
            if (overtop($obj.node)) {
                lenstop = zoom.smallimage.btop - $obj.node.h / 2;
            } else if (overbottom($obj.node)) {
                lenstop = zoom.smallimage.h + zoom.smallimage.btop - $obj.node.h / 2;
            }

            $obj.node.left = lensleft;
            $obj.node.top = lenstop;
            $obj.node.css({
                'left': lensleft + 'px',
                'top': lenstop + 'px'
            });
            zoom.largeimage.setposition();
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
    /*========================================================,
     |   LargeImage
     |---------------------------------------------------------:
     |   The large detailed image
     `========================================================*/
    Largeimage: function () {
        var $obj = {};
        $obj.scale = {};
        $obj.loadimage = function () {
            $obj.w = board.width();
            $obj.h = board.height();
            $obj.pos = board.offset();
            $obj.pos.l = board.offset().left;
            $obj.pos.t = board.offset().top;
            $obj.pos.r = $obj.w + $obj.pos.l;
            $obj.pos.b = $obj.h + $obj.pos.t;
            $obj.scale.x = ($obj.w / zoom.smallimage.w);
            $obj.scale.y = ($obj.h / zoom.smallimage.h);

            zoom.scale = $obj.scale;
            //setting lens dimensions;
            zoom.lens.setdimensions(0, 0);
            zoom.lens.show();

            board
                .mousedown(function (e) {
                    if (!Gui.lock) {
                        switch (e.which) {
                            case 1:
                                if (Army.selected) {
                                    var path = AStar.cursorPosition(e.pageX, e.pageY, 1);

                                    if (Army.selected.x == AStar.x && Army.selected.y == AStar.y) {
                                        return;
                                    }

                                    Websocket.move(AStar.x, AStar.y);
                                } else {
                                    // grabbing the map
                                    var pageX = e.pageX;
                                    var pageY = e.pageY;

                                    var centerPageX = zoom.gameWidth / 2;
                                    var centerPageY = zoom.gameHeight / 2;

                                    board.mousemove(function (e) {
                                        zoom.lens.setcenter((centerPageX + (pageX - e.pageX)) - zoom.largeimage.pos.l, (centerPageY + (pageY - e.pageY)) - zoom.largeimage.pos.t);
                                    });
                                }
                                break;
                            case 2:
//                        alert('Middle mouse button pressed');
                                break;
                            case 3:
                                if (Army.selected) {
                                    var destination = AStar.getDestinationXY(e.pageX, e.pageY);

                                    if (Army.selected.x == destination.x && Army.selected.y == destination.y) {
                                        return;
                                    }

                                    Army.deselect();
                                }
                                break;
                            default:
                                alert('You have a strange mouse');
                        }
                    }
                })
                .mousemove(function (e) {
                    if (!Gui.lock) {
                        AStar.cursorPosition(e.pageX, e.pageY);
                    }
                })
                .mouseleave(function () {
                    $('.path').remove();
                    board
                        .unbind('mousemove')
                        .mousemove(function (e) {
                            if (!Gui.lock) {
                                AStar.cursorPosition(e.pageX, e.pageY);
                            }
                        });
                })
                .mouseup(function () {
                    board
                        .unbind('mousemove')
                        .mousemove(function (e) {
                            if (!Gui.lock) {
                                AStar.cursorPosition(e.pageX, e.pageY);
                            }
                        });
                });

            zoom.loaded = true;
        };
        $obj.setposition = function () {
            var left = -zoom.scale.x * (zoom.lens.getoffset().left - zoom.smallimage.bleft + 1);
            var top = -zoom.scale.y * (zoom.lens.getoffset().top - zoom.smallimage.btop + 1);
            board.css({
                'left': left + 'px',
                'top': top + 'px'
            });
        };
        return $obj;
    }
};

zoom.setCenterIfOutOfScreen = function (x, y) {
    var top = parseInt(parseInt(y) / zoom.scale.y);
    var lensTop = parseInt($('.zoomPup').css('top'));
    var lensHeight = parseInt($('.zoomPup').css('height'));

    var maxTop = lensTop + lensHeight - 10;
    var minTop = lensTop + 10;

    var left = parseInt((parseInt(x)) / zoom.scale.x);
    var lensLeft = parseInt($('.zoomPup').css('left'));
    var lensWidth = parseInt($('.zoomPup').css('width'));

    var maxLeft = lensLeft + lensWidth - 20;
    var minLeft = lensLeft + 20;

    if ((top >= maxTop) || (top <= minTop)) {
        lens.setcenter(x, y);
    } else if ((left >= maxLeft) || (left <= minLeft)) {
        lens.setcenter(x, y);
    }
};

