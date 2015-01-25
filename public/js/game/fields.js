var Fields = new function () {
    var fields
    this.init = function (fields) {
        var mountainModel = Three.getMountainModel(),
            hillModel = Three.getHillModel(),
            treeModel = Three.getTreeModel(),
            scene = Three.getScene()

        for (var y in fields) {
            for (var x in fields[y]) {
                switch (fields[y][x]) {
                    case 'm':
                        var mesh = new THREE.Mesh(mountainModel.geometry, mountainModel.material)
                        mesh.position.set(x * 4 - 216, 0, y * 4 - 311)
                        mesh.rotation.y = Math.PI * Math.random()

                        mesh.castShadow = true
                        mesh.receiveShadow = true

                        scene.add(mesh)
                        break
                    case 'h':
                        var mesh = new THREE.Mesh(hillModel.geometry, hillModel.material)
                        mesh.position.set(x * 4 - 216, 0, y * 4 - 311)
                        mesh.rotation.y = Math.PI * Math.random()

                        //mesh.castShadow = true
                        mesh.receiveShadow = true

                        scene.add(mesh)
                        break
                    case 'f':
                        var mesh = new THREE.Mesh(treeModel.geometry, treeModel.material)
                        mesh.position.set(x * 4 - 216, 0, y * 4 - 311)
                        mesh.rotation.y = Math.PI * Math.random()

                        mesh.castShadow = true
                        mesh.receiveShadow = true

                        scene.add(mesh)
                        break
                }
            }
        }
    }
    this.add = function (x, y, field) {
        fields[y][x] = new Field(field)
    }
    this.get = function () {
        return fields[y][x]
    }
}