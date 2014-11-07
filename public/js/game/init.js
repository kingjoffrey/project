Image1 = new Image(27, 32);
Image1.src = '/img/game/cursor_attack.png';
Image2 = new Image(14, 46);
Image2.src = '/img/game/cursor_castle.png';
Image3 = new Image(25, 26);
Image3.src = '/img/game/cursor_select.png';
Image4 = new Image(9, 20);
Image4.src = '/img/game/cursor.png';
Image5 = new Image(20, 9);
Image5.src = '/img/game/cursor_pointer.png';

var fieldsOriginal = new Array()
var fields = new Array()
var terrain = {}
var online = {}
var players = {}

//var selectedEnemyArmy = null;

var firstCastleId = 1000;

var zoomer;
var map;
var board;
var coord;

var documentTitle = document.title;
var timeoutId = null;

var largeimageloaded = false;

var myArmies = false;
var myCastles = false;
var enemyArmies = false;
var enemyCastles = false;

var gameWidth;
var gameHeight;

var stop = 0;

var shipId;

var castlesConquered = null;
var castlesDestroyed = null;
var heroesKilled = null;
var soldiersKilled = null;
var soldiersCreated = null;

var loading = true;

$(document).ready(function () {
    Websocket.init();
    $(window).resize(function () {
        Gui.adjust();
    });
});

var Init = {
    game: function (r) {
        my = r.me
        players = r.players
        terrain = r.terrain
        fieldsCopy();

        Gui.init();
        Turn.init(r.turnHistory)
        Players.init(r.players)

        Sound.play('gamestart');

        Castle.showFirst();

        if (my.turn) {
            Turn.on();
        } else {
            Turn.off();
        }

        if (my.turn && !players[my.color].turnActive) {
            Websocket.startMyTurn();
        } else if (my.game && players[Turn.color].computer) {
            setTimeout('Websocket.computer()', 1000);
        }

        renderChatHistory();

        goldUpdate(my.gold)
        costsUpdate(my.costs)
        my.income += Tower.countPlayers(my.color) * 5
        incomeUpdate(my.income)
    },
    start: function () {
        if (!largeimageloaded) {
            setTimeout('Init.start()', 1000);
            return;
        }
    }
}
