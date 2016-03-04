var Castle = function (castle, bgC, miniMapColor, textColor) {
    Game.getMapElement().append(
        $('<div>').css({
                left: Zoom.calculateMiniMapX(castle.x) + 'px',
                top: Zoom.calculateMiniMapY(castle.y) + 'px',
                background: miniMapColor,
                'border-color': textColor,
                width: Zoom.calculateMiniMapX(2) + 'px',
                height: Zoom.calculateMiniMapY(2) + 'px'
            })
            .attr('id', 'c' + castle.id)
            .addClass('c')
    )

    return new CommonCastle(castle, bgC, miniMapColor, textColor)
}