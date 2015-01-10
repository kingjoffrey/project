// *** TOWERS ***

var Tower = {
    createNeutral: function (towerId) {
        var title = 'Tower';
        board.append(
            $('<div>')
                .addClass('tower')
                .attr({
                    id: 'tower' + towerId,
                    title: title
                })
                .css({
                    left: (game.neutralTowers[towerId].x * 40) + 'px',
                    top: (game.neutralTowers[towerId].y * 40) + 'px',
                    background: 'url(/img/game/towers/neutral.png) center center no-repeat'
                })
        );
    },
    create: function (towerId, color) {
        var title = 'Tower';
        //board.append(
        //    $('<div>')
        //        .addClass('tower')
        //        .attr({
        //            id: 'tower' + towerId,
        //            title: title
        //        })
        //        .css({
        //            left: (game.players[color].towers[towerId].x * 40) + 'px',
        //            top: (game.players[color].towers[towerId].y * 40) + 'px',
        //            background: 'url(/img/game/towers/' + color + '.png) center center no-repeat'
        //        })
        //);
        Three.loadTower(game.players[color].towers[towerId].x * 4 - 213, game.players[color].towers[towerId].y * 4 - 309)
    },
    change: function (towerId, color) { // todo zapisywanie zmian
        if (color == game.me.color) {
            //game.players[game.me.color].towers[towerId]
            incomeIncrement(5);
        } else if (isSet(game.players[game.me.color].towers[towerId])) {
            incomeIncrement(-5);
        }
        $('#tower' + towerId).css('background', 'url(/img/game/towers/' + color + '.png) center center no-repeat');
    }
}
