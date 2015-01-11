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
        //board.append(
        //    $('<div>')
        //        .addClass('ruin')
        //        .attr({
        //            id: 'ruin' + ruinId,
        //            title: title
        //        })
        //        .css({
        //            left: (game.ruins[ruinId].x * 40) + 'px',
        //            top: (game.ruins[ruinId].y * 40) + 'px',
        //            background: 'url(/img/game/ruin' + css + '.png) center center no-repeat'
        //        })
        //);
    },
    update: function (ruinId, empty) {
        var title;
        var css;
        if (empty) {
            game.ruins[ruinId].empty = 1;
            title = 'Ruins (empty)';
            css = '_empty';
        } else {
            game.ruins[ruinId].empty = 0;
            title = 'Ruins';
            css = '';
        }
        $('#ruin' + ruinId).attr('title', title)
            .css('background', 'url(/img/game/ruin' + css + '.png) center center no-repeat');
    },
    getIdByPosition: function (x, y) {
        for (var ruinId in game.ruins) {
            if (x == game.ruins[ruinId].x && y == game.ruins[ruinId].y) {
                if (isTruthful(game.ruins[ruinId].empty)) {
                    return null;
                }
                return ruinId;
            }
        }
        return null;
    }
}
