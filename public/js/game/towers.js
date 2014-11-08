// *** TOWERS ***

var Tower = {
    create: function (towerId) {
        var title = 'Tower';
        board.append(
            $('<div>')
                .addClass('tower')
                .attr({
                    id: 'tower' + towerId,
                    title: title
                })
                .css({
                    left: (towers[towerId].x * 40) + 'px',
                    top: (towers[towerId].y * 40) + 'px',
                    background: 'url(/img/game/towers/' + towers[towerId].color + '.png) center center no-repeat'
                })
        );
    },
    isAtPosition: function (x, y) {
        for (towerId in towers) {
            if (towers[towerId].x == x && towers[towerId].y == y) {
                return 1;
            }
        }
        return 0;
    },
    change: function (towerId, color) {
        if (color == game.me.color && towers[towerId].color != game.me.color) {
            incomeIncrement(5);
        } else if (color != game.me.color && towers[towerId].color == game.me.color) {
            incomeIncrement(-5);
        }
        towers[towerId].color = color;
        $('#tower' + towerId).css('background', 'url(/img/game/towers/' + color + '.png) center center no-repeat');
    },
    countPlayers: function (color) {
        var count = 0;
        for (i in game.players[color].towers) {
            count++;
        }
        return count;
    }
}
