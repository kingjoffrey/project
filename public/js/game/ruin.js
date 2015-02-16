var Ruin = function (ruin) {
    this.update = function (empty) {
        ruin.empty = empty
        Three.getScene().getObjectById(meshId).material.color.set(this.getColor())

//    var title
//    if (empty) {
//        empty = 1;
//        title = 'Ruins (empty)'
//    } else {
//        empty = 0;
//        title = 'Ruins'
//    }
    }
    this.getColor = function () {
        if (ruin.empty) {
            return '#8080a0'
        } else {
            return '#FFD700'
        }
    }
    var meshId = Three.addRuin(ruin.x, ruin.y, this.getColor())
}
