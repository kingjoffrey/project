"use strict"
var GameGui = new function () {
    var lock = true,
        show = true,
        timeoutId = null,
        documentTitle,
        friendsShow = false,
        afterInit = 0,
        doKey = function () {
            $('#game').on('keydown', function (event) {
                var key = event.keyCode || event.charCode;
                switch (key) {
                    case 27: //ESC
                        Message.remove()
                        Me.deselectArmy()
                        break;
                    case 32: //SPACE
                        Me.skip()
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
                        var army = Me.getSelectedArmy()
                        if (!army) {
                            return
                        }
                        var castle = Me.getCastle(Fields.get(army.getX(), army.getY()).getCastleId())
                        if (isSet(castle)) {
                            CastleWindow.show(castle)
                        }
                        break;
                    case 68: //d
                        Me.disband()
                        break;
                    case 69: //e
                        Turn.next()
                        break;
                    case 70: //f
                        WebSocketSendGame.fortify()
                        break;
                    case 78: //n
                        Me.findNext()
                        break;
                    case 79: //m
                        menu()
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
            })
        },
        menu = function () {
            Message.remove()
            GameRenderer.stop()
            $('#gameMenu').show()
        }
    this.adjust = function () {
        GameScene.resize($(window).innerWidth(), $(window).innerHeight())
        GameRenderer.setSize($(window).innerWidth(), $(window).innerHeight())

        var goldButtonLeft = $(window).innerWidth() / 2 - $('#goldAndStats').outerWidth() / 2
        $('#goldAndStats').css({
            'left': goldButtonLeft + 'px'
        })

        if (afterInit) {
            Message.adjust()
        }
    }
    this.end = function () {
        WebSocketSendMain.controller('over', 'index', {'id': Game.getGameId()})
    }
    this.unlock = function () {
        lock = false;
        $('#nextTurn').removeClass('buttonOff')
        $('#nextArmy').removeClass('buttonOff')
        $('#wait').hide()
    }
    this.lock = function () {
        lock = true
        $('#nextTurn').addClass('buttonOff')
        $('#nextArmy').addClass('buttonOff')
        $('#wait').show()
    }
    this.getLock = function () {
        return lock
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

        $(document).on('mousemove keypress', function () {
            clearInterval(timeoutId)
            document.title = GameGui.getDocumentTitle()
            window.onmousemove = null
        })
    }
    this.init = function () {
        $('#game').show().off()
        $('.game').show()
        $('#castleButtons').hide()

        $(window).resize(function () {
            GameGui.adjust()
        })

        // document.addEventListener('webkitfullscreenchange', GameGui.adjust(), false);
        // document.addEventListener('mozfullscreenchange', GameGui.adjust(), false);
        document.addEventListener('fullscreenchange', GameGui.adjust(), false)
        // document.addEventListener('MSFullscreenChange', GameGui.adjust(), false);

        $('#terrain').removeClass('editor').addClass('game')

        documentTitle = document.title

        doKey()
        this.adjust()
        afterInit = 1
    }
}
