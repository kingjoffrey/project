var Ruin = function (ruin) {
    console.log(ruin)
    var x = ruin.x,
        y = ruin.y,
        empty = ruin.empty,
        meshId = 0

    this.addToScene()

    this.update = function (empty) {
        var title
        if (empty) {
            empty = 1;
            title = 'Ruins (empty)'
        } else {
            empty = 0;
            title = 'Ruins'
        }
    }
    this.getX()
}

Ruin.prototype.addToScene = function (r) {
    var scale = 0.3
    var mesh = Three.getRuin()
    meshId = mesh.id

    mesh.scale.set(scale, scale, scale)
    mesh.position.set(x * 4 - 216, 0, y * 4 - 311)

    mesh.castShadow = true
    mesh.receiveShadow = true

    Three.getScene().add(mesh)
}
