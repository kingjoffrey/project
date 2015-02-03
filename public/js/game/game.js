var Game = new function () {
    var loading = true,
        timeoutId = null

    this.init = function (game) {
        if (loading) {
            loading = false

            Units.init(game.units)
            Terrain.init(game.terrain)
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

        if (Turn.isMy()) {
            Me.turnOn()
            if (!Me.getTurnActive()) {
                Websocket.startMyTurn()
            }
        } else {
            Me.turnOff()
            if (isComputer(Turn.color)) {
                setTimeout('Websocket.computer()', 1000)
            }
        }


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
    this.getTimeoutId = function () {
        return timeoutId
    }
    this.setTimeoutId = function (value) {
        timeoutId = value
    }
}

$(document).ready(function () {
    Websocket.init();
})