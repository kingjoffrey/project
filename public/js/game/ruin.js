var Ruin = function (ruin) {
    var meshId = Three.addRuin(ruin.x, ruin.y)

    this.update = function (empty) {
        ruin.empty = empty
//    var title
//    if (empty) {
//        empty = 1;
//        title = 'Ruins (empty)'
//    } else {
//        empty = 0;
//        title = 'Ruins'
//    }
    }

}
