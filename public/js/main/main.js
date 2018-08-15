"use strict"
var Main = new function () {
    var development = 0,
        serverDisconnectedMessage = '',
        addButtonsClicks = function () {
            $('#game #gold').off().click(function () {
                Sound.play('click')
                TreasuryWindow.treasury()
            })

            $('#game #statistics').off().click(function () {
                Sound.play('click')
                WebSocketSendGame.statistics();
            })

            $('#game #nextTurn').off().click(function () {
                Sound.play('click')
                Turn.next()
            })
            $('#game #showMenu').off().click(function () {
                Sound.play('click')
                GameGui.menu()
            })

            $('#game #nextArmy').off().click(function () {
                Sound.play('click')
                Me.findNext()
            })

            $('#castleButtons #close').off().click(function () {
                CastleWindow.hide()
            })

            $('#gameMenu #exit').off().click(function () {
                Sound.play('click')
                IndexController.index()
            })
            $('#gameMenu #surrender').off().click(function () {
                Sound.play('click')
                var id = Message.show(translations.surrender, $('<div>').html(translations.areYouSure))
                Message.addButton(id, 'surrender', function () {
                    WebSocketSendGame.surrender()
                    GameRenderer.start()
                    $('#gameMenu').hide()
                })
                Message.addButton(id, 'cancel')
            })
            $('#gameMenu #show').off().click(function () {
                Sound.play('click')
                GameGui.setShow(!GameGui.getShow())
                if (GameGui.getShow()) {
                    $(this).removeClass('off')
                } else {
                    $(this).addClass('off')
                }
            })
            $('#gameMenu #sound').off().click(function () {
                Sound.play('click')
                Sound.setMute(!Sound.getMute())
                if (Sound.getMute()) {
                    $(this).addClass('off')
                } else {
                    $(this).removeClass('off')
                }
            })
            $('#gameMenu #fullScreen').off().click(function () {
                Sound.play('click')
                Page.fullScreen()
            })
            $('#gameMenu #close').off().click(function () {
                Sound.play('click')
                $('#gameMenu').hide()
                GameRenderer.start()
                GameRenderer.animate()
            })
        },
        click = function (controller) {
            return function () {
                Sound.play('click')
                WebSocketSendMain.controller(controller)
            }
        }
    this.setEnv = function (val) {
        if (val == 'development') {
            development = 1
        }
    }
    this.getEnv = function () {
        return development
    }
    this.createMenu = function (menu) {
        serverDisconnectedMessage = $('#menu').html()

        $('#menu').html('')

        for (var controller in menu) {
            $('#menu').append(
                $('<div>').addClass('iconButton buttonColors')
                    .click(click(controller))
                    .append(
                        $('<div>').html(menu[controller]).addClass('varchar')
                    )
                    .attr('id', controller)
            )
        }

        $('#menu').append(
            $('<div>').addClass('iconButton buttonColors')
                .click(click('messages'))
                .append($('<div>').html('&nbsp;&nbsp;&nbsp;').addClass('varchar'))
                .attr({
                    id: 'messages',
                    title: 'Messages'
                })
        ).append(
            $('<div>').addClass('iconButton buttonColors')
                .click(function () {
                    Sound.play('click')
                    Page.fullScreen()
                })
                .append($('<div>').html('&nbsp;&nbsp;&nbsp;').addClass('varchar'))
                .attr({
                    id: 'fullScreen',
                    title: 'Full screen'
                })
        )
    }
    this.createContent = function (r) {
        var className = capitalizeFirstLetter(r.type + 'Controller')
        if (typeof window[className] !== "undefined") {
            var methodName = r.action
            if (typeof window[className][methodName] === "function") {
                window[className][methodName](r)

                $('#menuTop').hide()
                $('#menu .active').removeClass('active')
                $('#menu #' + r.type).addClass('active')

                if (r.type == 'help') {
                    $('#content')
                        .append($('<div>')
                            .append(
                                $('<div>').attr('id', 'back').addClass('button buttonColors').html(translations.Mainmenu).click(function () {
                                    Sound.play('click')
                                    IndexController.index()
                                })
                            ).css({
                                'text-align': 'right'
                            })
                        )
                }
            } else {
                console.log('Method ' + methodName + ' in class ' + className + ' !exists')
            }
        } else {
            console.log('Class ' + className + ' !exists')
        }
    }
    this.updateMenuClick = function () {
        $('#menu a').each(function () {
            $(this).click(click($(this).attr('id')))
        })
    }
    this.init = function () {
        WebSocketMain.init()
        addButtonsClicks()
    }
    this.serverDisconnect = function () {
        $('#menu').html(serverDisconnectedMessage)
    }
}

var Page = new function () {
    var index = '',
        shadows = 1,
        isInFullScreen = function () {
            return (document.fullscreenElement && document.fullscreenElement !== null) ||
                (document.webkitFullscreenElement && document.webkitFullscreenElement !== null) ||
                (document.mozFullScreenElement && document.mozFullScreenElement !== null) ||
                (document.msFullscreenElement && document.msFullscreenElement !== null)
        }

    this.getIndex = function () {
        return index
    }
    this.getShadows = function () {
        return shadows
    }
    this.setShadows = function (s) {
        shadows = s
    }
    this.fullScreen = function () {
        var docElm = document.documentElement

        if (!isInFullScreen()) {
            $('#fullScreen div').addClass('full')

            if (docElm.requestFullscreen) {
                docElm.requestFullscreen();
            } else if (docElm.mozRequestFullScreen) {
                docElm.mozRequestFullScreen();
            } else if (docElm.webkitRequestFullScreen) {
                docElm.webkitRequestFullScreen();
            } else if (docElm.msRequestFullscreen) {
                docElm.msRequestFullscreen();
            }

            $('.askFullScreen').hide()

        } else {
            $('#fullScreen div.full').removeClass('full')

            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    }

    this.init = function () {
        $('#lang').change(function () {
            window.location = '/' + $(this).val() + '/login'
        })

        var url_parts = window.location.pathname.split('/')

        $('#lang option').each(function () {
            if (url_parts[1] == $(this).val()) {
                $(this).attr('selected', '')
            }
        })

        $('#bg').scroll(function () {
            var x = $(this).scrollTop();
            $(this).css('background-position', '0% ' + parseInt(-x / 10) + 'px');
        })

        if (!index) {
            index = $('#content').html()
        }

        // console.log(window.orientation)
        // console.log(isTouchDevice())

        if (isTouchDevice()) {
            $('body').addClass('touchscreen')
            shadows = 0
        }

        if ($(window).innerWidth() < $(window).innerHeight()) {
            $('body').addClass('vertical')
        }

        if (!$('#menuBox #menu').length) {
            return
        }

        if (!isInFullScreen()) {
            $('#content').append($('<div>').addClass('askFullScreen')
                .append($('<div>').html(translations.SwitchtoFullScreen).addClass('question'))
                .append(
                    $('<div>')
                        .append($('<div>').addClass('button buttonColors').html(translations.No).click(function () {
                            Sound.play('click')
                            $('.askFullScreen').remove()
                        }))
                        .append($('<div>').addClass('button buttonColors').html(translations.Yes).click(function () {
                            Sound.play('click')
                            Page.fullScreen()
                            $('.askFullScreen').remove()
                        }))
                )
            )
        }
    }
}