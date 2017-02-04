"use strict"
var GameGui = new function () {
    var lock = true,
        show = true,
        timeoutId = null,
        documentTitle,
        friendsShow = false,
        init = 0,
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
                    WebSocketSendGame.fortify()
                    break;
                case 78: //n
                    CommonMe.findNext()
                    break;
                case 79: //m
                    GameRenderer.stop()
                    $('#game').hide()
                    $('#gameMenu').show()
                    break;
                case 13: //enter
                    $('.message .go').click()
                    break;
                case 82: //r
                    WebSocketSendGame.ruin()
                    break;
                case 83: //s
                    StatusWindow.show()
                    break;
                case 84: //t
                    Turn.next()
                    break;
                // default:
                //     console.log(key)
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

            $('#gameMenu #exit').click(function () {
                Sound.play('click')
                IndexController.index()
            })

            $('#surrender').click(function () {
                Sound.play('click')
                var id = Message.show(translations.surrender, $('<div>').html(translations.areYouSure))
                Message.ok(id, WebSocketSendGame.surrender)
                Message.cancel(id)
            });

            $('#statistics').click(function () {
                Sound.play('click')
                WebSocketSendGame.statistics();
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
                Sound.play('click')
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
                $('#gameMenu').show()
            })
            $('#close').click(function () {
                GameRenderer.start()
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

        var goldButtonLeft = $(window).innerWidth() / 2 - $('#gold').outerWidth() / 2
        $('#gold').css({
            'left': goldButtonLeft + 'px'
        })

        if (init) {
            console.log('no to dupa')
            Message.adjust()
        }
        init = 1
    }
    this.end = function () {
        WebSocketSendMain.controller('over', 'index', {'id': Game.getGameId()})
    }
    this.unlock = function () {
        lock = false;
        $('#nextTurn').removeClass('buttonOff');
        $('#nextArmy').removeClass('buttonOff');
    }
    this.setLock = function () {
        lock = true
        $('#nextTurn').addClass('buttonOff');
        $('#nextArmy').addClass('buttonOff');
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
        if (timeoutId) {
            clearInterval(timeoutId);
        }

        timeoutId = setInterval(function () {
            if (document.title == msg) {
                document.title = '...'
            } else {
                document.title = msg
            }
        }, 1000)

        $(document).bind("mousemove keypress", function () {
            clearInterval(timeoutId)
            document.title = GameGui.getDocumentTitle()
            window.onmousemove = null
        })
    }
    this.init = function () {
        $(window).resize(function () {
            GameGui.adjust()
        })

        documentTitle = document.title

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
