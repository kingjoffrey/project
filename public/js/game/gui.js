var Gui = new function () {
    var lock = true,
        show = true,
        commandsBox = {close: 0},
        chatBox = {close: 0},
        playerBox = {close: 0},
        limitBox = {close: 0},
        mapBox = {close: 0},
        speed = 200,
        documentTitle = 'WoF',
        mapHeight,
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
                    Me.deselectArmy()
                    break;
                case 37://left
                    Three.getCamera().position.x += -2
                    Three.getCamera().position.z += -2
                    break;
                case 38://up
                    Three.getCamera().position.x += 2
                    Three.getCamera().position.z += -2
                    break;
                case 39://right
                    Three.getCamera().position.x += 2
                    Three.getCamera().position.z += 2
                    break;
                case 40://down
                    Three.getCamera().position.x += -2
                    Three.getCamera().position.z += 2
                    break;
                case 66: //b
                    CastleWindow.build()
                    break;
                case 67: //c
                    Castle.show();
                    break;
                case 68: //d
                    Me.disband()
                    break;
                case 69: //e
                    Turn.next()
                    break;
                case 70: //f
                    WebSocketGame.fortify()
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
                    WebSocketGame.ruin()
                    break;
                case 83: //s
                    StatusWindow.show()
                    break;
//            default:
//                console.log(key)
            }
        }

    this.init = function () {
        $(window).resize(function () {
            Gui.adjust()
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
        Zoom.init()
        mapHeight = $('#mapImage').height()
        var mapWidth = $('#mapImage').width()
        $('#map').css({
            width: mapWidth + 'px',
            height: mapHeight + 'px'
        })
        $('#mapBox').css({
            width: mapWidth + 'px',
            height: mapHeight + 18 + 'px'
        });
        $('#terrain').css('top', mapHeight + 4 + 'px')
        $('#commandsBox').css({top: $('#playersBox').height() + 14 + 'px'})
        this.prepareButtons()
        this.prepareBoxes()
        this.adjust()
    }
    this.prepareButtons = function () {
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
        });

        $('#show').click(function () {
            Sound.play('click');
            show = !show;
            if (show) {
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
            var id = Message.show(translations.surrender, $('<div>').html(translations.areYouSure))
            Message.ok(id, WebSocketGame.surrender)
            Message.cancel(id)
        });

        $('#statistics').click(function () {
            WebSocketGame.statistics();
        });

        $('#nextTurn').click(function () {
            Turn.next()
        });

        $('#nextArmy').click(function () {
            Me.findNext()
        })
        ;
        $('#skipArmy').click(function () {
            Me.skip()
        });

        $('#quitArmy').click(function () {
            WebSocketGame.fortify()
        });

        $('#splitArmy').click(function () {
            if (!Me.getSelectedArmyId()) {
                return
            }

            SplitWindow.show()
        })

        $('#armyStatus').click(function () {
            if (!Me.getSelectedArmyId()) {
                return
            }

            StatusWindow.show()
        });

        $('#disbandArmy').click(function () {
            Me.disband()
        });

        $('#deselectArmy').click(function () {
            if (!Me.getSelectedArmyId()) {
                return;
            }

            Me.deselectArmy()
        });

        $('#searchRuins').click(function () {
            WebSocketGame.ruin()
        });

        $('#heroResurrection').click(function () {
            var id = Message.show(translations.resurrectHero, $('<div>').append(translations.doYouWantToResurrectHeroFor100Gold))
            Message.ok(id, WebSocketGame.resurrection)
            Message.cancel(id)
        })

        $('#heroHire').click(function () {
            var id = Message.show(translations.hireHero, $('<div>').html(translations.doYouWantToHireNewHeroFor1000Gold))
            Message.ok(id, WebSocketGame.hire)
            Message.cancel(id)
        });

        $('#razeCastle').click(function () {
            CastleWindow.raze()
        });

        $('#buildCastleDefense').click(function () {
            CastleWindow.build()
        });

        $('#showCastle').click(function () {
            var army = Me.getArmy(Me.getSelectedArmyId())
            if (army) {
                var castle = Me.getCastle(Fields.get(army.getX(), army.getY()).getCastleId())
                if (isSet(castle)) {
                    CastleWindow.show(castle)
                }
            }
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
        $('#commandsBox .close').click(function () {
            var right = parseInt($('#commandsBox').css('right')),
                move = $('#commandsBox').width()

            commandsBox.el = this;

            if (commandsBox.close) {
                move = -move;
            }

            commandsBox.move = move;

            $('#commandsBox').animate({'right': right - move + 'px'}, speed, function () {
                commandsBox.close = !commandsBox.close;
                changeCloseArrowLR(commandsBox.move, commandsBox.el);
            })
        })
        //$('#chatBox .close').click(function () {
        //    Gui.moveChatBox()
        //})
    }
    this.adjust = function () {
        Zoom.lens.setdimensions()

        commandsBox.close = 0
        chatBox.close = 0
        playerBox.close = 0
        limitBox.close = 0
        mapBox.close = 0

        Three.resize()

        if (!Players.countHumans() > 1) {
            $('#chatBox').css({display: 'none'});
        }

        var goldBoxLeft = Three.getWidth() / 2 - $('#goldBox').outerWidth() / 2

        $('#goldBox').css({
            'left': goldBoxLeft + 'px'
        })
        $('#armyBox').css({
            left: Three.getWidth() / 2 - 115 + 'px'
        })
        $('#mapBox .close').css({
            left: $('#mapBox').width() + 4 + 'px'
        })
        $('#limitBox .close').css({
            left: $('#limitBox').width() + 4 + 'px'
        })
        $('#limitBox').css({
            top: mapHeight + 30 + 'px'
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
    this.getDocumentTitle = function () {
        return documentTitle
    }
    this.getShow = function () {
        return show
    }
    this.moveChatBox = function (func) {
        $('#chatBox').removeClass('mini')
    }
}