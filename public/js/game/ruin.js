var Ruin = function (ruin) {
    var x = ruin.x,
        y = ruin.y,
        empty = ruin.empty

    var scale = 0.3
    var mesh = Three.getRuin()
    var meshId = mesh.id

    mesh.scale.set(scale, scale, scale)
    mesh.position.set(x * 4 - 216, 0, y * 4 - 311)

    mesh.castShadow = true
    mesh.receiveShadow = true

    Three.getScene().add(mesh)

//this.update = function (empty) {
//    var title
//    if (empty) {
//        empty = 1;
//        title = 'Ruins (empty)'
//    } else {
//        empty = 0;
//        title = 'Ruins'
//    }
//}

}
