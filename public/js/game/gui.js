var Gui = new function () {
    var lock = true,
        show = true,
        commandsBox = {'close': 0},
        chatBox = {'close': 0},
        playerBox = {'close': 0},
        limitBox = {'close': 0},
        mapBox = {'close': 0},
        speed = 200,
        documentTitle = 'WoF',
        leftMenuWidth = 220,
        minHeight = 700,
        minWidth = 960,
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
        }


    this.init = function () {
        $(window).resize(function () {
            console.log('aaa')
            Gui.adjust()
        })
        $('body')
            .keydown(Gui.doKey(arguments[0] || window.event))
            .on('contextmenu', function () {
                return false
            })
            .on('dragstart', function () {
                return false
            })

        this.prepareButtons()
        this.prepareBoxes()
        this.adjust()
        $('#game canvas').mousewheel(function (event) {
            if (event.deltaY > 0) {
                if (Three.getCamera().position.y < 230) {
                    Three.getCamera().position.y += 2

                    Three.getCamera().position.x -= 2
                    Three.getCamera().position.z += 2
                }
            } else {
                if (Three.getCamera().position.y > 22) {
                    Three.getCamera().position.y -= 2

                    Three.getCamera().position.x += 2
                    Three.getCamera().position.z -= 2
                }
            }
        })
    }
    this.doKey = function (event) {
        if ($(event.target).attr('id') == 'msg') {
            return;
        }
        var key = event.keyCode || event.charCode;
        switch (key) {
            case 27: //ESC
                Message.remove();
                Me.deselectArmy()
                break;
            case 37://left
                Three.getCamera().position.x += -0.5
                Three.getCamera().position.z += -0.5
                break;
            case 38://up
                Three.getCamera().position.x += 0.5
                Three.getCamera().position.z += -0.5
                break;
            case 39://right
                Three.getCamera().position.x += 0.5
                Three.getCamera().position.z += 0.5
                break;
            case 40://down
                Three.getCamera().position.x += -0.5
                Three.getCamera().position.z += 0.5
                break;
            case 66: //b
                Message.build()
                break;
            case 67: //c
                Castle.show();
                break;
            case 68: //d
                Message.disband();
                break;
            case 69: //e
                Message.nextTurn();
                break;
            case 70: //f
                Websocket.fortify()
                break;
            case 78: //n
                Me.findNext()
                break;
            case 79: //o
                $('.message .go').click()
                break;
            case 81: //q
                Me.skip()
                break;
            case 82: //r
                Websocket.ruin()
                break;
            case 83: //s
                Message.armyStatus()
                break;
//            default:
//                console.log(key)
        }
    }
    this.prepareButtons = function () {
        $('#gold').click(function () {
            Message.treasury()
        })

        $('#income').click(function () {
            Message.income()
        })

        $('#costs').click(function () {
            Message.upkeep()
        })

        $('#battleAttack').click(function () {
            Message.battleAttack()
        })

        $('#battleDefence').click(function () {
            Message.battleDefence()
        })

        $('#exit').click(function () {
            Gui.exit()
        });

        $('#show').click(function () {
            Sound.play('click');
            Gui.show = !Gui.show;
            if (Gui.show) {
                $(this).children().attr('src', '/img/game/show.png')
            } else {
                $(this).children().attr('src', '/img/game/show_off.png')
            }
        });

        $('#sound').click(function () {
            Sound.play('click');
            Sound.mute = !Sound.mute;
            if (Sound.mute) {
                $(this).children().attr('src', '/img/game/sound_off.png')
            } else {
                $(this).children().attr('src', '/img/game/sound_on.png')
            }
        });

        $('#surrender').click(function () {
            Message.surrender()
        });

        $('#statistics').click(function () {
            Websocket.statistics();
        });

        $('#send').click(function () {
            Websocket.chat();
        });

        $('#msg').keypress(function (e) {
            if (e.which == 13) {
                Websocket.chat();
            }
        });

        $('#nextTurn').click(function () {
            Message.nextTurn();
        });

        $('#nextArmy').click(function () {
            Me.findNext()
        })
        ;
        $('#skipArmy').click(function () {
            Me.skip()
        });

        $('#quitArmy').click(function () {
            Websocket.fortify()
        });

        $('#splitArmy').click(function () {
            if (!Me.getSelectedArmyId()) {
                return
            }

            Message.split()
        })

        $('#armyStatus').click(function () {
            if (!Me.getSelectedArmyId()) {
                return
            }

            Message.armyStatus()
        });

        $('#disbandArmy').click(function () {
            Message.disband();
        });

        $('#deselectArmy').click(function () {
            if (!Me.getSelectedArmyId()) {
                return;
            }

            Me.deselectArmy()
        });

        $('#searchRuins').click(function () {
            Websocket.ruin()
        });

        $('#heroResurrection').click(function () {
            Message.resurrection()
        })

        $('#heroHire').click(function () {
            Message.hire()
        });

        $('#razeCastle').click(function () {
            Message.raze();
        });

        $('#buildCastleDefense').click(function () {
            Message.build();
        });

        $('#showCastle').click(function () {
            var army = Me.getArmy(Me.getSelectedArmyId())
            var castle = Me.getCastle(Fields.get(army.getX(), army.getY()).getCastleId())
            if (isSet(castle)) {
                CastleWindow.show(castle)
            }
        })

        $('#showArtifacts').click(function () {
            Message.showArtifacts();
        });
    }
    this.prepareBoxes = function () {
        $('#mapBox .close').click(function () {
            var left = parseInt($('#mapBox').css('left')),
                move = -leftMenuWidth

            mapBox['el'] = this;

            if (mapBox['close']) {
                move = -move;
            }

            mapBox['move'] = move;

            $('#mapBox').animate({'left': left + move + 'px'}, speed, function () {
                mapBox['close'] = !mapBox['close'];
                changeCloseArrowLR(mapBox['move'], mapBox['el']);
            });
        });
        $('#limitBox .close').click(function () {
            var left = parseInt($('#limitBox').css('left')),
                move = -leftMenuWidth

            limitBox['el'] = this;

            if (limitBox['close']) {
                move = -move;
            }

            limitBox['move'] = move;

            $('#limitBox').animate({'left': left + move + 'px'}, speed, function () {
                limitBox['close'] = !limitBox['close'];
                changeCloseArrowLR(limitBox['move'], limitBox['el']);
            });
        });
        $('#playersBox .close').click(function () {
            var left = parseInt($('#playersBox').css('left')),
                move = leftMenuWidth

            playerBox['el'] = this;

            if (playerBox['close']) {
                move = -move;
            }

            playerBox['move'] = move;

            $('#playersBox').animate({'left': left + move + 'px'}, speed, function () {
                playerBox['close'] = !playerBox['close'];
                changeCloseArrowLR(playerBox['move'], playerBox['el']);
            });
        });
        $('#chatBox .close').click(function () {
            var left = parseInt($('#chatBox').css('left')),
                move = 490

            chatBox['el'] = this;

            if (chatBox['close']) {
                move = -move;
            }

            chatBox['move'] = move;

            $('#chatBox').animate({'left': left + move + 'px'}, speed, function () {
                chatBox['close'] = !chatBox['close'];
                changeCloseArrowLR(chatBox['move'], chatBox['el']);
            });
        });
        $('#commandsBox .close').click(function () {
            var left = parseInt($('#commandsBox').css('left')),
                move = leftMenuWidth

            commandsBox['el'] = this;

            if (commandsBox['close']) {
                move = -move;
            }

            commandsBox['move'] = move;

            $('#commandsBox').animate({'left': left + commandsBox['move'] + 'px'}, speed, function () {
                commandsBox['close'] = !commandsBox['close'];
                changeCloseArrowLR(commandsBox['move'], commandsBox['el']);
            });
        });
    }
    this.adjust = function () {
        Zoom.init()

        commandsBox.close = 0
        chatBox.close = 0
        playerBox.close = 0
        limitBox.close = 0
        mapBox.close = 0

        if (Zoom.gameWidth < minWidth) {
            Zoom.gameWidth = minWidth
        }
        if (Zoom.gameHeight < minHeight) {
            Zoom.gameHeight = minHeight
        }

        $('#game').css({
                width: Zoom.gameWidth + 'px',
                height: Zoom.gameHeight + 'px'
            }
        )

        Three.resize()

        var mapBoxHeight = parseInt($('#mapImage').css('height'))

        $('#mapBox').css({
            width: parseInt($('#mapImage').css('width')) + 7 + 'px',
            height: mapBoxHeight + 24 + 'px'
        });

        if (Players.countHumans() > 1) {
            var chatLeft = Zoom.gameWidth - 507;
            var chatTop = Zoom.gameHeight - 169;

            $('#chatBox').css({
                'left': chatLeft + 'px',
                'top': chatTop + 'px'
            });
        } else {
            $('#chatBox').css({display: 'none'});
        }

        var goldBoxLeft = Zoom.gameWidth / 2 - $('#goldBox').outerWidth() / 2

        $('#goldBox').css({
            'left': goldBoxLeft + 'px'
        })

        var left = Zoom.gameWidth - $('#playersBox').width();

        $('#playersBox').css({
            'left': left + 'px'
        });
        $('#commandsBox').css({
            top: parseInt($('#playersBox').css('height')) + 14 + 'px',
            'left': left + 'px'
        })
        $('#armyBox').css({
            top: Zoom.gameHeight - 34 + 'px',
            'left': Zoom.gameWidth / 2 - 115 + 'px'
        })

        $('#terrain').css('top', mapBoxHeight + 12 + 'px');

        $('#mapBox .close').css({
            left: $('#mapBox').width() + 4 + 'px'
        })
        $('#limitBox .close').css({
            left: $('#limitBox').width() + 4 + 'px'
        })

        $('#limitBox').css({
            top: mapBoxHeight + 30 + 'px'
        })

        Message.adjust()
        Message.setOverflowHeight()
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
}