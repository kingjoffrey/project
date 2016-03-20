"use strict"
var Gui = new function () {
    var lock = true,
        show = true,
        chatBox = {close: 0},
        playerBox = {close: 0},
        limitBox = {close: 0},
        mapBox = {close: 0},
        speed = 200,
        documentTitle = 'WoF',
        friendsShow = false,
        changeCloseArrowLR = function (move, el) {
            if (move > 0) {
                $(el).html('&#x25C0');
            } else {
                $(el).html('&#x25B6');
            }
        },
        changeCloseArrowUD = function (move, el) {
            if (move > 0) {
                $(el).html('&#x25C1');
            } else {
                $(el).html('&#x25B7');
            }
        },
        doKey = function (event) {
            if ($(event.target).attr('id') == 'msg') {
                return;
            }
            var key = event.keyCode || event.charCode;
            switch (key) {
                case 27: //ESC
                    Message.remove();
                    CommonMe.deselectArmy()
                    break;
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
                case 66: //b
                    CastleWindow.build()
                    break;
                case 67: //c
                    var army = CommonMe.getSelectedArmy()
                    if (!army) {
                        return
                    }
                    var castle = CommonMe.getCastle(Fields.get(army.getX(), army.getY()).getCastleId())
                    if (isSet(castle)) {
                        CastleWindow.show(castle)
                    }
                    break;
                case 68: //d
                    CommonMe.disband()
                    break;
                case 69: //e
                    Turn.next()
                    break;
                case 70: //f
                    WebSocketSend.fortify()
                    break;
                case 78: //n
                    CommonMe.findNext()
                    break;
                case 79: //o
                    $('.message .go').click()
                    break;
                case 81: //q
                    CommonMe.skip()
                    break;
                case 82: //r
                    WebSocketSend.ruin()
                    break;
                case 83: //s
                    StatusWindow.show()
                    break;
//            default:
//                console.log(key)
            }
        },
        prepareButtons = function () {
            $('#gold').click(function () {
                TreasuryWindow.treasury()
            })

            $('#income').click(function () {
                TreasuryWindow.income()
            })

            $('#costs').click(function () {
                TreasuryWindow.upkeep()
            })

            $('#battleAttack').click(function () {
                BattleWindow.attack()
            })

            $('#battleDefence').click(function () {
                BattleWindow.defence()
            })

            $('#exit').click(function () {
                Gui.exit()
            })

            $('#surrender').click(function () {
                var id = Message.show(translations.surrender, $('<div>').html(translations.areYouSure))
                Message.ok(id, WebSocketSend.surrender)
                Message.cancel(id)
            });

            $('#statistics').click(function () {
                WebSocketSend.statistics();
            })

            $('#config').click(function () {
                ConfigurationWindow.show()
            })

            $('#nextTurn').click(function () {
                Turn.next()
            });

            $('#nextArmy').click(function () {
                CommonMe.findNext()
            })

            $('#armyStatus').click(function () {
                if (!CommonMe.getSelectedArmyId()) {
                    return
                }

                StatusWindow.show()
            });

            $('#deselectArmy').click(function () {
                if (!CommonMe.getSelectedArmyId()) {
                    return;
                }

                CommonMe.deselectArmy()
            });

            $('#heroResurrection').click(function () {
                var id = Message.show(translations.resurrectHero, $('<div>').append(translations.doYouWantToResurrectHeroFor100Gold))
                Message.ok(id, WebSocketSend.resurrection)
                Message.cancel(id)
            })

            $('#heroHire').click(function () {
                var id = Message.show(translations.hireHero, $('<div>').html(translations.doYouWantToHireNewHeroFor1000Gold))
                Message.ok(id, WebSocketSend.hire)
                Message.cancel(id)
            })

            $('#showFriends').click(function () {
                if (friendsShow) {
                    $('#friends').css({
                        display: 'none',
                        top: 0
                    })
                    friendsShow = false
                } else {
                    var height = $('#friends').height()
                    $('#friends').css({
                        display: 'block',
                        height: 0
                    })
                    $('#friends').animate({
                        top: -height - 10 + 'px',
                        height: height + 'px'
                    }, 200)
                    friendsShow = true
                }
            })
        }
    this.prepareBoxes = function () {
        $('#mapBox .close').click(function () {
            var left = parseInt($('#mapBox').css('left')),
                move = -$('#mapBox').width()

            mapBox.el = this;

            if (mapBox.close) {
                move = -move;
            }

            mapBox.move = move;

            $('#mapBox').animate({'left': left + move + 'px'}, speed, function () {
                mapBox.close = !mapBox.close;
                changeCloseArrowLR(mapBox.move, mapBox.el);
            });
        });
        $('#limitBox .close').click(function () {
            var left = parseInt($('#limitBox').css('left')),
                move = -$('#limitBox').width()

            limitBox.el = this

            if (limitBox.close) {
                move = -move
            }

            limitBox.move = move

            $('#limitBox').animate({left: left + move + 'px'}, speed, function () {
                limitBox.close = !limitBox.close;
                changeCloseArrowLR(limitBox.move, limitBox.el);
            })
        })
        $('#playersBox .close').click(function () {
            var right = parseInt($('#playersBox').css('right')),
                move = $('#playersBox').width()

            playerBox.el = this;

            if (playerBox.close) {
                move = -move;
            }

            playerBox.move = move;

            $('#playersBox').animate({right: right - move + 'px'}, speed, function () {
                playerBox.close = !playerBox.close;
                changeCloseArrowLR(playerBox.move, playerBox.el);
            })
        })
        //$('#chatBox .close').click(function () {
        //    Gui.moveChatBox()
        //})
    }
    this.adjust = function () {
        Scene.resize()
        MiniMap.adjust()

        chatBox.close = 0
        playerBox.close = 0
        limitBox.close = 0
        mapBox.close = 0

        if (!Players.countHumans() > 1) {
            $('#chatBox').css({display: 'none'});
        }

        var goldBoxLeft = Scene.getWidth() / 2 - $('#goldBox').outerWidth() / 2

        $('#goldBox').css({
            'left': goldBoxLeft + 'px'
        })
        $('#mapBox .close').css({
            left: $('#mapBox').width() + 4 + 'px'
        })
        $('#limitBox .close').css({
            left: $('#limitBox').width() + 4 + 'px'
        })
        $('#limitBox').css({
            top: Fields.getHeight() + 30 + 'px'
        })

        //Message.adjust()
        //Message.setOverflowHeight()
    }
    this.exit = function () {
        window.location = '/' + lang + '/index'
    }
    this.end = function () {
        window.location = '/' + lang + '/over/index/id/' + gameId
    }
    this.unlock = function () {
        lock = false;
        $('#nextTurn').removeClass('buttonOff');
        $('#nextArmy').removeClass('buttonOff');
        //makeMyCursorUnlock();
    }
    this.setLock = function () {
        lock = true
        $('#nextTurn').addClass('buttonOff');
        $('#nextArmy').addClass('buttonOff');
        //makeMyCursorLock();
    }
    this.getDocumentTitle = function () {
        return documentTitle
    }
    this.getShow = function () {
        return show
    }
    this.moveChatBox = function (func) {
        $('#chatBox').removeClass('mini')
    }
    this.titleBlink = function (msg) {
        var timeoutId = Game.getTimeoutId()
        if (timeoutId) {
            clearInterval(timeoutId);
        }
        Game.setTimeoutId(setInterval(function () {
            if (document.title == msg) {
                document.title = '...'
            } else {
                document.title = msg
            }
        }))

        $(document).bind("mousemove keypress", function () {
            clearInterval(Game.getTimeoutId())
            document.title = Gui.getDocumentTitle()
            window.onmousemove = null
        })
    }
    this.init = function () {
        $(window).resize(function () {
            Gui.adjust()
        })

        $('#mapImage').css({
            width: Fields.getWidth() + 'px',
            height: Fields.getHeight() + 'px'
        })

        Game.getMapElement().css({
            width: Fields.getWidth() + 'px',
            height: Fields.getHeight() + 'px'
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
                if (Scene.getCameraY() < 230) {
                    Scene.moveCameraAway()
                }
            } else {
                if (Scene.getCameraY() > 22) {
                    Scene.moveCameraClose()
                }
            }
        })

        MiniMap.init()
        $('#mapBox').css({
            width: Fields.getWidth() + 'px',
            height: Fields.getHeight() + 18 + 'px'
        });
        $('#terrain').css('top', Fields.getHeight() + 4 + 'px')
        prepareButtons()
        this.prepareBoxes()
        this.adjust()
    }
}