"use strict"
var GameGui = new function () {
    var lock = true,
        show = true,
        documentTitle = 'WoF',
        friendsShow = false,
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
                case 79: //o
                    $('.message .go').click()
                    break;
                case 82: //r
                    WebSocketSendCommon.ruin()
                    break;
                case 83: //s
                    StatusWindow.show()
                    break;
                //default:
                //    console.log(key)
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
                GameGui.exit()
            })

            $('#surrender').click(function () {
                var id = Message.show(translations.surrender, $('<div>').html(translations.areYouSure))
                Message.ok(id, WebSocketSendCommon.surrender)
                Message.cancel(id)
            });

            $('#statistics').click(function () {
                WebSocketSendCommon.statistics();
            })

            $('#config').click(function () {
                ConfigurationWindow.show()
            })

            $('#nextTurn').click(function () {
                Turn.next()
            })

            $('#nextArmy').click(function () {
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
    this.adjust = function () {
        GameScene.resize($(window).innerWidth(), $(window).innerHeight())

//         MiniMap.adjust()

//         console.log(Players.countHumans())
        
//         if (!Players.countHumans() > 1) {
//             console.log('b')
            $('#chatBox').css({display: 'none'})
//         }

        var goldBoxLeft = GameScene.getWidth() / 2 - $('#gold').outerWidth() / 2

        $('#gold').css({
            'left': goldBoxLeft + 'px'
        })
    }
    this.exit = function () {
        WebSocketSendMain.controller('index', 'index')
    }
    this.end = function () {
        WebSocketSendMain.controller('over', 'index', {'id': gameId})
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
