var Game = new function () {
    var loading = true

    this.init = function (game) {
        if (loading) {
            fieldsCopy();

            Three.init()
            Gui.init();
            Turn.init()
            Players.init(game.players)
            Me.init(game.me)
            Ruins.init()

            renderChatHistory();


            loading = false
        }

        Players.updateOnline()

        if (Turn.isMy()) {
            console.log('ccc')
            Turn.on();
        } else {
            Turn.off();
        }

        if (Turn.isMy() && !game.players[game.me.color].turnActive) {
            Websocket.startMyTurn();
        } else if (isComputer(Turn.color)) {
            setTimeout('Websocket.computer()', 1000);
        }

        //Sound.play('gamestart')
    }
}

$(document).ready(function () {
    Websocket.init();
})