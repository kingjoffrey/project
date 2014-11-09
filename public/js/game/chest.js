var Chest = {
    update: function (color, artifactId) {
        console.log(game.players[color].chest[artifactId]);
        if (typeof game.players[color].chest[artifactId] == 'undefined') {
            game.players[color].chest[artifactId] = {artifactId: artifactId, quantity: 1};
        } else {
            game.players[color].chest[artifactId].quantity++;
        }
    }
}