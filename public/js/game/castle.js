var Castle = function (castle, bgC, miniMapColor, textColor) {
    Game.getMapElement().append(
        $('<div>').css({
                left: MiniMap.calculateX(castle.x) + 'px',
                top: MiniMap.calculateY(castle.y) + 'px',
                background: miniMapColor,
                'border-color': textColor,
                width: MiniMap.calculateX(2) + 'px',
                height: MiniMap.calculateY(2) + 'px'
            })
            .attr('id', 'c' + castle.id)
            .addClass('c')
    )

    return new CommonCastle(castle, bgC, miniMapColor, textColor)
}