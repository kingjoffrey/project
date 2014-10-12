// *** RUINS ***

var Ruin = {
    create: function (ruinId) {
        var title;
        var css;
        if (typeof ruins[ruinId].e == 'undefined') {
            title = 'Ruins';
            css = '';
        } else {
            title = 'Ruins (empty)';
            css = '_empty';
        }
        board.append(
            $('<div>')
                .addClass('ruin')
                .attr({
                    id: 'ruin' + ruinId,
                    title: title
                })
                .css({
                    left: (ruins[ruinId].x * 40) + 'px',
                    top: (ruins[ruinId].y * 40) + 'px',
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
