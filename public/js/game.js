var newX = 0;
var newY = 0;

var turn = new Array();
var my = new Array();

var lock = true;

var selectedArmy = null;
var unselectedArmy = null;
var parentArmyId = null;
var selectedEnemyArmy = null;
var nextArmy = null;
var nextArmySelected = false;

var zoomer;

var socket;

var nextArmy;

var channel = 'publicA';

$(document).ready(function() {
    zoomer = new zoom(758, 670);
    $('#wsStatus').html('DISCONNECTED!');
    initWS()
    lock = false;
    startM();
    if(turn.myTurn){
        turnOn();
    }else{
        turnOff();
    }
/*    for(y in fields) {
        for(x in fields[y]) {
            board.append(
                $('<div>')
                .html(fields[y][x])
                .addClass('field')
                .css({
                    left:(x*40)+'px',
                    top:(y*40)+'px'
                })
            );
        }
    }*/
});

function turnOn() {
    turn.myTurn = true;
    $('#nextTurn')
    .css({
        'color':'#ddd',
        'text-shadow': '#000 -1px -1px',
        'background':'#555',
        'border':'1px solid #fff',
        'cursor':'pointer'
    })
    .click(function(){sendNextTurn()});
    $('#nextArmy')
    .css({
        'color':'#ddd',
         'text-shadow': '#000 -1px -1px',
         'background':'#555',
         'border':'1px solid #fff',
         'cursor':'pointer'
    })
    .click(function(){findNextArmy()});
    showFirstCastle();
}

function turnOff() {
    turn.myTurn = false;
    unselectArmy();
    $('#nextTurn')
    .css({
        'color':'#000',
        'text-shadow': '#fff 1px 1px',
        'background':'#eee',
        'border':'1px solid #555',
        'cursor':'default'
    })
    .click(function(){return null});
    $('#nextArmy')
    .css({
        'color':'#000',
         'text-shadow': '#fff 1px 1px',
         'background':'#eee',
         'border':'1px solid #555',
         'cursor':'default'
    })
    .click(function(){return null});
}

function changeTurn(playerId, color) {
    if(!color) {
        console.log('Turn "color" not set');
        return false;
    }
    if(!playerId) {
        console.log('Turn "playerId" not set');
        return false;
    }
    $('#'+turn.color+'Turn').html('');
    turn.color = color;
    $('#'+turn.color+'Turn').html('turn');
    turn.playerId = playerId;
    if(turn.playerId == my.playerId) {
        turnOn();
        startMyTurn();
        return 0;
    } else {
        turnOff();
        return 1;
    }
}

function initGame(){
    subscribeChannel();
    for(i in castles) {
        castles[i] = new createCastle(i);
    }
    for(color in players) {
        $('.'+color).css('display','block');
        $('#'+color+'Color').css({
            width:'50px',
            height:'18px',
            background:color,
            border:'1px solid #cecece'
        })
        for(i in players[color].armies) {
            players[color].armies[i] = new army(players[color].armies[i], color);
        }
        for(i in players[color].castles) {
            castleOwner(i, color);
        }
    }
    auth();

//     setInterval ( 'wsPing()', 9000 );
//     $('#game').fadeIn(1000);
}

