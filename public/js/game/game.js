var Game = new function () {
    var loading = true

    this.init = function (game) {
        if (loading) {
            loading = false

            Units.init(game.units)
            Three.init()
            Fields.init(game.fields)
            Gui.init()
            Turn.init(game)
            Players.init(game.players)
            timer.start(game)
            Ruins.init(game.ruins)
            Me.init(game.me)

            renderChatHistory();


        }

        //Players.updateOnline()
        //
        //if (Turn.isMy()) {
        //    console.log('ccc')
        //    Turn.on();
        //} else {
        //    Turn.off();
        //}
        //
        //if (Turn.isMy() && !game.players[game.me.color].turnActive) {
        //    Websocket.startMyTurn();
        //} else if (isComputer(Turn.color)) {
        //    setTimeout('Websocket.computer()', 1000);
        //}

        //Sound.play('gamestart')
    }
}

$(document).ready(function () {
    Websocket.init();
})