var Gui = {
    lock: true,
    show: true,
    armyBox: {'close': 0},
    chatBox: {'close': 0},
    playerBox: {'close': 0},
    limitBox: {'close': 0},
    timerBox: {'close': 0},
    mapBox: {'close': 0},
    speed: 200,
    documentTitle: null,
    init: function () {
        $(window).resize(function () {
            Gui.adjust();
        })
        documentTitle = 'WoF'
        map = $('#map')
        coord = $('#coord')
        Zoom.init()
        this.prepareButtons()
        this.adjust()
        $('body').mousewheel(function (event) {
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
        });
    },
    doKey: function (event) {
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
                Me.fortify()
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
    },
    prepareButtons: function () {
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
            Me.fortify()
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
            Castle.show();
        });

        $('#showArtifacts').click(function () {
            Message.showArtifacts();
        });

        $('#mapBox .close').click(function () {
            var left = parseInt($('#mapBox').css('left'));
            var move = -220;
            Gui.mapBox['el'] = this;

            if (Gui.mapBox['close']) {
                move = -move;
            }

            Gui.mapBox['move'] = move;

            $('#mapBox').animate({'left': left + move + 'px'}, Gui.speed, function () {
                Gui.mapBox['close'] = !Gui.mapBox['close'];
                Gui.changeCloseArrowLR(Gui.mapBox['move'], Gui.mapBox['el']);
            });
        });
        $('#limitBox .close').click(function () {
            var left = parseInt($('#limitBox').css('left'));
            var move = -220;
            Gui.limitBox['el'] = this;

            if (Gui.limitBox['close']) {
                move = -move;
            }

            Gui.limitBox['move'] = move;

            $('#limitBox').animate({'left': left + move + 'px'}, Gui.speed, function () {
                Gui.limitBox['close'] = !Gui.limitBox['close'];
                Gui.changeCloseArrowLR(Gui.limitBox['move'], Gui.limitBox['el']);
            });
        });
        $('#timerBox .close').click(function () {
            var left = parseInt($('#timerBox').css('left'));
            var move = -220;
            Gui.timerBox['el'] = this;

            if (Gui.timerBox['close']) {
                move = -move;
            }

            Gui.timerBox['move'] = move;

            $('#timerBox').animate({'left': left + move + 'px'}, Gui.speed, function () {
                Gui.timerBox['close'] = !Gui.timerBox['close'];
                Gui.changeCloseArrowLR(Gui.timerBox['move'], Gui.timerBox['el']);
            });
        });
        $('#playersBox .close').click(function () {
            var left = parseInt($('#playersBox').css('left'));
            var move = 220;
            Gui.playerBox['el'] = this;

            if (Gui.playerBox['close']) {
                move = -move;
            }

            Gui.playerBox['move'] = move;

            $('#playersBox').animate({'left': left + move + 'px'}, Gui.speed, function () {
                Gui.playerBox['close'] = !Gui.playerBox['close'];
                Gui.changeCloseArrowLR(Gui.playerBox['move'], Gui.playerBox['el']);
            });
        });
        $('#chatBox .close').click(function () {
            var left = parseInt($('#chatBox').css('left'));
            var move = 490;
            Gui.chatBox['el'] = this;

            if (Gui.chatBox['close']) {
                move = -move;
            }

            Gui.chatBox['move'] = move;

            $('#chatBox').animate({'left': left + move + 'px'}, Gui.speed, function () {
                Gui.chatBox['close'] = !Gui.chatBox['close'];
                Gui.changeCloseArrowLR(Gui.chatBox['move'], Gui.chatBox['el']);
            });
        });
        $('#armyBox .close').click(function () {
            var left = parseInt($('#armyBox').css('left'));
            var move = 220;
            Gui.armyBox['el'] = this;

            if (Gui.armyBox['close']) {
                move = -move;
            }

            Gui.armyBox['move'] = move;

            $('#armyBox').animate({'left': left + Gui.armyBox['move'] + 'px'}, Gui.speed, function () {
                Gui.armyBox['close'] = !Gui.armyBox['close'];
                Gui.changeCloseArrowLR(Gui.armyBox['move'], Gui.armyBox['el']);
            });
        });
    },
    changeCloseArrowLR: function (move, el) {
        if (move > 0) {
            $(el).html('&#x25C0');
        } else {
            $(el).html('&#x25B6');
        }
    },
    changeCloseArrowUD: function (move, el) {
        if (move > 0) {
            $(el).html('&#x25C1');
        } else {
            $(el).html('&#x25B7');
        }
    },
    adjust: function () {
        this.armyBox.close = 0
        this.chatBox.close = 0
        this.playerBox.close = 0
        this.limitBox.close = 0
        this.timerBox.close = 0
        this.mapBox.close = 0

        var mapBoxHeight = parseInt($('#mapImage').css('height'));

        $('#mapBox').css({
            width: parseInt($('#mapImage').css('width')) + 20 + 'px',
            height: mapBoxHeight + 40 + 'px'
        });

        var minWidth = parseInt($('#mapBox').css('width')) + parseInt($('#playersBox').css('width')) + 450
        var minHeight = parseInt($('#playersBox').css('height')) + parseInt($('#armyBox').css('height')) + 50

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

        var numberOfHumanPlayers = 0
        for (sn in game.players) {
            if (!game.players[sn].computer) {
                numberOfHumanPlayers++
            }
        }

        if (numberOfHumanPlayers > 1) {
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

        $('#battleSettingsBox').css({
            'left': 20 + goldBoxLeft + parseInt($('#goldBox').width()) + 'px'
        })

        var left = Zoom.gameWidth - 237;

        $('#playersBox').css({
            'left': left + 'px'
        });
        $('#armyBox').css({
            top: parseInt($('#playersBox').css('height')) + 19 + 'px',
            'left': left + 'px'
        })

        $('#terrain').css('top', mapBoxHeight + 19 + 'px');

        var closeLeft = parseInt($('#mapImage').css('width')) + 30

        $('#mapBox .close').css({
            left: closeLeft + 'px'
        })
        $('#limitBox .close').css({
            left: closeLeft + 'px'
        })
        $('#timerBox .close').css({
            left: closeLeft + 'px'
        })

        $('#limitBox').css({
            top: mapBoxHeight + 51 + 'px'
        })

        $('#timerBox').css({
            top: mapBoxHeight + 72 + parseInt($('#limitBox').css('height')) + 'px'
        })

        Message.adjust()
        Message.setOverflowHeight()
    },
    exit: function () {
        window.location = '/' + lang + '/index'
    },
    end: function () {
        window.location = '/' + lang + '/over/index/id/' + gameId
    },
    unlock: function () {
        Gui.lock = false;
        $('#nextTurn').removeClass('buttonOff');
        $('#nextArmy').removeClass('buttonOff');
        //makeMyCursorUnlock();
    },
    setLock: function () {
        Gui.lock = true;
        $('#nextTurn').addClass('buttonOff');
        $('#nextArmy').addClass('buttonOff');
        //makeMyCursorLock();
    }


}