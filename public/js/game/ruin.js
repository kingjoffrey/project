var Ruin = function (ruin) {
    this.update = function (empty) {
        ruin.empty = empty
        Three.getScene().getObjectById(meshId).material.color.set(this.getColor())
    }
    this.getColor = function () {
        if (ruin.empty) {
            return '#8080a0'
        } else {
            return '#FFD700'
        }
    }
    this.getX = function () {
        return ruin.x
    }
    this.getY = function () {
        return ruin.y
    }
    var meshId = Three.addRuin(ruin.x, ruin.y, this.getColor())
}
