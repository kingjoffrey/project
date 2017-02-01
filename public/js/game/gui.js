"use strict"
var GameGui = new function () {
    var lock = true,
        show = true,
        documentTitle = 'WoF',
        friendsShow = false,
        doKey = function (event) {
            if ($('#game').length == 0) {
                return
            }
            var key = event.keyCode || event.charCode;
            switch (key) {
                case 27: //ESC
                    Message.remove();
                    CommonMe.deselectArmy()
                    break;
                case 32: //SPACE
                    CommonMe.skip()
                    break;
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
                    WebSocketSendCommon.fortify()
                    break;
                case 78: //n
                    CommonMe.findNext()
                    break;
                case 13: //enter
                    $('.message .go').click()
                    break;
                case 82: //r
                    WebSocketSendCommon.ruin()
                    break;
                case 83: //s
                    StatusWindow.show()
                    break;
                case 84: //t
                    Turn.next()
                    break;
                default:
                    console.log(key)
            }
        },
        prepareButtons = function () {
            $('#gold').click(function () {
                Sound.play('click')
                TreasuryWindow.treasury()
            })

            $('#income').click(function () {
                Sound.play('click')
                TreasuryWindow.income()
            })

            $('#costs').click(function () {
                Sound.play('click')
                TreasuryWindow.upkeep()
            })

            $('#exit').click(function () {
                Sound.play('click')
                GameGui.exit()
            })

            $('#surrender').click(function () {
                Sound.play('click')
                var id = Message.show(translations.surrender, $('<div>').html(translations.areYouSure))
                Message.ok(id, WebSocketSendCommon.surrender)
                Message.cancel(id)
            });

            $('#statistics').click(function () {
                Sound.play('click')
                WebSocketSendCommon.statistics();
            })

            $('#nextTurn').click(function () {
                Sound.play('click')
                Turn.next()
            })

            $('#nextArmy').click(function () {
                Sound.play('click')
                CommonMe.findNext()
            })

            $('#armyStatus').click(function () {
                if (!CommonMe.getSelectedArmyId()) {
                    return
                }

                StatusWindow.show()
            })

            $('#deselectArmy').click(function () {
                if (!CommonMe.getSelectedArmyId()) {
                    return;
                }

                CommonMe.deselectArmy()
            })

            $('#show').click(function () {
                Sound.play('click')
                GameGui.setShow(!GameGui.getShow())
                if (GameGui.getShow()) {
                    $(this).children().attr('src', '/img/game/show.png')
                } else {
                    $(this).children().attr('src', '/img/game/show_off.png')
                }
            })
            $('#sound').click(function () {
                Sound.play('click')
                Sound.mute = !Sound.mute
                if (Sound.mute) {
                    $(this).children().attr('src', '/img/game/sound_off.png')
                } else {
                    $(this).children().attr('src', '/img/game/sound_on.png')
                }
            })
            $('#fullScreen').click(function () {
                Sound.play('click')
                var elem = document.getElementById('main');
                if (elem.requestFullscreen) {
                    elem.requestFullscreen()
                } else if (elem.msRequestFullscreen) {
                    elem.msRequestFullscreen()
                } else if (elem.mozRequestFullScreen) {
                    elem.mozRequestFullScreen()
                } else if (elem.webkitRequestFullscreen) {
                    elem.webkitRequestFullscreen()
                }
            })
            $('#showMenu').click(function () {
                GameRenderer.stop()
                $('#game').hide()
                $('#gameMenu').show()
            })
            $('#close').click(function () {
                GameRenderer.start()
                $('#game').show()
                $('#gameMenu').hide()
            })
            // $('#').click(function () {
            //
            // })
        }
    this.adjust = function () {
        GameScene.resize($(window).innerWidth(), $(window).innerHeight())
        BattleScene.resize($(window).innerWidth(), $(window).innerHeight())
        GameRenderer.setSize($(window).innerWidth(), $(window).innerHeight())

        var goldBoxLeft = $(window).innerWidth() / 2 - $('#gold').outerWidth() / 2
        $('#gold').css({
            'left': goldBoxLeft + 'px'
        })
    }
    this.exit = function () {
        WebSocketSendMain.controller('index', 'index')
    }
    this.end = function () {
        WebSocketSendMain.controller('over', 'index', {'id': Game.getGameId()})
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
    this.setShow = function (s) {
        show = s
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
            document.title = GameGui.getDocumentTitle()
            window.onmousemove = null
        })
    }
    this.init = function () {
        $(window).resize(function () {
            GameGui.adjust()
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

        prepareButtons()
        this.adjust()
    }
}
