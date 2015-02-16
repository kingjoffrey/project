var Tower = function (tower, bgColor) {
    var meshId = Three.addTower(tower.x, tower.y, bgColor)
    this.update = function (bgColor) {
        Three.getScene().getObjectById(meshId).material.color.set(bgColor)
    }
}


var Towerrr = {

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