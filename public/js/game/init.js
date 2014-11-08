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

var fields = new Array()
var game = {}
var neutralCastles = {}

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
        game = r
        fieldsCopy();

        Gui.init();
        Turn.init()
        Players.init()

        Players.updateOnline()

        for (castleId in game.neutralCastles) {
            Castle.createNeutral(castleId)
        }

        for (towerId in game.neutralTowers) {
            Tower.createNeutral(towerId)
        }

        for (ruinId in game.ruins) {
            Ruin.create(ruinId)
        }

        renderChatHistory();

        goldUpdate(game.me.gold)
        costsUpdate(game.me.costs)

        incomeUpdate(game.me.income)

        Init.start()
    },
    start: function () {
        if (!zoom.loaded) {
            setTimeout('Init.start()', 1000);
            return;
        }

        if (game.me.turn) {
            Turn.on();
        } else {
            Turn.off();
        }

        if (Turn.color == game.me.color && !game.players[game.me.color].turnActive) {
            Websocket.startMyTurn();
        } else if (game.players[Turn.color].computer) {
            setTimeout('Websocket.computer()', 1000);
        }

        Sound.play('gamestart')
    }
}
