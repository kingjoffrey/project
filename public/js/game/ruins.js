// *** RUINS ***

var Ruin = {
    create: function (ruinId) {
        var title;
        var css;
        if (game.ruins[ruinId].empty) {
            title = 'Ruins (empty)';
            css = '_empty';
        } else {
            title = 'Ruins';
            css = '';
        }
        board.append(
            $('<div>')
                .addClass('ruin')
                .attr({
                    id: 'ruin' + ruinId,
                    title: title
                })
                .css({
                    left: (game.ruins[ruinId].x * 40) + 'px',
                    top: (game.ruins[ruinId].y * 40) + 'px',
                    background: 'url(/img/game/ruin' + css + '.png) center center no-repeat'
                })
        );
    },
    update: function (ruinId, empty) {
        var title;
        var css;
        if (empty) {
            ruins[ruinId].e = 1;
            title = 'Ruins (empty)';
            css = '_empty';
        } else {
            ruins[ruinId].e = 0;
            title = 'Ruins';
            css = '';
        }
        $('#ruin' + ruinId).attr('title', title)
            .css('background', 'url(/img/game/ruin' + css + '.png) center center no-repeat');
    },
    getIdByPosition: function (x, y) {
        for (i in ruins) {
            if (x == ruins[i].x && y == ruins[i].y) {
                if (isSet(ruins[i].e) && ruins[i].e) {
                    return null;
                }
                return i;
            }
        }
        return null;
    }
}
