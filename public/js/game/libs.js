$(document)[0].oncontextmenu = function () {
    return false;
} // usuwa menu kontekstowe spod prawego przycisku


// *** OTHER ***

function turnOn() {
    makeMyCursorUnlock();
    skippedArmies = new Array();
    my.turn = true;
    $('#nextTurn').removeClass('buttonOff');
    $('#nextArmy').removeClass('buttonOff');
    showFirstCastle();
    turnM();
    titleBlink('Your turn!');
//    test();
}

function turnOff() {
    my.turn = false;
    unselectArmy();
    $('#nextTurn').addClass('buttonOff');
    $('#nextArmy').addClass('buttonOff');
    makeMyCursorLock();
}

function changeTurn(color, nr) {
    if (!color) {
        console.log('Turn "color" not set');
        return false;
    }
    $('#turn').css('background', 'none');
    turn.color = color;
    if (typeof nr != 'undefined') {
        turn.nr = nr;
    }
    $('#turn').css('background', color);
    $('#turnNumber').html(turn.nr);
    if (turn.color == my.color) {
        turnOn();
        wsStartMyTurn();
        return 0;
    } else {
        turnOff();
        return 1;
    }
}

function startGame() {
    if (!largeimageloaded) {
        setTimeout('startGame()', 1000);
        return;
    }

    for (i in castles) {
        new createNeutralCastle(i);
    }

    for (i in ruins) {
        new ruinCreate(i);
    }
    for (i in towers) {
        new towerCreate(i);
    }
    for (color in players) {
        players[color].active = 0;
        $('.' + color + ' .color').addClass(color + 'bg');
        if (players[color].computer) {
            $('.' + color + ' .color .type').css('background', 'url(../img/game/computer.png) center center no-repeat');
        } else {
            $('.' + color + ' .color .type').css('background', 'url(../img/game/hero_' + color + '.png) center center no-repeat');
        }

        for (i in players[color].armies) {
            players[color].armies[i] = new army(players[color].armies[i], color);
            if (color == my.color) {
                for (s in players[color].armies[i].soldiers) {
                    costs += units[players[color].armies[i].soldiers[s].unitId].cost;
                }
                myArmies = true;
            } else {
                enemyArmies = true;
            }
        }

        if (players[color].armies == "" && players[color].castles == "") {
            $('.nr.' + color).html('<img src="/img/game/skull_and_crossbones.png" />');
        }

        for (i in players[color].castles) {
            updateCastleDefense(i, players[color].castles[i].defenseMod);
            castleOwner(i, color);
            if (color == my.color) {
                income += castles[i].income;
                if (firstCastleId > i) {
                    firstCastleId = i;
                }
                myCastles = true;
                setMyCastleProduction(i);
            } else {
                enemyCastles = true;
            }
        }
    }

    showFirstCastle();

    if (!enemyArmies && !enemyCastles) {
        turnOff();
        winM(my.color);
    } else if (!myArmies && !myCastles) {
        turnOff();
        lostM(my.color);
    } else {
        if (my.turn) {
            turnOn();
        } else {
            turnOff();
        }
        if (my.turn && !players[my.color].turnActive) {
            wsStartMyTurn();
        } else if (my.game && players[turn.color].computer) {
            setTimeout('wsComputer()', 1000);
        }
    }

    renderChatHistory();
    costsUpdate(costs);
    income += countPlayerTowers(my.color) * 5;
    incomeUpdate(income);
}

function goldUpdate(gold) {
    $('#gold').html(gold);
}

function costsUpdate(gold) {
    $('#costs').html(gold);
}

function incomeUpdate(gold) {
    $('#income').html(gold);
}

function updatePlayers(color) {
    players[color].active = 2;
}

function setLock() {
    lock = true;
    $('#nextTurn').addClass('buttonOff');
    $('#nextArmy').addClass('buttonOff');
    makeMyCursorLock();
}

function unlock() {
    if (my.turn) {
        lock = false;
        $('#nextTurn').removeClass('buttonOff');
        $('#nextArmy').removeClass('buttonOff');
        makeMyCursorUnlock();
    }
}

function makeMyCursorUnlock() {
    $('body *').css('cursor', 'url(../img/game/cursor.png), auto');
    $('.zoomPup').css('cursor', 'url(../img/game/lupa.png) 13 13, crosshair');
    $('#map').css('cursor', 'url(../img/game/lupa.png) 13 13, crosshair');
    $('.c').css('cursor', 'url(../img/game/lupa.png) 13 13, crosshair');
    $('.a').css('cursor', 'url(../img/game/lupa.png) 13 13, crosshair');
    myCastlesAddCursor();
}

function makeMyCursorLock() {
    $('body *').css('cursor', 'url(../img/game/cursor_hourglass.png), wait');
}

function titleBlink(msg) {
    if (timeoutId) {
        clearInterval(timeoutId);
    }
    timeoutId = setInterval(function () {
        if (document.title == msg) {
            document.title = '...';
        } else {
            document.title = msg;
        }
    });
    window.onmousemove = function () {
        clearInterval(timeoutId);
        document.title = documentTitle;
        window.onmousemove = null;
    };
}

function getColor(color) {
    if (color == 'green') {
        return '#00db00';
    } else {
        return color;
    }
}

function makeTime() {
    var d = new Date();
    var minutes = d.getMinutes();
    if (minutes.length == 1) {
        minutes = '0' + minutes
    }
    return d.getHours() + ':' + minutes;
}

function getISODateTime(d) {
    // padding function
    var s = function (a, b) {
        return(1e15 + a + "").slice(-b)
    };

    // default date parameter
    if (typeof d === 'undefined') {
        d = new Date();
    } else {
        d = new Date(d);
    }

    // return ISO datetime
    return d.getFullYear() + '-' +
        s(d.getMonth() + 1, 2) + '-' +
        s(d.getDate(), 2) + ' ' +
        s(d.getHours(), 2) + ':' +
        s(d.getMinutes(), 2) + ':' +
        s(d.getSeconds(), 2);
}

function isDigit(val) {
    if (typeof val == 'undefined') {
        return false;
    }
    var intRegex = /^\d+$/;
    if (intRegex.test(val)) {
        return true;
    } else {
        return false;
    }
}

function isTruthful(val) {
    if (typeof val != 'undefined' && val) {
        return true;
    }
    return false;
}

function isSet(val) {
    if (typeof val == 'undefined') {
        return false;
    } else {
        return true;
    }
}

function notSet(val) {
    return !isSet(val);
}

function prepareButtons() {
    zoomPad = $(".zoomPad");
    board = $("#board");

    $('#send').click(function () {
        wsChat();
    });
    $('#msg').keypress(function (e) {
        if (e.which == 13) {
            wsChat();
        }
    });
    $('#nextTurn').click(function () {
        nextTurnM()
    });
    $('#nextArmy').click(function () {
        findNextArmy()
    });
    $('#skipArmy').click(function () {
        skipArmy()
    });
    $('#quitArmy').click(function () {
        fortifyArmy()
    });
    $('#splitArmy').click(function () {
        if (selectedArmy) {
            splitArmyM()
        }
    });
    $('#armyStatus').click(function () {
        if (selectedArmy) {
            armyStatusM()
        }
    });
    $('#disbandArmy').click(function () {
        if (selectedArmy) {
            disbandArmyM()
        }
    });
    $('#unselectArmy').click(function () {
        if (selectedArmy) {
            unselectArmy();
        }
    });
    $('#searchRuins').click(function () {
        wsSearchRuins()
    });
    $('#test').click(function () {
        test()
    });
    $('#nextTurn').addClass('buttonOff');
    $('#nextArmy').addClass('buttonOff');
    $('#skipArmy').addClass('buttonOff');
    $('#quitArmy').addClass('buttonOff');
    $('#splitArmy').addClass('buttonOff');
    $('#disbandArmy').addClass('buttonOff');
    $('#searchRuins').addClass('buttonOff');

    //    $('.'+my.color+' .color').append('You');
    $('#turn').css('background', turn.color);
    $('#turnNumber').html(turn.nr);
}

function fieldsCopy() {
    for (y in fieldsOryginal) {
        fields[y] = new Array();
        for (x in fieldsOryginal[y]) {
            fields[y][x] = fieldsOryginal[y][x];
        }
    }
}

function unitsReformat() {
    for (i in units) {
        if (i == 0) {
            continue;
        }
        units[i]['f'] = units[i].modMovesForest;
        units[i]['s'] = units[i].modMovesSwamp;
        units[i]['m'] = units[i].modMovesHills;
    }
}

function adjustGui() {
    documentWidth = $(document).width();
    documentHeigh = $(document).height() - 21;
    $('.zoomWindow').css('height', documentHeigh + 'px');

    messageLeft = documentWidth / 2 - 160;

    var left = documentWidth - 252;
    var top = documentHeigh - 153;
    $('#chatBox').css({
        'left':left + 'px',
        'top':top + 'px'
    });
    var goldBoxLeft = left / 2;
    $('#goldBox').css({
        'left':goldBoxLeft + 'px'
    });
    $('#playersBox').css({
        'left':left + 'px'
    });
    $('#armyBox').css({
        'left':left + 'px'
    });
    if (!zoomer) {
        zoomer = new zoom(documentWidth, documentHeigh);
    } else {
        zoomer.lens.setdimensions(documentWidth, documentHeigh);
    }
}